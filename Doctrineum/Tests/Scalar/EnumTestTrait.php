<?php
namespace Doctrineum\Tests\Scalar;

trait EnumTestTrait
{
    /**
     * @return \Doctrineum\Scalar\Enum|\Doctrineum\Scalar\SelfTypedEnum
     */
    protected function getEnumClass()
    {
        return preg_replace('~Test$~', '', static::class);
    }

    /** @test */
    public function can_create_instance()
    {
        $enumClass = $this->getEnumClass();
        $instance = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertInstanceOf($enumClass, $instance);
    }

    /** @test */
    public function same_instance_for_same_name_is_returned()
    {
        $enumClass = $this->getEnumClass();
        $firstInstance = $enumClass::getEnum($firstValue = 'foo');
        $secondInstance = $enumClass::getEnum($secondValue = 'bar');
        $thirdInstance = $enumClass::getEnum($firstValue);
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertNotSame(
            $firstInstance,
            $secondInstance,
            "Instance of enum $enumClass with value $firstValue should not be same as instance with value $secondValue"
        );
        $this->assertSame($firstInstance, $thirdInstance);
    }

    /** @test */
    public function returns_same_value_as_created_with()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', $enum->getEnumValue());
    }

    /** @test */
    public function as_string_is_of_same_value_as_created_with()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\CanNotBeCloned
     */
    public function can_not_be_cloned()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum('foo');
        /** @noinspection PhpExpressionResultUnusedInspection */
        clone $enum;
    }

    /** @test */
    public function with_to_string_object_is_of_same_value_as_object()
    {
        $enumClass = $this->getEnumClass();
        $enum = $enumClass::getEnum(new WithToStringTestObject('foo'));
        /** @var \PHPUnit_Framework_TestCase $this */
        $this->assertSame('foo', $enum->getEnumValue());
        $this->assertSame('foo', (string)$enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function object_without_to_string_cause_exception()
    {
        $enumClass = $this->getEnumClass();
        $enumClass::getEnum(new \stdClass());
    }
}

