<?php
namespace Doctrineum\Generic;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @method static SelfTypedEnum getType(string $name),
 * @see EnumType::getType
 * @method SelfTypedEnum convertToPHPValue(string $value, AbstractPlatform $platform)
 * @see EnumType::convertToPHPValue
 */
class SelfTypedEnum extends EnumType implements EnumInterface
{
    /**
     * The enum __toString overwrites type __toString method
     * @see \Doctrineum\Generic\EnumTrait::__toString for current
     * and
     * @see \Doctrine\DBAL\Types\Type::__toString for overwritten
     */
    use EnumTrait;

    const SELF_TYPED_ENUM = 'self_typed_enum';

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerSelf()
    {
        static::addType(static::getTypeName(), static::class);
    }

    /**
     * Type has private constructor, the only way how to create an Enum, which is also Type, is by Type factory method,
     * @see Type::getType
     *
     * @param string|int|float|bool|null $enumValue
     * @return SelfTypedEnum
     */
    protected static function createByValue($enumValue)
    {
        if (!is_scalar($enumValue) && !is_null($enumValue)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null, got ' . gettype($enumValue));
        }

        $selfTypedEnum = static::getType(static::getTypeName());
        $selfTypedEnum->enumValue = $enumValue;

        return $selfTypedEnum;
    }

    /**
     * Core idea of self-typed enum.
     * As an enum class returns itself.
     *
     * @return string
     */
    protected static function getEnumClass()
    {
        return static::class;
    }

    /**
     * The name is auto-generated from this class base name
     * Gets the strongly recommended name of this type.
     * Its used at @see \Doctrine\DBAL\Platforms\AbstractPlatform::getDoctrineTypeComment
     *
     * @return string
     */
    public static function getTypeName()
    {
        // Doctrineum\Generic\SelfTypedEnum = SelfTypedEnum
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', static::class);
        // SelfTypedEnum = Self_Typed_Enum = self_typed_enum
        return strtolower(preg_replace('~(\w)([A-Z])~', '$1_$2', $baseClassName));
    }
}
