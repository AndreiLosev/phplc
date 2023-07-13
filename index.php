<?php

require_once __DIR__ . '/vendor/autoload.php';

class Test11
{
    public function publciPrint()
    {
        print_r('public');
    }

    private function privatePrint()
    {
        print_r('private');
    }
}

$qwe = new Test11;

print_r([
    method_exists($qwe, 'publciPrint'),
    method_exists($qwe, 'privatePrint'),
]);

print_r([
    is_callable([$qwe, 'publciPrint']),
    is_callable([$qwe, 'privatePrint'])
]);
