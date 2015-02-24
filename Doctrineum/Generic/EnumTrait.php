<?php
namespace Doctrineum\Generic;

trait EnumTrait
{

    /**
     * @var Enum[]
     */
    private static $builtEnums = [];

    /**
     * @var string|int|float|bool|null
     */
    protected $enumValue;

    /**
     * @return string (null is casted into empty string!)
     * @see getEnumValue()
     */
    public function __toString()
    {
        return (string)$this->enumValue;
    }

    /**
     * @throws Exceptions\CanNotBeCloned
     */
    public function __clone()
    {
        throw new Exceptions\CanNotBeCloned('Enum as a singleton can not be cloned. Use same instance everywhere.');
    }

    /**
     * @return string|int|float|bool|null
     */
    public function getEnumValue()
    {
        return $this->enumValue;
    }

    /**
     * @return Enum[]
     */
    protected static function getBuiltEnums()
    {
        return self::$builtEnums;
    }

    /**
     * @param string|float|int|bool|null $enumValue
     * @param string $namespace
     * @return Enum
     */
    public static function getEnum($enumValue, $namespace = __CLASS__)
    {
        $checkedValue = static::convertToScalarOrNull($enumValue);

        if (!isset(self::$builtEnums[$namespace])) {
            self::$builtEnums[$namespace] = [];
        }

        $enumKey = serialize($checkedValue);
        if (!isset(self::$builtEnums[$namespace][$enumKey])) {
            self::$builtEnums[$namespace][$enumKey] = static::createByValue($checkedValue);
        }

        return self::$builtEnums[$namespace][$enumKey];
    }

    /**
     * @param mixed $enumValue
     * @return string|float|int|null
     */
    protected static function convertToScalarOrNull($enumValue)
    {
        if (is_scalar($enumValue) || is_null($enumValue)) {
            return $enumValue;
        } elseif (is_object($enumValue) && method_exists($enumValue, '__toString')) {
            return $enumValue->__toString();
        } else {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null or to string object, got ' . gettype($enumValue));
        }
    }

    /**
     * @param string|int|float|bool|null $enumValue
     * @return Enum
     */
    protected static function createByValue($enumValue)
    {
        return new static($enumValue);
    }

}
