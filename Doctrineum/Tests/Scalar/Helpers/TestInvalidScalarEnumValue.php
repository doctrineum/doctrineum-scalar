<?php
namespace Doctrineum\Tests\Scalar\Helpers;

use Granam\ScalarEnum\ScalarEnum;

class TestInvalidScalarEnumValue extends ScalarEnum
{

    protected static function convertToEnumFinalValue($value)
    {
        // intentionally no conversion at all
        return $value;
    }
}