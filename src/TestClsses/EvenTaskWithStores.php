<?php

namespace Phplc\Core\TestClsses;

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
        print_r('Test 1 2');
    }
}
