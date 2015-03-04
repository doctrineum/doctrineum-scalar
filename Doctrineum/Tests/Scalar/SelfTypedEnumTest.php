<?php
namespace Doctrineum\Scalar;

use Doctrineum\Tests\Scalar\EnumTestTrait;
use Doctrineum\Tests\Scalar\EnumTypeTestTrait;

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

    protected function setUp()
    {
        // overloaded type registration from the trait to test it later
    }

    /**
     * @test
     */
    public function can_register_self()
    {
        SelfTypedEnum::registerSelf();
    }

    /**
     * @test
     * @depends can_register_self
     */
    public function repeated_self_registration_returns_false()
    {
        $this->assertFalse(SelfTypedEnum::registerSelf());
    }

    /**
     * Overloaded test to compare new type name
     * @test
     */
    public function type_name_is_as_expected()
    {
        /** @var \PHPUnit_Framework_TestCase|SelfTypedEnumTest $this */
        $this->assertSame('self_typed_enum', SelfTypedEnum::getTypeName());
        $this->assertSame('self_typed_enum', SelfTypedEnum::SELF_TYPED_ENUM);
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        $this->assertSame($enumType::getTypeName(), SelfTypedEnum::getTypeName());
    }
}
