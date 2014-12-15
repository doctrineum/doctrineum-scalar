<?php
namespace Doctrineum;

/**
 * Inspired by @link http://github.com/marc-mabe/php-enum
 */
class Enum
{
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
     * @return Enum
     */
    public static function get($value)
    {
        if (!isset(self::$builtEnums[$value])) {
            self::$builtEnums[$value] = self::createByValue($value);
        }

        return self::$builtEnums[$value];
    }

    /**
     * @param $value
     * @return static
     */
    protected static function createByValue($value){
        return new static($value);
    }

}
