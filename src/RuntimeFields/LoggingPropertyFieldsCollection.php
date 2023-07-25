<?php

namespace Phplc\Core\RuntimeFields;

use Phplc\Core\Config;
use Phplc\Core\Container;
use Phplc\Core\Contracts\LoggingProperty;
use Phplc\Core\Contracts\Storage;
use Phplc\Core\Contracts\Task;
use function Amp\delay;
use Phplc\Core\Helpers;


class LoggingPropertyFieldsCollection
{
    private float $period;

    private LoggingProperty $loggingService;

    /** 
     * @param array<class-string<Task|Storage>, LoggingPropertyField[]> $collection
     */
    public function __construct(
        private array $collection,
        private Container $continer,
    ) {
        $this->period = $continer->make(Config::class)->loggingPeriod;
        $this->loggingService = $continer->make(LoggingProperty::class);
    }

    public function run(): void
    {
        while (true) {
            delay($this->period);
            $prepared = [];
            foreach ($this->collection as $class => $property) {
                $shortName = Helpers::shortName($class);
                $instants = $this->continer->make($class);
                for ($i = 0; $i < count($property); $i++) {
                    [$key, $value] = $this->getKeyValue(
                        $shortName,
                        $property[$i],
                        $instants
                    );  
                    $prepared[$key] = $value;
                }
            }
            $this->loggingService->setLog($prepared);
        }
    }

    /** 
     * @return array{string, scalar} 
     */
    private function getKeyValue(
        string $shortName,
        LoggingPropertyField $prop,
        Task|Storage $class,
    ): array
    {
        $key = "{$shortName}::{$prop->name}";
        /** @var scalar */
        $value = match ($prop->getter) {
            null => $class->{$prop->name},
            default => $class->{$prop->getter}(),
        };

        return [$key, $value];
    }
}
