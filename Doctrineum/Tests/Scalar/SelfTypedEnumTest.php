<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Types\Type;
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

    /**
     * Overloaded parent test to test self-registration
     *
     * @test
     */
    public function can_be_registered()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumTypeClass::registerSelf();
        $this->assertTrue(Type::hasType($enumTypeClass::getTypeName()));
    }

    /**
     * @test
     * @depends can_be_registered
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

    protected function getInheritedEnum($value)
    {
        if (!Type::hasType(TestInheritedSelfTypedEnum::getTypeName())) {
            TestInheritedSelfTypedEnum::registerSelf();
        }
        $enum = TestInheritedSelfTypedEnum::getEnum($value);

        return $enum;
    }
}

/** inner */
class TestInheritedSelfTypedEnum extends SelfTypedEnum
{

}
