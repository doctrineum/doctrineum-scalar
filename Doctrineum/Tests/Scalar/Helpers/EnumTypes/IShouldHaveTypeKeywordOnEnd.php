<?php
namespace Doctrineum\Tests\Scalar\Helpers\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;

class IShouldHaveTypeKeywordOnEnd extends ScalarEnumType
{
    const I_SHOULD_HAVE_TYPE_KEYWORD_ON_END = 'i_should_have_type_keyword_on_end';

    public function getName()
    {
        return self::I_SHOULD_HAVE_TYPE_KEYWORD_ON_END;
    }
}