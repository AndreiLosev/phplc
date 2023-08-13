<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use function Amp\Future\awaitAll;
use function Amp\async;

class RetainPropertyHeandler
{
    /** 
     * @param RetainPropertyField[] $taskRetainPropertus  
     * @param array<class-string<Storage>, RetainPropertyField[]> $storageRetainProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private array $taskRetainPropertus,
        private array $storageRetainProerty,
        private RetainProperty $retainService,
        private \Closure $makeStorage,
    ) {}

    public function init(Task $task): void
    {
        $names = $this->createRetainTableAndPropertyIfNotExists($task);
        $this->initRetaintPropertyFromPropertyStorage($names, $task);
    }

    public function saveProprty(Task $task): void
    {
        $futurs = [];

        for ($i = 0; $i  < count($this->taskRetainPropertus); $i ++) { 
            [$key, $value] = $this->taskRetainPropertus[$i]->getKeyValue($task);
            $futurs[] = async($this->retainService->update(...), $key, $value);
        }

        $makeStorage = $this->makeStorage;
        foreach ($this->storageRetainProerty as $storageName => $property) {
            $storageInstans = $makeStorage($storageName);
            for ($i = 0; $i < count($property); $i ++) { 
                [$key, $value] = $property[$i]->getKeyValue($storageInstans);
                $futurs[] = async($this->retainService->update(...), $key, $value);
            }
        }

        [$errors] = awaitAll($futurs);

        foreach ($errors as $e) {
            throw $e;
        }
    }

    /** 
     * @return string[]  
     */
    private function createRetainTableAndPropertyIfNotExists(Task $task): array
    {
        $names = [];

        for ($i = 0; $i  < count($this->taskRetainPropertus); $i ++) { 
            [$key, $value] = $this->taskRetainPropertus[$i]->getKeyValue($task);
            $this->retainService->createIfNotExists($key, $value);
            $names[] = $key;
        }

        $makeStorage = $this->makeStorage;
        foreach ($this->storageRetainProerty as $storageName => $property) {
            $storageInstans = $makeStorage($storageName);
            for ($i = 0; $i < count($property); $i ++) { 
                [$key, $value] = $property[$i]->getKeyValue($storageInstans);
                $this->retainService->createIfNotExists($key, $value);
                $names[] = $key;
            }
        }

        return $names;
    }

    /** 
     * @param string[] $names
     */
    private function initRetaintPropertyFromPropertyStorage(array $names, Task $task): void
    {
        if (count($names) === 0) {
            return;
        }

        $retainProperty = $this->retainService->select($names);

        for ($i = 0; $i  < count($this->taskRetainPropertus); $i ++) { 
            [$key] = $this->taskRetainPropertus[$i]->getKeyValue($task);
            $this->taskRetainPropertus[$i]->setValue($task, $retainProperty[$key]);
        }

        $makeStorage = $this->makeStorage;
        foreach ($this->storageRetainProerty as $storageName => $property) {
            $storageInstans = $makeStorage($storageName);
            for ($i = 0; $i < count($property); $i ++) { 
                [$key] = $property[$i]->getKeyValue($storageInstans);
                $property[$i]->setValue(
                    $storageInstans,
                    $retainProperty[$key],
                );
            }
        }
    }
}
