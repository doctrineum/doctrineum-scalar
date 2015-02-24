<?php
namespace Doctrineum\Generic;

use Granam\Strict\Object\StrictObject;

/**
 * Inspired by @link http://github.com/marc-mabe/php-enum
 */
class Enum extends StrictObject implements EnumInterface
{
    use EnumTrait;

    /**
     * @param mixed $enumValue
     */
    public function __construct($enumValue)
    {
        $this->enumValue = static::convertToScalarOrNull($enumValue);
    }
}
