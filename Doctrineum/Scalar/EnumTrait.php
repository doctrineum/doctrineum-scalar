<?php
namespace Doctrineum\Scalar;

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
     * @var bool
     */
    private $allowSingleClone = false;

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
        if (!$this->allowSingleClone) {
            throw new Exceptions\CanNotBeCloned('Enum as a singleton can not be cloned. Use same instance everywhere.');
        }
        // disabling cloning on clone itself
        $this->allowSingleClone = false;
    }

    protected function allowSingleClone()
    {
        $this->allowSingleClone = true;
    }

    protected function prohibitSingleClone()
    {
        $this->allowSingleClone = false;
    }

    /**
     * @return string|int|float|bool|null
     */
    public function getEnumValue()
    {
        return $this->enumValue;
    }

    /**
     * @param string|float|int|bool|null $enumValue
     * @return Enum
     */
    public static function getEnum($enumValue)
    {
        return static::getEnumFromNamespace($enumValue, static::getInnerNamespace());
    }

    /**
     * @return string
     */
    protected static function getInnerNamespace()
    {
        return static::class;
    }

    protected static function getEnumFromNamespace($enumValue, $namespace)
    {
        $finalValue = static::convertToEnumFinalValue($enumValue);
        if (!static::hasBuiltEnum($finalValue, $namespace)) {
            static::addBuiltEnum(static::createByValue($finalValue), $namespace);
        }

        return static::getBuiltEnum($finalValue, $namespace);
    }

    /**
     * @param mixed $enumValue
     * @return string|float|int|null
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        return static::convertToScalarOrNull($enumValue);
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
            throw new Exceptions\UnexpectedValueToEnum(
                'Expected scalar or null or to string object, got '
                . gettype($enumValue)
            );
        }
    }

    /**
     * @param string|int|float|bool|null $enumValue
     * @return Enum
     */
    protected static function createByValue($enumValue)
    {
        if (!is_scalar($enumValue) && !is_null($enumValue)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null, got ' . gettype($enumValue));
        }

        return new static($enumValue);
    }

    protected static function hasBuiltEnum($enumValue, $namespace)
    {
        return isset(self::$builtEnums[self::createKey($namespace)][self::createKey($enumValue)]);
    }

    /**
     * @param EnumInterface $enum
     * @param mixed $namespace
     * @throws Exceptions\EnumIsAlreadyBuilt
     */
    protected static function addBuiltEnum(EnumInterface $enum, $namespace)
    {
        $namespaceKey = self::createKey($namespace);
        $enumKey = self::createKey($enum->getEnumValue());
        if (isset(self::$builtEnums[$namespaceKey][$enumKey])) {
            throw new Exceptions\EnumIsAlreadyBuilt(
                'Enum of namespace key ' . var_export($namespaceKey, true) . ' and enum key ' . var_export($enumKey, true) .
                ' is already registered with enum of class ' . get_class(static::getBuiltEnum($enum->getEnumValue(), $namespace))
            );
        }

        if (!isset(self::$builtEnums[$namespaceKey])) {
            self::$builtEnums[$namespaceKey] = [];
        }

        self::$builtEnums[$namespaceKey][$enumKey] = $enum;
    }

    /**
     * @param mixed $key
     * @return string
     */
    protected static function createKey($key)
    {
        return serialize($key);
    }

    /**
     * @param mixed $enumValue
     * @param mixed $namespace
     * @return EnumInterface
     */
    protected static function getBuiltEnum($enumValue, $namespace)
    {
        $namespaceKey = self::createKey($namespace);
        $enumKey = self::createKey($enumValue);
        if (!isset(self::$builtEnums[$namespaceKey][$enumKey])) {
            throw new Exceptions\EnumIsNotBuilt(
                'Enum of namespace key ' . var_export($namespaceKey, true) . ' and enum key ' . var_export($enumKey, true) . ' is not registered'
            );
        }

        return self::$builtEnums[self::createKey($namespace)][self::createKey($enumValue)];
    }

}
