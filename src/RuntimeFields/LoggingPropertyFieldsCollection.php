<?php

namespace Phplc\Core\RuntimeFields;

use Amp\Cancellation;
use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use function Amp\delay;

class LoggingPropertyFieldsCollection
{
    private float $period;

    private LoggingProperty $loggingService;

    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $collection
     */
    public function __construct(
        private Container $continer,
        private array $collection,
    ) {
        $this->period = $continer->make(Config::class)->loggingPeriod;
        $this->loggingService = $continer->make(LoggingProperty::class);
    }

    public function run(Cancellation $cancellation): void
    {
        while (true) {
            delay($this->period);
            $cancellation->throwIfRequested();
            $prepared = [];
            foreach ($this->collection as $class => $property) {
                $instants = $this->continer->make($class);
                for ($i = 0; $i < count($property); $i++) {
                    [$key, $value] = $property[$i]->getKeyValue($instants);  
                    $prepared[$key] = $value;
                }
            }
            $this->loggingService->setLog($prepared);
        }
    }
}
