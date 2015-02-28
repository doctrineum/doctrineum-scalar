<?php
namespace Doctrineum\Generic;

use Doctrineum\Tests\Generic\EnumTestTrait;
use Doctrineum\Tests\Generic\EnumTypeTestTrait;

class SelfTypedEnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Combining both enum type tests
     * @see EnumTypeTest
     * and enum tests
     * @see EnumTestTrait
     */
    use EnumTestTrait;
    use EnumTypeTestTrait;

    /**
     * Overloaded test to compare new type name
     * @test
     */
    public function type_name_is_as_expected()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        /** @var \PHPUnit_Framework_TestCase|SelfTypedEnumTest $this */
        $this->assertSame('self_typed_enum', $enumTypeClass::getTypeName());
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        $this->assertSame($enumType::getTypeName(), $enumTypeClass::getTypeName());
    }

    /** @test */
    public function any_enum_namespace_is_accepted()
    {
        $this->markTestSkipped('Self-typed enum does not support different namespaces yet');
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\SelfTypedEnumConstantNamespaceChanged
     */
    public function non_default_enum_namespace_is_prohibited()
    {
        SelfTypedEnum::getEnum('foo', SelfTypedEnum::CANNOT_BE_CHANGED_NAMESPACE . 'bar');
    }
}
