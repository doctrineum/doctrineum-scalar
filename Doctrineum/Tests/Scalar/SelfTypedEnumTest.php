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
        SelfTypedEnum::registerSelf();
        $this->assertTrue(Type::hasType(SelfTypedEnum::getTypeName()));
    }

    /**
     * @test
     * @depends can_be_registered
     */
    public function repeated_self_registration_returns_false()
    {
        $this->assertFalse(SelfTypedEnum::registerSelf());
    }

    /** @test */
    public function can_use_subtype()
    {
        SelfTypedEnum::addSubtypeEnum(TestInheritedSelfTypedEnum::class, $pattern = '~foo~');
        $this->assertRegExp($pattern, $enumValue = 'foo bar baz');
        $enumBySubtype = SelfTypedEnum::getEnum($enumValue);
        $this->assertInstanceOf(TestInheritedSelfTypedEnum::class, $enumBySubtype);
    }

    protected function getInheritedEnum($value)
    {
        if (!Type::hasType(TestInheritedSelfTypedEnum::getTypeName())) {
            TestInheritedSelfTypedEnum::registerSelf();
        }
        $enum = TestInheritedSelfTypedEnum::getEnum($value);

        return $enum;
    }

    protected function getTestSubTypeClass()
    {
        return TestInheritedSelfTypedEnum::class;
    }
}

/** inner */
class TestInheritedSelfTypedEnum extends SelfTypedEnum
{

}
