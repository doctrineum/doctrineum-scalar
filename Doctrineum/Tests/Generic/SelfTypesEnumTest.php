<?php
namespace Doctrineum\Tests\Generic;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrineum\Generic\SelfTypedEnum;
use Granam\Strict\Object\Tests\StrictObjectTestTrait;

class SelfTypesEnumTest extends \PHPUnit_Framework_TestCase
{
    use StrictObjectTestTrait;

    /**
     * This is just for sure - every test should close the Mockery and therefore evaluate expectations itself.
     */
    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function setUp()
    {
        if (SelfTypedEnum::hasType(SelfTypedEnum::getTypeName())) {
            SelfTypedEnum::overrideType(SelfTypedEnum::getTypeName(), SelfTypedEnum::class);
        } else {
            SelfTypedEnum::addType(SelfTypedEnum::getTypeName(), SelfTypedEnum::class);
        }
    }

    /**
     * For strict object tests,
     * @see StrictObjectTestTrait
     *
     * @return \Doctrine\DBAL\Types\Type
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createObjectInstance()
    {
        return SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
    }


    /**
     * EnumType tests
     */

    /** @test */
    public function can_create_type_instance()
    {
        $instance = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        $this->assertInstanceOf(SelfTypedEnum::class, $instance);
    }

    /** @test */
    public function sql_declaration_is_valid()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        $nullEnum = SelfTypedEnum::getEnum(null);
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $this->assertNull($enumType->convertToDatabaseValue($nullEnum, $platform));
    }

    /**
     * @test
     */
    public function enum_as_database_value_is_string_value_of_that_enum()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        $enum = SelfTypedEnum::getEnum($value = 'foo');
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, $platform));
    }

    /**
     * @test
     */
    public function null_to_php_value_creates_enum()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue(null, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
        $this->assertNull($enum->getEnumValue());
    }

    /**
     * @test
     */
    public function string_to_php_value_is_enum_with_that_string()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
        $this->assertSame($string, $enum->getEnumValue());
    }

    /**
     * @test
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($false = false, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
        $this->assertSame($true, $enum->getEnumValue());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception()
    {
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
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
        $enumType = SelfTypedEnum::getType(SelfTypedEnum::getTypeName());
        /** @var AbstractPlatform $platform */
        $platform = \Mockery::mock(AbstractPlatform::class);
        $enumType->convertToPHPValue(function () {
        }, $platform);
    }

    /**
     * Enum tests
     */

    /** @test */
    public function can_create_enum_instance()
    {
        $instance = SelfTypedEnum::getEnum('foo');
        $this->assertInstanceOf(SelfTypedEnum::class, $instance);
    }

    /** @test */
    public function same_instance_for_same_name_is_returned()
    {
        $firstInstance = SelfTypedEnum::getEnum('foo');
        $secondInstance = SelfTypedEnum::getEnum('bar');
        $thirdInstance = SelfTypedEnum::getEnum('foo');
        $this->assertNotSame($firstInstance, $secondInstance);
        $this->assertSame($firstInstance, $thirdInstance);
    }

    /** @test */
    public function returns_same_value_as_created_with()
    {
        $enum = SelfTypedEnum::getEnum('foo');
        $this->assertSame('foo', $enum->getEnumValue());
    }

    /** @test */
    public function as_string_is_of_same_value_as_created_with()
    {
        $enum = SelfTypedEnum::getEnum('foo');
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\CanNotBeCloned
     */
    public function can_not_be_cloned()
    {
        $enum = SelfTypedEnum::getEnum('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /** @test */
    public function any_enum_namespace_is_accepted()
    {
        $enum = SelfTypedEnum::getEnum('foo', 'bar');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(SelfTypedEnum::class, $enum);
        $this->assertSame('foo', $enum->getEnumValue());
        $this->assertSame('foo', (string)$enum);
    }

}
