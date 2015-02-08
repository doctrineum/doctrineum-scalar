<?php
namespace Doctrineum;

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
     * @param string|float|int|bool|null $value
     * @param string $namespace
     * @return Enum
     */
    public static function get($value, $namespace = __CLASS__)
    {
        static::checkIfScalarOrNull($value);

        if (!isset(self::$builtEnums[$namespace])) {
            self::$builtEnums[$namespace] = [];
        }

        $valueKey = serialize($value);
        if (!isset(self::$builtEnums[$namespace][$valueKey])) {
            self::$builtEnums[$namespace][$valueKey] = static::createByValue($value);
        }

        return self::$builtEnums[$namespace][$valueKey];
    }

    /**
     * @param string|float|int|null $value
     */
    protected static function checkIfScalarOrNull($value)
    {
        if (!is_scalar($value) && !is_null($value)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null, got ' . gettype($value));
        }
    }

    /**
     * @param string|int|float|bool|null $value
     * @return Enum
     */
    protected static function createByValue($value)
    {
        return static::create($value);
    }

    /**
     * @param string|int|float|bool|null $value
     * @return Enum
     */
    protected static function create($value)
    {
        return new static($value);
    }

}
