<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Container\Container;
use Phplc\Core\RuntimeFields\Dto\PeriodicTaskBuildResult;

// class Test11 {
//     private $x;
//     public $y;
//     protected $z;

//     public function __construct($x, $y, $z)
//     {
//         $this->x = $x;
//         $this->y = $y;
//         $this->z = $z;
//     }

//     public function printAll()
//     {
//         print_r($this);
//     }
// }


// $xxx = new \ReflectionClass(Test11::class);

// foreach ($xxx->getConstructor()->getParameters() as $param) {
//     print_r($param->getName());
// }

$container = new Container(); 

