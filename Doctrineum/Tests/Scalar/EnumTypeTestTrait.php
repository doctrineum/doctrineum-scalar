<?php
namespace Doctrineum\Tests\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\EnumInterface;
use Doctrineum\Scalar\EnumType;

trait EnumTypeTestTrait
{
    /**
     * @return \Doctrineum\Scalar\EnumType|\Doctrineum\Scalar\SelfTypedEnum
     */
    protected function getEnumTypeClass()
    {
        return preg_replace('~Test$~', '', static::class);
    }

    /**
     * @return \Doctrineum\Scalar\Enum|\Doctrineum\Scalar\SelfTypedEnum
     */
    protected function getRegisteredEnumClass()
    {
        return preg_replace('~(Type)?Test$~', '', static::class);
    }

    /**
     * This is called after every test
     */
    protected function tearDown()
    {
        \Mockery::close();

        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = Type::getType($enumTypeClass::getTypeName(), $enumTypeClass);
        /** @var EnumType $enumType */
        if ($enumType::hasSubtype(TestSubtype::class)) {
            /** @var \PHPUnit_Framework_TestCase $this */
            $this->assertTrue($enumType::removeSubtype(TestSubtype::class));
        }
    }

    /**
     * @test
     */
    public function can_be_registered()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        Type::addType($enumTypeClass::getTypeName(), $enumTypeClass);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertTrue(Type::hasType($enumTypeClass::getTypeName()));
    }

    /**
     * @test
     * @depends can_be_registered
     */
    public function instance_can_be_obtained()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $instance = Type::getType($enumTypeClass::getTypeName());
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf($enumTypeClass, $instance);

        return $instance;
    }


    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function type_name_is_as_expected(EnumType $enumType)
    {
        $enumTypeClass = $this->getEnumTypeClass();
        // like self_typed_enum
        $typeName = $this->convertToTypeName($enumTypeClass);
        // like SELF_TYPED_ENUM
        $constantName = strtoupper($typeName);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertTrue(defined("$enumTypeClass::$constantName"));
        $this->assertSame($enumTypeClass::getTypeName(), $typeName);
        $this->assertSame($typeName, constant("$enumTypeClass::$constantName"));
        $this->assertSame($enumType::getTypeName(), $enumTypeClass::getTypeName());
    }

    /**
     * @param string $className
     * @return string
     */
    private function convertToTypeName($className)
    {
        $withoutType = preg_replace('~Type$~', '', $className);
        $parts = explode('\\', $withoutType);
        $baseClassName = $parts[count($parts) -1];
        preg_match_all('~(?<words>[A-Z][^A-Z]+)~', $baseClassName, $matches);
        $concatenated = implode('_', $matches['words']);

        return strtolower($concatenated);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function sql_declaration_is_valid(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $sql = $enumType->getSQLDeclaration([], $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function enum_with_null_to_database_value_is_null(EnumType $enumType)
    {
        $nullEnum = \Mockery::mock(EnumInterface::class);
        $nullEnum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn(null);
        /** @var EnumInterface $nullEnum */
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertNull($enumType->convertToDatabaseValue($nullEnum, $platform));
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function enum_as_database_value_is_string_value_of_that_enum(EnumType $enumType)
    {
        $enum = \Mockery::mock(EnumInterface::class);
        $enum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn($value = 'foo');
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var EnumInterface $enum */
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
    }

    /**
     * conversion to PHP tests
     */

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function null_to_php_value_creates_enum(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(null, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertNull($enum->getEnumValue());
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function string_to_php_value_is_enum_with_that_string(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($string, $enum->getEnumValue());
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($emptyString, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function integer_to_php_value_is_enum_with_that_integer(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($integer, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function zero_integer_to_php_value_is_enum_with_that_zero_integer(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($zeroInteger, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function float_to_php_value_is_enum_with_that_float(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($float, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function zero_float_to_php_value_is_enum_with_that_zero_float(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($zeroFloat, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function false_to_php_value_is_enum_with_that_false(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($false = false, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($false, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function true_to_php_value_is_enum_with_that_true(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($true, $enum->getEnumValue());
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function object_with_to_string_to_php_value_is_enum_with_that_string(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(new WithToStringTestObject($value = 'foo'), $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertSame($value, $enum->getEnumValue());
        $this->assertSame($value, (string)$enum);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue([], $platform);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function resource_to_php_value_cause_exception(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(tmpfile(), $platform);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function object_to_php_value_cause_exception(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(new \stdClass(), $platform);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function callback_to_php_value_cause_exception(EnumType $enumType)
    {
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }

    /**
     * subtype tests
     */

    /**
     * @param EnumType $enumType
     * @return EnumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function can_register_subtype(EnumType $enumType)
    {
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertTrue($enumType::addSubtype(TestSubtype::class, '~foo~'));
        $this->assertTrue($enumType::hasSubtype(TestSubtype::class));

        return $enumType;
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends can_register_subtype
     */
    public function can_unregister_subtype(EnumType $enumType)
    {
        /**
         * @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this
         *
         * The subtype is unregistered because of tearDown clean up
         * @see EnumTypeTestTrait::tearDown
         */
        $this->assertFalse($enumType::hasSubtype(TestSubtype::class));
        $this->assertTrue($enumType::addSubtype(TestSubtype::class, '~foo~'));
        $this->assertTrue($enumType::removeSubtype(TestSubtype::class));
        $this->assertFalse($enumType::hasSubtype(TestSubtype::class));
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends can_register_subtype
     */
    public function subtype_returns_proper_enum(EnumType $enumType)
    {
        /**
         * @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this
         */
        $this->assertTrue($enumType::addSubtype(TestSubtype::class, $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = \Mockery::mock(AbstractPlatform::class);
        $matchingValueToConvert = 'A string with some specific string inside.';
        $this->assertRegExp($regexp, $matchingValueToConvert);
        /**
         * Used TestSubtype returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubtype::getEnum
         */
        $this->assertSame($matchingValueToConvert, $enumType->convertToPHPValue($matchingValueToConvert, $abstractPlatform));
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends can_register_subtype
     */
    public function default_enum_is_given_if_subtype_does_not_match(EnumType $enumType)
    {
        /**
         * @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this
         */
        $this->assertTrue($enumType::addSubtype(TestSubtype::class, $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = \Mockery::mock(AbstractPlatform::class);
        $nonMatchingValueToConvert = 'A string without that specific string.';
        $this->assertNotRegExp($regexp, $nonMatchingValueToConvert);
        /**
         * Used TestSubtype returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubtype::getEnum
         */
        $enum = $enumType->convertToPHPValue($nonMatchingValueToConvert, $abstractPlatform);
        $this->assertNotSame($nonMatchingValueToConvert, $enum);
        $this->assertInstanceOf(EnumInterface::class, $enum);
        $this->assertSame($nonMatchingValueToConvert, (string)$enum);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \LogicException
     * @expectedExceptionMessage Subtype of class 'Doctrineum\\Tests\\Scalar\\TestSubtype' is already registered
     */
    public function registering_same_subtype_again_throws_exception(EnumType $enumType)
    {
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertFalse($enumType::hasSubtype(TestSubtype::class));
        $this->assertTrue($enumType::addSubtype(TestSubtype::class, '~foo~'));
        // registering twice - should thrown an exception
        $enumType::addSubtype(TestSubtype::class, '~foo~');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \LogicException
     * @expectedExceptionMessage Subtype class 'NonExistingClassName' has not been found
     */
    public function registering_non_existing_subtype_class_throws_exception(EnumType $enumType)
    {
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $enumType::addSubtype('NonExistingClassName', '~foo~');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \LogicException
     * @expectedExceptionMessage Subtype class 'stdClass' lacks required method "getEnum"
     */
    public function registering_subtype_class_without_proper_method_throws_exception(EnumType $enumType)
    {
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $enumType::addSubtype(\stdClass::class, '~foo~');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \LogicException
     * @expectedExceptionMessage The given regexp is not enclosed by same delimiters and therefore is not valid: 'foo~'
     */
    public function registering_subtype_with_invalid_regexp_throws_exception(EnumType $enumType)
    {
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $enumType::addSubtype(TestSubtype::class, /* missing opening delimiter */
            'foo~');
    }

}

/** inner */
class TestSubtype
{
    public static function getEnum($value)
    {
        return $value;
    }
}
