<?php
namespace Doctrineum\Tests\Scalar\Helpers\EnumTypes;

use Doctrineum\Scalar\EnumType;

class IAmUsingOccupiedName extends EnumType
{
    public static function getTypeName()
    {
        return parent::getTypeName();
    }

}
