<?php
namespace Doctrineum\Tests\Scalar;

use Doctrineum\Scalar\ScalarEnumInterface;
use Granam\Scalar\ScalarInterface;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_use_enum_interface_as_scalar()
    {
        self::assertTrue(is_a(ScalarEnumInterface::class, ScalarInterface::class, true));
    }

    /**
     * @test
     */
    public function I_got_enums_comparison_method()
    {
        $enumReflection = new \ReflectionClass(ScalarEnumInterface::class);
        $isMethod = $enumReflection->getMethod('is');
        $parameters = $isMethod->getParameters();
        self::assertCount(1, $parameters);
        /** @var \ReflectionParameter $enumAsParameter */
        $enumAsParameter = current($parameters);
        self::assertFalse($enumAsParameter->isOptional());
    }
}