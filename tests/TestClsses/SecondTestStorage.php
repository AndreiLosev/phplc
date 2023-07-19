<?php

namespace Tests\TestClsses;

use Phplc\Core\Contracts\Storage;

class SecondTestStorage implements Storage
{
    public int $value = 0;
}
