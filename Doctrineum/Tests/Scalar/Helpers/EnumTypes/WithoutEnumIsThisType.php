<?php
namespace Doctrineum\Tests\Scalar\Helpers\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;

class WithoutEnumIsThisType extends ScalarEnumType
{
    const WITHOUT_ENUM_IS_THIS_TYPE = 'without_enum_is_this_type';

    public function getName()
    {
        return self::WITHOUT_ENUM_IS_THIS_TYPE;
    }
}