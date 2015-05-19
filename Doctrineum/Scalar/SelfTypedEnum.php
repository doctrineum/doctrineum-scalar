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

    /**
     * @param string $subTypeClassName
     * @param string $subTypeValueRegexp
     * @return bool
     */
    public static function addSubTypeEnum($subTypeClassName, $subTypeValueRegexp)
    {
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
        if (!is_a($subTypeClassName, __CLASS__, true /* allow tested class to be just its name and can be searched by auto-loader */)) {
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
                'Under type of name ' . var_export(static::getTypeName(), true) .
                ' is already registered different class ' . get_class($alreadyRegisteredType)
            );
        }
    }

    /**
     * Type has private constructor, the only way how to create an Enum, which is also Type, is by Type factory method,
     * @see Type::getType
     *
     * @param string|int|float|bool|null $finalEumValue
     * @return SelfTypedEnum
     */
    protected static function createByValue($finalEumValue)
    {
        if (!is_scalar($finalEumValue) && !is_null($finalEumValue)) {
            throw new Exceptions\UnexpectedValueToEnum('Expected scalar or null, got ' . gettype($finalEumValue));
        }

        /** @var SelfTypedEnum $enumClass */
        // determining of enum class by getEnumClass is important for subtypes
        $enumClass = static::getEnumClass($finalEumValue);
        // Type has private constructor, the only way how to create an Enum, which is also Type, is by Type factory method getType
        $selfTypedEnum = $enumClass::getType($enumClass::getTypeName());
        if ($selfTypedEnum->enumValue === $finalEumValue) {
            return $selfTypedEnum;
        }

        $selfTypedEnum->allowSingleClone();
        $newSelfTypedEnum = clone $selfTypedEnum;
        $selfTypedEnum->prohibitSingleClone();
        $newSelfTypedEnum->enumValue = $finalEumValue;

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
        // Doctrineum\Scalar\SelfTypedEnum1a2b3Foo = SelfTypedEnum1a2b3Foo
        $baseClassName = preg_replace('~(\w+\\\)*(\w+)~', '$2', static::class);
        // SelfTypedEnum123Foo = Self_Typed_Enum1a2b3_Foo
        $underScoredClassName = preg_replace('~(\w)([A-Z])~', '$1_$2', $baseClassName);
        // SelfTypedEnum123Foo = Self_Typed_Enum_1a2b3_Foo
        $underScoredDigitsClassName = preg_replace('~(\w)(\d[^A-Z]*)~', '$1_$2', $underScoredClassName);

        // Self_Typed_Enum_1a2b3_Foo = self_typed_enum_1a2b3_foo
        return strtolower($underScoredDigitsClassName);
    }
}
