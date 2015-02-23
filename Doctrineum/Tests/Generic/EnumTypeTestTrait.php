<?php
namespace Doctrineum\Tests\Generic;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Generic\Enum;
use Doctrineum\Generic\EnumType;

trait EnumTypeTestTrait {

    // SET UP

    protected function setUp()
    {
        if (Type::hasType(EnumType::getTypeName())) {
            Type::overrideType(EnumType::getTypeName(), EnumType::class);
        } else {
            Type::addType(EnumType::getTypeName(), EnumType::class);
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
        return EnumType::getType(EnumType::getTypeName());
    }

    // TESTS THEMSELVES

    /** @test */
    public function instance_can_be_obtained()
    {
        $instance = EnumType::getType(EnumType::getTypeName());
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(EnumType::class, $instance);
    }

    /** @test */
    public function sql_declaration_is_valid()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
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
        $enumType = EnumType::getType(EnumType::getTypeName());
        $nullEnum = \Mockery::mock(Enum::class);
        $nullEnum->shouldReceive('getValue')
            ->once()
            ->andReturn(null);
        /** @var Enum $nullEnum */
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
        $enumType = EnumType::getType(EnumType::getTypeName());
        $enum = \Mockery::mock(Enum::class);
        $enum->shouldReceive('getValue')
            ->once()
            ->andReturn($value = 'foo');
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var Enum $enum */
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
        \Mockery::close();
    }

    /**
     * @test
     */
    public function null_to_php_value_creates_enum()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(null, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertNull($enum->getValue());
    }

    /**
     * @test
     */
    public function string_to_php_value_is_enum_with_that_string()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($string, $enum->getValue());
    }

    /**
     * @test
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($emptyString, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function integer_to_php_value_is_enum_with_that_integer()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($integer, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function zero_integer_to_php_value_is_enum_with_that_zero_integer()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($zeroInteger, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function float_to_php_value_is_enum_with_that_float()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($float, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function zero_float_to_php_value_is_enum_with_that_zero_float()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($zeroFloat, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function false_to_php_value_is_enum_with_that_false()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($false = false, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($false, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @test
     */
    public function true_to_php_value_is_enum_with_that_true()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($true, $enum->getValue());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue([], $platform);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function resource_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(tmpfile(), $platform);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function object_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(new \stdClass(), $platform);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function callback_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }
}
