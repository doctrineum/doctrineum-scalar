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
    private static $enums = [];

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
     * @return string
     */
    public function getName()
    {
        // find the string key of current enum instance
        return array_search($this, self::$enums, true /* strict */);
    }

    /**
     * @return Enum[]
     */
    public function getRegisteredEnums()
    {
        return self::$enums;
    }

    /**
     * @param string $name
     * @return Enum
     */
    public static function get($name)
    {
        if (!isset(self::$enums[$name])) {
            self::$enums[$name] = self::createByName($name);
        }

        return self::$enums[$name];
    }

    /**
     * @param $name
     * @return static
     */
    protected static function createByName($name){
        return new static($name);
    }

}
