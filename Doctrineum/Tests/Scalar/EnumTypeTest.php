<?php
namespace Doctrineum\Tests\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\Enum;
use Doctrineum\Scalar\EnumInterface;
use Doctrineum\Scalar\EnumType;

class EnumTypeTest extends \PHPUnit_Framework_TestCase

{
    /**
     * @test
     */
    public function can_be_registered()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumTypeClass::registerSelf();
        $this->assertTrue(Type::hasType($enumTypeClass::getTypeName()));
        $this->assertTrue($enumTypeClass::isRegistered());
    }

    /**
     * @return \Doctrineum\Scalar\EnumType
     */
    protected function getEnumTypeClass()
    {
        return '\Doctrineum\Scalar\EnumType';
    }

    /**
     * @test
     * @depends can_be_registered
     */
    public function self_registering_again_returns_false()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $this->assertTrue(Type::hasType($enumTypeClass::getTypeName()));
        $this->assertTrue($enumTypeClass::isRegistered());
        $this->assertFalse($enumTypeClass::registerSelf());
    }

    /**
     * @test
     * @depends can_be_registered
     */
    public function instance_can_be_obtained()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $instance = Type::getType($enumTypeClass::getTypeName());
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
        $this->assertTrue(defined("$enumTypeClass::$constantName"));
        $this->assertSame($enumTypeClass::getTypeName(), $typeName);
        $this->assertSame($typeName, constant("$enumTypeClass::$constantName"));
        $this->assertSame($enumType::getTypeName(), $enumTypeClass::getTypeName());
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private function convertToTypeName($className)
    {
        $withoutType = preg_replace('~Type$~', '', $className);
        $parts = explode('\\', $withoutType);
        $baseClassName = $parts[count($parts) - 1];
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

        $platform = $this->getPlatform();
        $sql = $enumType->getSQLDeclaration([], $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @return AbstractPlatform
     */
    protected function getPlatform()
    {
        return \Mockery::mock('Doctrine\DBAL\Platforms\AbstractPlatform');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function enum_with_null_to_database_value_is_null(EnumType $enumType)
    {
        $nullEnum = \Mockery::mock('Doctrineum\Scalar\EnumInterface');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $nullEnum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn(null);
        /** @var EnumInterface $nullEnum */

        $platform = $this->getPlatform();
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
        $enum = \Mockery::mock('Doctrineum\Scalar\EnumInterface');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $enum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn($value = 'foo');

        $platform = $this->getPlatform();
        /** @var EnumInterface $enum */
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function null_to_php_value_creates_enum(EnumType $enumType)
    {

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue(null, $platform);
        $this->assertInstanceOf($this->getRegisteredEnumClass(), $enum);
        $this->assertNull($enum->getEnumValue());
    }

    /**
     * conversion to PHP tests
     */

    /**
     * @return \Doctrineum\Scalar\Enum
     */
    protected function getRegisteredEnumClass()
    {
        return '\Doctrineum\Scalar\Enum';
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function string_to_php_value_is_enum_with_that_string(EnumType $enumType)
    {

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
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

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
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

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
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

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
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
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
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
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
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
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($false = false, $platform);
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
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($true = true, $platform);
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
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue(new WithToStringTestObject($value = 'foo'), $platform);
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
        $platform = $this->getPlatform();
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
        $platform = $this->getPlatform();
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
        $platform = $this->getPlatform();
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
        $platform = $this->getPlatform();
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }

    /**
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToDatabaseValue
     */
    public function conversion_of_non_object_to_database_cause_exception()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = Type::getType($enumTypeClass::getTypeName());
        $enumType->convertToDatabaseValue(new \stdClass(), $this->getPlatform());
    }

    /**
     * @test
     * @depends instance_can_be_obtained
     */
    public function enum_type_name_is_same_as_name()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = Type::getType($enumTypeClass::getTypeName());
        $this->assertSame($enumTypeClass::getTypeName(), $enumType->getName());
    }

    /**
     * @test
     * @depends instance_can_be_obtained
     */
    public function requires_sql_comment_hint()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = Type::getType($enumTypeClass::getTypeName());
        $this->assertTrue($enumType->requiresSQLCommentHint($this->getPlatform()));
    }

    /**
     * SUBTYPE TESTS
     */

    /**
     * @param EnumType $enumType
     *
     * @return EnumType
     *
     * @test
     * @depends instance_can_be_obtained
     */
    public function can_register_subtype(EnumType $enumType)
    {
        $this->assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        $this->assertTrue($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));

        return $enumType;
    }

    /**
     * @return string|TestSubTypeEnum
     */
    protected function getSubTypeEnumClass()
    {
        return TestSubTypeEnum::getClass();
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
         * The subtype is unregistered because of tearDown clean up
         * @see EnumTypeTestTrait::tearDown
         */
        $this->assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        $this->assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        $this->assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        $this->assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
    }

    /**
     * @param EnumType $subType
     *
     * @test
     * @depends can_register_subtype
     */
    public function subtype_returns_proper_enum(EnumType $subType)
    {
        $this->assertTrue($subType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = $this->getPlatform();
        $matchingValueToConvert = 'A string with some specific string inside.';
        $this->assertRegExp($regexp, $matchingValueToConvert);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enumFromSubType = $subType->convertToPHPValue($matchingValueToConvert, $abstractPlatform);
        $this->assertInstanceOf($this->getSubTypeEnumClass(), $enumFromSubType);
        $this->assertSame($matchingValueToConvert, (string)$enumFromSubType);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends can_register_subtype
     */
    public function default_enum_is_given_if_subtype_does_not_match(EnumType $enumType)
    {
        $this->assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = $this->getPlatform();
        $nonMatchingValueToConvert = 'A string without that specific string.';
        $this->assertNotRegExp($regexp, $nonMatchingValueToConvert);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enum = $enumType->convertToPHPValue($nonMatchingValueToConvert, $abstractPlatform);
        $this->assertNotSame($nonMatchingValueToConvert, $enum);
        $this->assertInstanceOf('Doctrineum\Scalar\EnumInterface', $enum);
        $this->assertSame($nonMatchingValueToConvert, (string)$enum);
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumIsAlreadyRegistered
     */
    public function registering_same_subtype_again_throws_exception(EnumType $enumType)
    {
        $this->assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        $this->assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        // registering twice - should thrown an exception
        $enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumHasToBeEnum
     */
    public function registering_invalid_subtype_class_throws_exception(EnumType $enumType)
    {
        $enumType::addSubTypeEnum('stdClass', '~foo~');
    }

    /**
     * @param EnumType $enumType
     *
     * @test
     * @depends instance_can_be_obtained
     * @expectedException \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     * @expectedExceptionMessage The given regexp is not enclosed by same delimiters and therefore is not valid: 'foo~'
     */
    public function registering_subtype_with_invalid_regexp_throws_exception(EnumType $enumType)
    {
        $enumType::addSubTypeEnum(
            $this->getSubTypeEnumClass(),
            'foo~' // missing opening delimiter
        );
    }

    /**
     * @test
     *
     * @depends can_be_registered
     */
    public function can_register_another_enum_type()
    {
        $anotherEnumType = $this->getAnotherEnumTypeClass();
        if (!$anotherEnumType::isRegistered()) {
            $this->assertTrue($anotherEnumType::registerSelf());
        } else {
            $this->assertFalse($anotherEnumType::registerSelf());
        }

        $this->assertTrue($anotherEnumType::isRegistered());
        $this->assertTrue(Type::hasType($anotherEnumType::getTypeName()));
    }

    /**
     * @return string|TestAnotherEnumType
     */
    protected function getAnotherEnumTypeClass()
    {
        return TestAnotherEnumType::getClass();
    }

    /**
     * @test
     *
     * @depends can_register_another_enum_type
     */
    public function different_types_with_same_subtype_regexp_distinguish_them()
    {
        /** @var EnumType $enumTypeClass */
        $enumTypeClass = $this->getEnumTypeClass();
        if ($enumTypeClass::hasSubTypeEnum($this->getSubTypeEnumClass())) {
            $enumTypeClass::removeSubTypeEnum($this->getSubTypeEnumClass());
        }
        $enumTypeClass::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~searching pattern~');

        $anotherEnumTypeClass = $this->getAnotherEnumTypeClass();
        if ($anotherEnumTypeClass::hasSubTypeEnum($this->getAnotherSubTypeEnumClass())) {
            $anotherEnumTypeClass::removeSubTypeEnum($this->getAnotherSubTypeEnumClass());
        }
        // regexp is same, sub-type is not
        $anotherEnumTypeClass::addSubTypeEnum($this->getAnotherSubTypeEnumClass(), $regexp);

        $value = 'some string fitting to searching pattern';
        $this->assertRegExp($regexp, $value);

        $enumType = Type::getType($enumTypeClass::getTypeName());
        $enumSubType = $enumType->convertToPHPValue($value, $this->getPlatform());
        $this->assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        $this->assertSame($value, "$enumSubType");

        $anotherEnumType = Type::getType($anotherEnumTypeClass::getTypeName());
        $anotherEnumSubType = $anotherEnumType->convertToPHPValue($value, $this->getPlatform());
        $this->assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        $this->assertSame($value, "$anotherEnumSubType");

        // registered sub-types were different, just regexp was the same - let's test if they are kept separately
        $this->assertNotSame($enumSubType, $anotherEnumSubType);
    }

    /**
     * @return string|TestAnotherSubTypeEnum
     */
    protected function getAnotherSubTypeEnumClass()
    {
        return TestAnotherSubTypeEnum::getClass();
    }

    /**
     * @test
     * @depends can_be_registered
     */
    public function repeated_self_registration_returns_false()
    {
        $this->assertFalse(EnumType::registerSelf());
    }

    /** @test */
    public function can_use_subtype()
    {
        EnumType::addSubTypeEnum($this->getSubTypeEnumClass(), $pattern = '~foo~');
        $this->assertRegExp($pattern, $enumValue = 'foo bar baz');
        $enumBySubType = EnumType::getType(EnumType::ENUM)->convertToPHPValue($enumValue, $this->getPlatform());
        $this->assertInstanceOf($this->getSubTypeEnumClass(), $enumBySubType);
    }

    /**
     * This is called after every test
     */
    protected function tearDown()
    {
        \Mockery::close();

        $enumTypeClass = $this->getEnumTypeClass();
        if (Type::hasType($enumTypeClass::getTypeName())) {
            $enumType = Type::getType($enumTypeClass::getTypeName());
            /** @var EnumType $enumType */
            if ($enumType::hasSubTypeEnum($this->getSubTypeEnumClass())) {
                $this->assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
            }
        }
    }

}

/** inner */
class TestSubTypeEnum extends Enum
{

}

class TestAnotherSubTypeEnum extends Enum
{

}

class TestAnotherEnumType extends EnumType
{

}
