<?php

namespace Tests\TestClsses;

use Phplc\Core\Attributes\EventTask;
use Phplc\Core\Contracts\Task;

#[EventTask('3-test')]
class EvenTaskWithStores implements Task
{
    public function __construct(
        public StoreTest1 $storeTest1,
        private StoreTest2 $storeTest2,
    ) {}

    public function execute(): void
    {
        $this->storeTest1->setX2(
            $this->storeTest1->getX2() + 0.55,
        );

        $this->storeTest2->setX4(
            $this->storeTest2->getX4() + 1.05,
        );
    }
}
