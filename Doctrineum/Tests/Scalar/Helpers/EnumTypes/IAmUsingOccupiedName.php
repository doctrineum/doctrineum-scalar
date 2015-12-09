<?php
namespace Doctrineum\Tests\Scalar\Helpers\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;

class IAmUsingOccupiedName extends ScalarEnumType
{
    public static function getTypeName()
    {
        return parent::getTypeName();
    }

}
