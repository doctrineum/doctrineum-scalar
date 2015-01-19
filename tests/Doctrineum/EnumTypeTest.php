<?php
namespace Doctrineum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Granam\StrictObject\Tests\StrictObjectTestTrait;

class EnumTypeTest extends \PHPUnit_Framework_TestCase
{
    use StrictObjectTestTrait;

    protected function setUp()
    {
        if (\Doctrine\DBAL\Types\Type::hasType(EnumType::TYPE)) {
            \Doctrine\DBAL\Types\Type::overrideType(EnumType::TYPE, EnumType::class);
        } else {
            \Doctrine\DBAL\Types\Type::addType(EnumType::TYPE, EnumType::class);
        }
    }

    /**
     * @return \Doctrine\DBAL\Types\Type
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createObjectInstance()
    {
        return EnumType::getType(EnumType::TYPE);
    }

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
        $sql = $enumType->getSQLDeclaration([], \Mockery::mock(AbstractPlatform::class));
        $this->assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @test
     */
    public function null_to_database_value_is_null()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $this->assertNull(null, $enumType->convertToDatabaseValue(null, \Mockery::mock(AbstractPlatform::class)));
    }

    /**
     * @test
     */
    public function enum_as_database_value_is_string_value_of_that_enum()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enum = \Mockery::mock(Enum::class);
        $enum->shouldReceive('getValue')
            ->andReturn($value = 'foo');
        $this->assertSame($value, $enumType->convertToDatabaseValue($enum, \Mockery::mock(AbstractPlatform::class)));
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\Logic
     */
    public function non_enum_type_as_database_value_throws_exception()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enumType->convertToDatabaseValue('foo', \Mockery::mock(AbstractPlatform::class));
    }

    /**
     * @test
     */
    public function null_to_php_value_is_null()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $this->assertNull($enumType->convertToPHPValue(null, \Mockery::mock(AbstractPlatform::class)));
    }

    /**
     * @test
     */
    public function string_to_php_value_is_enum_with_that_string()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enum = $enumType->convertToPHPValue($string = 'foo', \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($string, $enum->getValue());
    }

    /**
     * @test
     */
    public function empty_string_to_php_value_is_enum_with_that_empty_string()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enum = $enumType->convertToPHPValue($string = '', \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($string, $enum->getValue());
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
        $enum = $enumType->convertToPHPValue($integer = 12345, \Mockery::mock(AbstractPlatform::class));
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
        $enum = $enumType->convertToPHPValue($integer = 0, \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($integer, $enum->getValue());
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
        $enum = $enumType->convertToPHPValue($float = 12345.6789, \Mockery::mock(AbstractPlatform::class));
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
        $enum = $enumType->convertToPHPValue($float = 0, \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($float, $enum->getValue());
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
        $enum = $enumType->convertToPHPValue($false = false, \Mockery::mock(AbstractPlatform::class));
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
        $enum = $enumType->convertToPHPValue($true = true, \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($true, $enum->getValue());
    }

    /**
     * @test
     * @expectedException
     */
    public function non_scalar_to_php_value_cause_exception()
    {
        $enumType = EnumType::getType(EnumType::TYPE);
        $enum = $enumType->convertToPHPValue($true = true, \Mockery::mock(AbstractPlatform::class));
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame($true, $enum->getValue());
    }
}
