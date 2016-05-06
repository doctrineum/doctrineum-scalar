<?php
namespace Doctrineum\Tests\Scalar;

use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\ScalarEnumType;
use Granam\Tests\Tools\TestWithMockery;

abstract class AbstractTypeTest extends TestWithMockery
{

    /**
     * @test
     */
    public function I_can_register_it()
    {
        $typeClass = $this->getTypeClass();
        $typeClass::registerSelf();
        self::assertTrue(Type::hasType($this->getExpectedTypeName()));
        self::assertTrue($typeClass::isRegistered());
    }

    /**
     * @return ScalarEnumType|string
     */
    protected function getTypeClass()
    {
        return preg_replace('~[\\\]Tests([\\\].+)Test$~', '$1', static::class);
    }

    /**
     * @param string|null $typeClass
     * @return string
     */
    protected function getExpectedTypeName($typeClass = null)
    {
        // like Doctrineum\Scalar\EnumType = EnumType
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', $typeClass ?: $this->getTypeClass());
        // like EnumType = Enum
        $baseTypeName = preg_replace('~Type$~', '', $baseClassName);

        // like FooBarEnum = Foo_Bar_Enum = foo_bar_enum
        return strtolower(preg_replace('~(\w)([A-Z])~', '$1_$2', $baseTypeName));
    }

    /**
     * @test
     * @depends I_can_register_it
     */
    public function I_can_get_instance()
    {
        $typeClass = $this->getTypeClass();
        $instance = Type::getType($this->getExpectedTypeName());
        self::assertInstanceOf($typeClass, $instance);

        return $instance;
    }

    /**
     * @test
     * @depends I_can_get_instance
     * @param Type $type
     */
    public function I_can_get_expected_type_name(Type $type)
    {
        $typeClass = $this->getTypeClass();
        // like self_typed_enum
        $typeName = $this->convertToTypeName($typeClass);
        // like SELF_TYPED_ENUM
        $constantName = strtoupper($typeName);
        self::assertTrue(defined("$typeClass::$constantName"));
        self::assertSame($this->getExpectedTypeName(), $typeName);
        self::assertSame($typeName, constant("$typeClass::$constantName"));
        self::assertSame($type->getName(), $this->getExpectedTypeName());
    }

    /**
     * @param string $className
     * @return string
     */
    protected function convertToTypeName($className)
    {
        $withoutType = preg_replace('~Type$~', '', $className);
        $parts = explode('\\', $withoutType);
        $baseClassName = end($parts);
        preg_match_all('~(?<words>[A-Z][^A-Z]+)~', $baseClassName, $matches);
        $concatenated = implode('_', $matches['words']);

        return strtolower($concatenated);
    }

    /**
     * @return \Doctrineum\Scalar\ScalarEnum|string
     */
    protected function getRegisteredClass()
    {
        return preg_replace('~Type$~', '', $this->getTypeClass());
    }

}