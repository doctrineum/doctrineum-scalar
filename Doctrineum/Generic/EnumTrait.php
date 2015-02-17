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
     * @see getValue()
     */
    public function __toString()
    {
        return (string)$this->getValue();
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
    public function getValue()
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
    public static function get($enumValue, $namespace = __CLASS__)
    {
        static::checkIfScalarOrNull($enumValue);

        if (!isset(self::$builtEnums[$namespace])) {
            self::$builtEnums[$namespace] = [];
        }

        $enumKey = serialize($enumValue);
        if (!isset(self::$builtEnums[$namespace][$enumKey])) {
            self::$builtEnums[$namespace][$enumKey] = static::createByValue($enumValue);
        }

        return self::$builtEnums[$namespace][$enumKey];
    }

    /**
     * @param string|float|int|null $enumValue
     */
    protected static function checkIfScalarOrNull($enumValue)
    {
        if (!is_scalar($enumValue) && !is_null($enumValue)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null, got ' . gettype($enumValue));
        }
    }

    /**
     * @param string|int|float|bool|null $enumValue
     * @return Enum
     */
    protected static function createByValue($enumValue)
    {
        return static::create($enumValue);
    }

    /**
     * @param string|int|float|bool|null $enumValue
     * @return Enum
     */
    protected static function create($enumValue)
    {
        return new static($enumValue);
    }

}
