<?php
namespace Doctrineum\Scalar;

use Granam\Scalar\Tools\ToScalar;
use Granam\Strict\Object\StrictObject;

/**
 * Inspired by @link http://github.com/marc-mabe/php-enum
 */
class ScalarEnum extends StrictObject implements Enum
{
    const SCALAR_ENUM = 'scalar_enum';

    /**
     * @var ScalarEnum[]
     */
    private static $builtEnums = [];

    /**
     * @var string|int|float|bool
     */
    protected $enumValue;

    /**
     * @param bool|float|int|string|object $enumValue
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function __construct($enumValue)
    {
        $this->enumValue = static::convertToEnumFinalValue($enumValue);
    }

    /**
     * @param bool|float|int|string|object $enumValue
     * @return string|float|int
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        try {
            return ToScalar::toScalar($enumValue, true /* strict */);
        } catch (\Granam\Scalar\Tools\Exceptions\WrongParameterType $exception) {
            throw new Exceptions\UnexpectedValueToEnum($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param bool|float|int|string|object $enumValue
     *
     * @return ScalarEnum
     */
    public static function getEnum($enumValue)
    {
        return static::getEnumFromNamespace($enumValue, static::getInnerNamespace());
    }

    protected static function getEnumFromNamespace($enumValue, $namespace)
    {
        $finalEnumValue = static::convertToEnumFinalValue($enumValue);
        if (!static::hasBuiltEnum($finalEnumValue, $namespace)) {
            static::addBuiltEnum(static::createByValue($finalEnumValue), $namespace);
        }

        return static::getBuiltEnum($finalEnumValue, $namespace);
    }

    protected static function hasBuiltEnum($enumValue, $namespace)
    {
        return isset(self::$builtEnums[self::createKey($namespace)][self::createKey($enumValue)]);
    }

    /**
     * @param mixed $key
     *
     * @return string
     */
    protected static function createKey($key)
    {
        return serialize($key);
    }

    /**
     * @param Enum $enum
     * @param mixed $namespace
     *
     * @throws Exceptions\EnumIsAlreadyBuilt
     */
    protected static function addBuiltEnum(Enum $enum, $namespace)
    {
        $namespaceKey = self::createKey($namespace);
        $enumKey = self::createKey($enum->getValue());
        if (isset(self::$builtEnums[$namespaceKey][$enumKey])) {
            throw new Exceptions\EnumIsAlreadyBuilt(
                'Enum of namespace key ' . var_export($namespaceKey, true) . ' and enum key ' . var_export($enumKey, true) .
                ' is already registered with enum of class ' . get_class(static::getBuiltEnum($enum->getValue(), $namespace))
            );
        }

        if (!isset(self::$builtEnums[$namespaceKey])) {
            self::$builtEnums[$namespaceKey] = [];
        }

        self::$builtEnums[$namespaceKey][$enumKey] = $enum;
    }

    /**
     * @param mixed $enumValue
     * @param mixed $namespace
     * @return Enum
     * @throws \Doctrineum\Scalar\Exceptions\EnumIsNotBuilt
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

    /**
     * @param string|int|float|bool $finalEnumValue
     * @return ScalarEnum
     * @throws \Doctrineum\Scalar\Exceptions\CanNotCreateInstanceOfAbstractEnum
     * @throws \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    protected static function createByValue($finalEnumValue)
    {
        if (!is_scalar($finalEnumValue)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar, got ' . gettype($finalEnumValue));
        }
        $reflection = new \ReflectionClass(static::getClass());
        if ($reflection->isAbstract()) {
            throw new Exceptions\CanNotCreateInstanceOfAbstractEnum(
                'Can not create instance of enum ' . self::getClass() . '. Have you forget to register a descendant or sub-type?'
            );
        }

        return new static($finalEnumValue);
    }

    /**
     * @return string
     */
    protected static function getInnerNamespace()
    {
        return get_called_class();
    }

    /**
     * @return string
     * @see getValue()
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * @return string|int|float|bool
     */
    public function getValue()
    {
        return $this->enumValue;
    }

    /**
     * Doctrineum enums are intentionally not final, but should not be compared by just a value.
     * Use $enum1 === $enum2 or $enum1->is($enum2) for equality of different instances.
     * Think twice before suppressing $sameClassOnly condition, because ArticleTypeEnum->getValue == RoleEnum->getValue is true.
     * @param Enum $enum
     * @param bool $sameClassOnly = false
     * @return bool
     */
    public function is(Enum $enum, $sameClassOnly = true)
    {
        return
            $this->getValue() === $enum->getValue()
            && (!$sameClassOnly || static::class === get_class($enum));
    }

    /**
     * @throws Exceptions\CanNotBeCloned
     */
    public function __clone()
    {
        throw new Exceptions\CanNotBeCloned('Enum as a singleton can not be cloned. Use same instance everywhere.');
    }

}
