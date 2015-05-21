<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Types\Type;
use Doctrineum\Tests\Scalar\EnumTestTrait;
use Doctrineum\Tests\Scalar\EnumTypeTestTrait;

/**
 * Class SelfTypedEnumTest
 * @package Doctrineum\Scalar
 */
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
        SelfTypedEnum::addSubTypeEnum($this->getSubTypeEnumClass(), $pattern = '~foo~');
        $this->assertRegExp($pattern, $enumValue = 'foo bar baz');
        $enumBySubType = SelfTypedEnum::getEnum($enumValue);
        $this->assertInstanceOf($this->getSubTypeEnumClass(), $enumBySubType);
    }

    /**
     * @test
     * @depends can_be_registered
     * @return SelfTypedEnum
     */
    public function can_create_enum_from_itself()
    {
        /** @var SelfTypedEnum $selfTypedEnum */
        $selfTypedEnum = Type::getType(SelfTypedEnum::getTypeName());
        // enum from self typed enum is created by cloning (because of Doctrine type, as the parent, limitations)
        $enum = $selfTypedEnum::getEnum('foo');
        $this->assertInstanceOf('Doctrineum\Scalar\SelfTypedEnum', $enum);

        return $enum;
    }

    /**
     * @param SelfTypedEnum $enum
     *
     * @test
     * @depends can_create_enum_from_itself
     * @expectedException \Doctrineum\Scalar\Exceptions\CanNotBeCloned
     */
    public function can_not_clone_created_enum(SelfTypedEnum $enum)
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /**
     * @test
     * @depends can_be_registered
     * @expectedException \Doctrineum\Scalar\Exceptions\CanNotBeCloned
     */
    public function can_not_clone_self_typed_enum_type_after_enum_creation()
    {
        /** @var SelfTypedEnum $selfTypedEnum */
        $selfTypedEnum = Type::getType(SelfTypedEnum::getTypeName());
        // creates an enum from self typed enum by cloning (because of Doctrine type, as the parent, limitations)
        $selfTypedEnum::getEnum('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $selfTypedEnum;
    }

    // inner providers

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
        return 'Doctrineum\Scalar\TestAnotherSelfTypedEnumType';
    }

    /**
     * @return string|TestSubTypeSelfTypedEnum
     */
    protected function getSubTypeEnumClass()
    {
        return 'Doctrineum\Scalar\TestSubTypeSelfTypedEnum';
    }

    /**
     * @return string|TestAnotherSubTypeSelfTypedEnum
     */
    protected function getAnotherSubTypeEnumClass()
    {
        return 'Doctrineum\Scalar\TestAnotherSubTypeSelfTypedEnum';
    }

}

/** inner */
class TestInheritedSelfTypedEnum extends SelfTypedEnum
{

}

class TestAnotherSelfTypedEnumType extends SelfTypedEnum
{

}

class TestSubTypeSelfTypedEnum extends SelfTypedEnum
{

}

class TestAnotherSubTypeSelfTypedEnum extends SelfTypedEnum
{

}
