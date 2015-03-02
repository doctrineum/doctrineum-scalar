<?php
namespace Doctrineum\Tests\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\EnumInterface;

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


    protected function setUp()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        if (Type::hasType($enumTypeClass::getTypeName())) {
            Type::overrideType($enumTypeClass::getTypeName(), $enumTypeClass);
        } else {
            Type::addType($enumTypeClass::getTypeName(), $enumTypeClass);
        }
    }

    /**
     * This is just for sure - every test should close the Mockery and therefore evaluate expectations itself.
     */
    protected function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @return \Doctrine\DBAL\Types\Type
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createObjectInstance()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        return $enumTypeClass::getType($enumTypeClass::getTypeName());
    }

    /** @test */
    public function type_name_is_as_expected()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertSame('enum', $enumTypeClass::getTypeName());
        $this->assertSame($enumTypeClass::ENUM, $enumTypeClass::getTypeName());
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        $this->assertSame($enumType::getTypeName(), $enumTypeClass::getTypeName());
    }

    /** @test */
    public function instance_can_be_obtained()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $instance = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf($enumTypeClass, $instance);
    }

    /** @test */
    public function sql_declaration_is_valid()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $sql = $enumType->getSQLDeclaration([], $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @test
     */
    public function enum_with_null_to_database_value_is_null()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        $nullEnum = \Mockery::mock(EnumInterface::class);
        $nullEnum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn(null);
        /** @var EnumInterface $nullEnum */
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertNull($enumType->convertToDatabaseValue($nullEnum, $platform));
        \Mockery::close();
    }

    /**
     * @test
     */
    public function enum_as_database_value_is_string_value_of_that_enum()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        $enum = \Mockery::mock(EnumInterface::class);
        $enum->shouldReceive('getEnumValue')
            ->once()
            ->andReturn($value = 'foo');
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var EnumInterface $enum */
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
        \Mockery::close();
    }

    /**
     * @test
     */
    public function null_to_php_value_creates_enum()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(null, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertNull($enum->getEnumValue());
    }

    /**
     * @test
     */
    public function string_to_php_value_is_enum_with_that_string()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($string, $enum->getEnumValue());
    }

    /**
     * @test
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($emptyString, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function integer_to_php_value_is_enum_with_that_integer()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($integer, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function zero_integer_to_php_value_is_enum_with_that_zero_integer()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($zeroInteger, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function float_to_php_value_is_enum_with_that_float()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($float, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function zero_float_to_php_value_is_enum_with_that_zero_float()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($zeroFloat, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function false_to_php_value_is_enum_with_that_false()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($false = false, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($false, $enum->getEnumValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function true_to_php_value_is_enum_with_that_true()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($true, $enum->getEnumValue());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue([], $platform);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function resource_to_php_value_cause_exception()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(tmpfile(), $platform);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function object_to_php_value_cause_exception()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(new \stdClass(), $platform);
    }

    /**
     * @test
     */
    public function object_with_to_string_to_php_value_is_enum_with_that_string()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(new WithToStringTestObject($value = 'foo'), $platform);
        /** @var \PHPUnit_Framework_TestCase|EnumTypeTestTrait $this */
        $this->assertInstanceOf($this->getRegisteredEnumClass(),  $enum);
        $this->assertSame($value, $enum->getEnumValue());
        $this->assertSame($value, (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function callback_to_php_value_cause_exception()
    {
        $enumTypeClass = $this->getEnumTypeClass();
        $enumType = $enumTypeClass::getType($enumTypeClass::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }
}
