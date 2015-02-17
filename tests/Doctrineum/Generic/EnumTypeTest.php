<?php
namespace Doctrineum\Generic;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Granam\Strict\Object\Tests\StrictObjectTestTrait;

class EnumTypeTest extends \PHPUnit_Framework_TestCase
{
    use StrictObjectTestTrait;

    // SET UP

    protected function setUp()
    {
        if (Type::hasType(EnumType::TYPE)) {
            Type::overrideType(EnumType::TYPE, EnumType::class);
        } else {
            Type::addType(EnumType::TYPE, EnumType::class);
        }
    }

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
        return EnumType::getType(EnumType::TYPE);
    }

    // TESTS THEMSELVES

    /** @test */
    public function instance_can_be_obtained()
    {
        $instance = EnumType::getType(EnumType::TYPE);
        $this->assertInstanceOf(EnumType::class, $instance);
    }

    /** @test */
    public function sql_declaration_is_valid()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $sql = $enumType->getSQLDeclaration([], $platform);
        $this->assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @test
     */
    public function enum_with_null_to_database_value_is_null()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $nullEnum = \Mockery::mock(Enum::class);
        $nullEnum->shouldReceive('getValue')
            ->once()
            ->andReturn(null);
        /** @var Enum $nullEnum */
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $this->assertNull($enumType->convertToDatabaseValue($nullEnum, $platform));
    }

    /**
     * @test
     */
    public function enum_as_database_value_is_string_value_of_that_enum()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enum = \Mockery::mock(Enum::class);
        $enum->shouldReceive('getValue')
            ->once()
            ->andReturn($value = 'foo');
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        /** @var Enum $enum */
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
    }

    /**
     * @test
     */
    public function null_to_php_value_creates_enum()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(null, $platform);
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertNull($enum->getValue());
    }

    /**
     * @test
     */
    public function string_to_php_value_is_enum_with_that_string()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($string, $enum->getValue());
    }

    /**
     * @test
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($false = false, $platform);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($true, $enum->getValue());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
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
        $enumType = EnumType::getType(EnumType::TYPE);
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
        $enumType = EnumType::getType(EnumType::TYPE);
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
        $enumType = EnumType::getType(EnumType::TYPE);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }
}
