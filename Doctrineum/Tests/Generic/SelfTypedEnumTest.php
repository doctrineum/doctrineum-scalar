<?php
namespace Doctrineum\Generic;

use Doctrineum\Tests\Generic\EnumTestTrait;

class SelfTypedEnumTest extends EnumTypeTest
{
    /**
     * Combining both enum type tests
     * @see EnumTypeTest
     * and enum tests
     * @see EnumTestTrait
     */
    use EnumTestTrait;

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
}
