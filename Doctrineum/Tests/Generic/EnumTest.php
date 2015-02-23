<?php
namespace Doctrineum\Generic;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function can_create_instance()
    {
        $instance = Enum::get('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $instance);
    }

    /** @test */
    public function same_instance_for_same_name_is_returned()
    {
        $firstInstance = Enum::get('foo');
        $secondInstance = Enum::get('bar');
        $thirdInstance = Enum::get('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertNotSame($firstInstance, $secondInstance);
        $this->assertSame($firstInstance, $thirdInstance);
    }

    /** @test */
    public function returns_same_value_as_created_with()
    {
        $enum = Enum::get('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', $enum->getValue());
    }

    /** @test */
    public function as_string_is_of_same_value_as_created_with()
    {
        $enum = Enum::get('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\CanNotBeCloned
     */
    public function can_not_be_cloned()
    {
        $enum = Enum::get('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /** @test */
    public function any_enum_namespace_is_accepted()
    {
        $enum = Enum::get('foo', 'bar');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf(Enum::class, $enum);
        $this->assertSame('foo', $enum->getValue());
        $this->assertSame('foo', (string)$enum);
    }
}
