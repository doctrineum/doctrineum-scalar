<?php
namespace Doctrineum\Tests\Scalar\Helpers\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;

class EnumWithSubNamespaceType extends ScalarEnumType
{
    const WITH_SUB_NAMESPACE = 'with_sub_namespace';

    public function getName()
    {
        return self::WITH_SUB_NAMESPACE;
    }
}
