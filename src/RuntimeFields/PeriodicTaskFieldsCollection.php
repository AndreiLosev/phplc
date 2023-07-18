<?php

namespace Phplc\Core\RuntimeFields;

use Amp\Future;
use function Amp\async;

class PeriodicTaskFieldsCollection
{
    /** 
     * @param PeriodicTaskField[] $collection
     */
    public function __construct(
        private array $collection,
    ) {}

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
}
