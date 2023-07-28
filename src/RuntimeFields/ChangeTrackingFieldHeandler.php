<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Contracts\EventDispatcher;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use Phplc\Core\System\ChangeTrackingStorage;

class ChangeTrackingFieldHeandler 
{
    /** 
     * @param ChangeTrackingField[] $taskChangeTrackingPropertus
     * @param array<class-string<Storage>, ChangeTrackingField[]> $storageChangeTrackingProerty
     * @param \Closure(class-string<Storage>): Storage $makeStorage
     */
    public function __construct(
        private array $taskChangeTrackingPropertus,
        private array $storageChangeTrackingProerty,
        private ChangeTrackingStorage $trackingStorage,
        private EventDispatcher $eventDispatcher,
        private \Closure $makeStorage,
    ) {}

    public function heandler(Task $task): void
    {
        for ($i = 0; $i  < count($this->taskChangeTrackingPropertus); $i ++) { 
            /** @var scalar $value */
            [$name, $value] = $this->taskChangeTrackingPropertus[$i]->getKeyValue($task);
            if ($this->trackingStorage->valueIsChanged($name, $value)) {
                $event = $this->taskChangeTrackingPropertus[$i]->event;
                $this->eventDispatcher->dispatch($event);
            }
        }

        $makeStorage = $this->makeStorage;
        foreach ($this->storageChangeTrackingProerty as $storageName => $property) {
            $storageInstans = $makeStorage($storageName);
            for ($i = 0; $i < count($property); $i ++) { 
                /** @var scalar $value */
                [$name, $value] = $property[$i]->getKeyValue($storageInstans);
                if ($this->trackingStorage->valueIsChanged($name, $value)) {
                    $event = $property[$i]->event;
                    $this->eventDispatcher->dispatch($event);
                }
            }
        }
    }
}
