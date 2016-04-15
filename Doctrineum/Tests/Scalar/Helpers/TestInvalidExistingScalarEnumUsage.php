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

    protected static function getEnumFromNamespace($enumValue, $namespace)
    {
        $finalValue = static::convertToEnumFinalValue($enumValue);
        if (self::$forceAdding) {
            static::addBuiltEnum(static::createByValue($finalValue), $namespace);
        }

        if (self::$forceGetting) {
            return static::getBuiltEnum($finalValue, $namespace);
        }

        return null;
    }

}
