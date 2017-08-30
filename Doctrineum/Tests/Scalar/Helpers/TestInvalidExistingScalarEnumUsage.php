<?php
namespace Doctrineum\Tests\Scalar\Helpers;

use Doctrineum\Scalar\ScalarEnum;

class TestInvalidExistingScalarEnumUsage extends ScalarEnum
{
    private static $forceAdding = false;
    private static $forceGetting = false;

    public static function forceAdding($force = true)
    {
        self::$forceAdding = $force;
    }

    public static function forceGetting($force = true)
    {
        self::$forceGetting = $force;
    }

    /**
     * @param float|int|string $enumValue
     * @param string $namespace
     * @return \Doctrineum\Scalar\ScalarEnumInterface|null
     */
    protected static function getEnumFromNamespace($enumValue, string $namespace)
    {
        $finalValue = static::convertToEnumFinalValue($enumValue);
        if (self::$forceAdding) {
            static::addCreatedEnum(static::createEnum($finalValue), $namespace);
        }

        if (self::$forceGetting) {
            return static::getCreatedEnum($finalValue, $namespace);
        }

        return null;
    }

}