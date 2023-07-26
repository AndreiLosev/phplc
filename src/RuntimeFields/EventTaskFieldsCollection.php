<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Container;
use Phplc\Core\Contracts\RetainProperty;
use Phplc\Core\RuntimeFields\Dto\EventTaskFieldDto;

class EventTaskFieldsCollection  
{
    /** 
     * @var EventTaskField[] 
     */
    private array $collection;

    /** 
     * @param EventTaskFieldDto[] $collection
     */
    public function __construct(
        private Container $container,
        array $collection,
    ) {
        $this->collection = array_map(
            fn(EventTaskFieldDto $etf) => new EventTaskField(
                $etf->task,
                $etf->eventName,
                $etf->taskRetainPropertus,
                $etf->storageRetainProerty,
                $etf->taskChangeTrackingPropertus,
                $etf->storageChangeTrackingProerty,
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

    public function run(string $event): bool
    {
        for ($i = 0; $i < count($this->collection); $i++) { 
            if ($this->collection[$i]->match($event)) {
                $this->collection[$i]->run();
                return true;
            }
        }

        return false;
    }

    public function eventIsExists(string $event): bool
    {
        for ($i = 0; $i < count($this->collection); $i++) { 
            if ($this->collection[$i]->match($event)) {
                return true;
            }
        }

        return false;
    }
}
