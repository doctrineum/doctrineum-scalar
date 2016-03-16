<?php
namespace Doctrineum\Tests\Scalar;

use Doctrineum\Scalar\ScalarEnum;

class ScalarEnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_create_it()
    {
        $enumClass = $this->getEnumClass();
        $instance = $enumClass::getEnum('foo');
        self::assertInstanceOf($enumClass, $instance);
    }

    /**
     * @return \Doctrineum\Scalar\ScalarEnum
     */
    protected function getEnumClass()
    {
        return ScalarEnum::getClass();
    }

    /**
     * @test
     */
    public function I_got_same_instance_for_same_name()
    {
        $enumClass = $this->getEnumClass();
        $firstInstance = $enumClass::getEnum($firstValue = 'foo');
        $secondInstance = $enumClass::getEnum($secondValue = 'bar');
        $thirdInstance = $enumClass::getEnum($firstValue);
        self::assertNotSame(
            $firstInstance,
            $secondInstance,
            "Instance of enum $enumClass with value $firstValue should not be same as instance with value $secondValue"
        );
        self::assertSame($firstInstance, $thirdInstance);
    }

    /** @test */
    public function I_got_same_value_as_I_created_with()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        self::assertSame('foo', $enum->getValue());
    }

    /** @test */
    public function I_got_same_value_as_string()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        self::assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\CanNotBeCloned
     */
    public function I_can_not_clone_it()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /** @test */
    public function I_can_create_it_by_to_string_object_and_got_back_that_value()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum(new WithToStringTestObject('foo'));
        self::assertSame('foo', $enum->getValue());
        self::assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function I_can_not_create_it_by_object_without_to_string()
    {
        $enumClass = $this->getEnumClass();
        $enumClass::getEnum(new \stdClass());
    }

    /**
     * @test
     */
    public function I_can_compare_enums()
    {
        $firstEnum = ScalarEnum::getEnum('foo');
        self::assertTrue($firstEnum->is($firstEnum), 'Enum should recognize itself');

        $secondEnum = ScalarEnum::getEnum($secondValue = 'bar');
        self::assertFalse($firstEnum->is($secondEnum), 'Same class enums but with different values should not be equal');
        self::assertFalse($secondEnum->is($firstEnum), 'Same class enums but with different values should not be equal');

        $childEnum = TestInheritedScalarEnum::getEnum($secondValue);
        self::assertFalse($firstEnum->is($childEnum), 'Parent class enum should not be equal to its child class');
        self::assertFalse($secondEnum->is($childEnum), 'Parent class enum should not be equal to its child even if with same value');
        self::assertFalse($childEnum->is($secondEnum), 'Child class enum should not be equal to its parent even if with same value');
    }

    /**
     * inner namespace test
     */

    /** @test */
    public function inherited_enum_with_same_value_lives_in_own_inner_namespace()
    {
        $enumClass = $this->getEnumClass();

        $enum = $enumClass::getEnum($value = 'foo');
        self::assertInstanceOf($enumClass, $enum);
        self::assertSame($value, $enum->getValue());
        self::assertSame($value, (string)$enum);

        $inDifferentNamespace = $this->getInheritedEnum($value);
        self::assertInstanceOf($enumClass, $inDifferentNamespace);
        self::assertSame($enum->getValue(), $inDifferentNamespace->getValue());
        self::assertNotSame($enum, $inDifferentNamespace);
    }

    protected function getInheritedEnum($value)
    {
        return new TestInheritedScalarEnum($value);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\EnumIsAlreadyBuilt
     */
    public function adding_an_existing_enum_cause_exception()
    {
        TestInvalidExistingScalarEnumUsage::forceGetting(false);
        TestInvalidExistingScalarEnumUsage::forceAdding(true);
        // getting twice to internally add twice
        TestInvalidExistingScalarEnumUsage::getEnum('foo');
        TestInvalidExistingScalarEnumUsage::getEnum('foo');
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\EnumIsNotBuilt
     */
    public function getting_an_non_existing_enum_cause_exception()
    {
        TestInvalidExistingScalarEnumUsage::forceAdding(false);
        TestInvalidExistingScalarEnumUsage::forceGetting(true);
        TestInvalidExistingScalarEnumUsage::getEnum('bar');
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function using_invalid_value_without_casting_cause_exception()
    {
        TestInvalidScalarEnumValueTest::getEnum(new \stdClass());
    }
}

/** inner */
class TestInvalidExistingScalarEnumUsage extends ScalarEnum
{
    private static $forceAdding = false;
    private static $forceGetting = false;

    public static function forceAdding($force = true)
    {
        self::$forceAdding = $force;
    }

    public static function forceGetting($force = true)
    {
        self::$forceGetting = $force;
    }

    protected static function getEnumFromNamespace($enumValue, $namespace)
    {
        $finalValue = static::convertToEnumFinalValue($enumValue);
        if (self::$forceAdding) {
            static::addBuiltEnum(static::createByValue($finalValue), $namespace);
        }

        if (self::$forceGetting) {
            return static::getBuiltEnum($finalValue, $namespace);
        }

        return null;
    }

    protected function getInheritedEnum($value)
    {
        return new TestInheritedScalarEnum($value);
    }
}

/** inner */
class TestInheritedScalarEnum extends ScalarEnum
{

}

class TestInvalidScalarEnumValueTest extends ScalarEnum
{

    protected static function convertToEnumFinalValue($value)
    {
        // intentionally no conversion at all
        return $value;
    }
}
