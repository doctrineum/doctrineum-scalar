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
        SelfTypedEnum::addSubTypeEnum(TestSelfTypedSubTypeEnum::class, $pattern = '~foo~');
        $this->assertRegExp($pattern, $enumValue = 'foo bar baz');
        $enumBySubType = SelfTypedEnum::getEnum($enumValue);
        $this->assertInstanceOf(TestSelfTypedSubTypeEnum::class, $enumBySubType);
    }

    /**
     * @param $value
     * @return SelfTypedEnum
     */
    protected function getInheritedEnum($value)
    {
        if (!Type::hasType(TestInheritedSelfTypedEnum::getTypeName())) {
            TestInheritedSelfTypedEnum::registerSelf();
        }
        $enum = TestInheritedSelfTypedEnum::getEnum($value);

        return $enum;
    }

    /**
     * @return string|TestAnotherSelfTypedEnumType
     */
    protected function getAnotherEnumTypeClass()
    {
        return TestAnotherSelfTypedEnumType::class;
    }

    /**
     * @return string|TestSelfTypedSubTypeEnum
     */
    protected function getTestSubTypeEnumClass()
    {
        return TestSelfTypedSubTypeEnum::class;
    }

    /**
     * @return string|TestAnotherSelfTypedSubTypeEnum
     */
    protected function getTestAnotherSubTypeEnumClass()
    {
        return TestAnotherSelfTypedSubTypeEnum::class;
    }

}

/** inner */
class TestInheritedSelfTypedEnum extends SelfTypedEnum
{

}

class TestAnotherSelfTypedEnumType extends SelfTypedEnum
{

}

class TestSelfTypedSubTypeEnum extends SelfTypedEnum
{

}

class TestAnotherSelfTypedSubTypeEnum extends SelfTypedEnum
{

}
