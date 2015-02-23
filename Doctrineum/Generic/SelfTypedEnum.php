<?php
namespace Doctrineum\Generic;

use Granam\Strict\Object\StrictObjectTrait;

class SelfTypedEnum extends EnumType implements EnumInterface
{
    use StrictObjectTrait;
    use EnumTrait;

    /**
     * @param int|float|string|bool|null $enumValue
     */
    public function __construct($enumValue)
    {
        $this->enumValue = $enumValue;
    }

    /**
     * @return string
     */
    protected static function getEnumClass()
    {
        return static::class;
    }
}
