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
 *
 * @method static SelfTypedEnum getIt
 * @see EnumType::getIt
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

    /**
     * @param string $subTypeClassName
     * @param string $subTypeValueRegexp
     * @return bool
     */
    public static function addSubTypeEnum($subTypeClassName, $subTypeValueRegexp) {
        /**
         * The class has to be self-registering to by-pass enum and enum type bindings,
         * @see SelfTypedEnum::createByValue
         */
        static::checkIfSelfRegistering($subTypeClassName);
        $result = parent::addSubTypeEnum($subTypeClassName, $subTypeValueRegexp);
        /** @var SelfTypedEnum $subTypeClassName */
        $subTypeClassName::registerSelf();

        return $result;
    }

    /**
     * @param string $subTypeClassName
     */
    protected static function checkIfSelfRegistering($subTypeClassName)
    {
        if (!is_a($subTypeClassName, __CLASS__, true /* allow tested class as a string */)) {
            throw new Exceptions\SubTypeEnumHasToBeSelfRegistering(
                'Sub-type class ' . var_export($subTypeClassName, true) . ' has to be child of self-typed ' . __CLASS__
            );
        }
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

        /** @var SelfTypedEnum $enumClass */
        // determining of enum class by getEnumClass is important for subtypes
        $enumClass = static::getEnumClass($enumValue);
        // Type has private constructor, the only way how to create an Enum, which is also Type, is by Type factory method getType
        $selfTypedEnum = $enumClass::getType($enumClass::getTypeName());
        if ($selfTypedEnum->enumValue === $enumValue) {
            return $selfTypedEnum;
        }

        $selfTypedEnum->allowSingleClone();
        $newSelfTypedEnum = clone $selfTypedEnum;
        $selfTypedEnum->prohibitSingleClone();
        $newSelfTypedEnum->enumValue = $enumValue;

        return $newSelfTypedEnum;
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
