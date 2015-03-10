<?php
namespace Doctrineum\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @method static SelfTypedEnum getType(string $name),
 * @see EnumType::getType
 *
 * @method SelfTypedEnum convertToPHPValue(string $value, AbstractPlatform $platform)
 * @see EnumType::convertToPHPValue
 *
 * @method static SelfTypedEnum getEnum(mixed $value)
 * @see EnumTrait::getEnum
 */
class SelfTypedEnum extends EnumType implements EnumInterface
{
    /**
     * The enum __toString overwrites type __toString method
     * @see \Doctrineum\Scalar\EnumTrait::__toString for current
     * and
     * @see \Doctrine\DBAL\Types\Type::__toString for overwritten
     */
    use EnumTrait;

    const SELF_TYPED_ENUM = 'self_typed_enum';

    /**
     * @return bool If enum has not been registered before and was registered now
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerSelf()
    {
        if (static::hasType(static::getTypeName())) {
            static::checkRegisteredType();

            return false;
        }

        static::addType(static::getTypeName(), static::class);

        return true;
    }

    protected static function checkRegisteredType()
    {
        $alreadyRegisteredType = static::getType(static::getTypeName());
        if (get_class($alreadyRegisteredType) !== static::class) {
            throw new Exceptions\TypeNameOccupied(
                'Under type name ' . static::getTypeName() .
                ' is already registered different class ' . get_class($alreadyRegisteredType)
            );
        }
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
        if ($selfTypedEnum->enumValue === $enumValue) {
            return $selfTypedEnum;
        }

        $selfTypedEnum->allowSingleClone();
        $newSelfTypedEnum = clone $selfTypedEnum;
        $newSelfTypedEnum->enumValue = $enumValue;

        return $newSelfTypedEnum;
    }

    /**
     * Core idea of self-typed enum.
     * As an enum class returns itself.
     *
     * @return string
     */
    protected function getDefaultEnumClass()
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
        // Doctrineum\Scalar\SelfTypedEnum = SelfTypedEnum
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', static::class);
        // SelfTypedEnum = Self_Typed_Enum
        $underScoredClassName = preg_replace('~(\w)([A-Z])~', '$1_$2', $baseClassName);

        // Self_Typed_Enum = self_typed_enum
        return strtolower($underScoredClassName);
    }
}
