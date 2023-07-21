<?php

class TestClass
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
}

/** 
 * @param TestClass::TYPE_* $type 
 */
function test1(\stdClass $obj, $type): null|string|int
{
    if (!property_exists($obj, 'xx')) {
        return null;
    }

    if ($type === TestClass::TYPE_STRING && is_string($obj->xx)) {
        return $obj->xx;
    }

    if ($type === TestClass::TYPE_INT && is_int($obj->xx)) {
        return $obj->xx;
    }

    return null;
}

function test2(\stdClass $obj): void
{
    $obj = test1($obj, 'string');
}
