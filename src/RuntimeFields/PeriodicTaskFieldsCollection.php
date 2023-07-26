<?php

namespace Phplc\Core\RuntimeFields;

use Amp\Future;
use Phplc\Core\Container;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskFieldDto;
use function Amp\async;

class PeriodicTaskFieldsCollection
{
    /** 
     * @var PeriodicTaskField[] $collection 
     */
    private array $collection;
    /** 
     * @param PeriodicTaskFieldDto[] $collection
     */
    public function __construct(
        private Container $container,
        array $collection,
    ) {
        $this->collection = array_map(
            fn(PeriodicTaskFieldDto $ptf) => new PeriodicTaskField(
                $ptf->task,
                $ptf->periodMilis,
                $ptf->taskRetainPropertus,
                $ptf->storageRetainProerty,
                $ptf->taskChangeTrackingPropertus,
                $ptf->storageChangeTrackingProerty,
                $container->make(RetainProperty::class),
                fn(string $strStorage) => $container->make($strStorage),
            ),
            $collection,
        );
    }

    public function build(): void
    {
        for ($i = 0; $i < count($this->collection); $i++) { 
            $this->collection[$i]->init();
        }
    }

    public function run(): void
    {
        $fetures = [];
        for ($i = 0; $i < count($this->collection); $i++) {
            $fetures[] = async($this->collection[$i]->run(...));        
        }

       Future\awaitAll($fetures);
    }

    public function cancel(): void
    {
        for ($i = 0; $i < count($this->collection); $i++) { 
            $this->collection[$i]->cancel();
        }
    }

    /** 
     * @param class-string<Storage> $strStorage
     */
    private function makeStorage(string $strStorage): Storage
    {
        return $this->container->make($strStorage);
    }
}
