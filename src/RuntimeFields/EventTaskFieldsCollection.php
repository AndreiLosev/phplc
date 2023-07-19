<?php

namespace Phplc\Core\RuntimeFields;

class EventTaskFieldsCollection  
{
    /** 
     * @param EventTaskField[] $collection
     */
    public function __construct(
        private array $collection,
    ) {}

    public function run(string $event): void
    {
        for ($i = 0; $i < count($this->collection); $i++) { 
            if ($this->collection[$i]->match($event)) {
                $this->collection[$i]->run();
            }
        }
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
