<?php
namespace Doctrineum;
use Granam\StrictObject\StrictObject;

/**
 * Inspired by @link http://github.com/marc-mabe/php-enum
 */
class Enum extends StrictObject
{
    /**
     * __CLASS__ magic constant remains untouched at child classes, it's still this, parent class name
     * see @link http://php.net/manual/en/language.constants.predefined.php
     */
    const INNER_NAMESPACE = __CLASS__;

    /**
     * @var Enum[]
     */
    private static $builtEnums = [];

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value The value to select
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     * @see getValue()
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @throws Exceptions\Logic
     */
    public function __clone()
    {
        throw new Exceptions\Logic('Enum as a singleton can not be cloned. Use same instance everywhere.');
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Enum[]
     */
    protected static function getBuiltEnums()
    {
        return self::$builtEnums;
    }

    /**
     * @param string $value
     * @param string $namespace
     * @return Enum
     */
    public static function get($value, $namespace = self::INNER_NAMESPACE)
    {
        if (!isset(self::$builtEnums[$namespace])) {
            self::$builtEnums[$namespace] = [];
        }

        if (!isset(self::$builtEnums[$namespace][$value])) {
            self::$builtEnums[$namespace][$value] = static::createByValue($value, $namespace);
        }

        return self::$builtEnums[$namespace][$value];
    }

    /**
     * @param string $value
     * @param string $namespaceToCheck
     * @return Enum
     */
    protected static function createByValue($value, $namespaceToCheck)
    {
        static::checkNamespace($namespaceToCheck);

        return static::create($value);
    }

    /**
     * @param $namespace
     * @throws Exceptions\UnexpectedInnerNamespace
     */
    protected static function checkNamespace($namespace)
    {
        if ($namespace !== static::INNER_NAMESPACE) {
            throw new Exceptions\UnexpectedInnerNamespace(
                'Expecting ' . static::INNER_NAMESPACE . ' inner namespace, got ' . var_export($namespace, true)
            );
        }
    }

    /**
     * @param $value
     * @return Enum
     */
    protected static function create($value)
    {
        return new static($value);
    }

}
