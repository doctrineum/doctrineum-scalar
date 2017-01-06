<?php
namespace Doctrineum\Tests\Scalar;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Scalar\ScalarEnum;
use Doctrineum\Scalar\ScalarEnumInterface;
use Doctrineum\Scalar\ScalarEnumType;
use Doctrineum\Tests\Scalar\Helpers\EnumTypes\EnumWithSubNamespaceType;
use Doctrineum\Tests\Scalar\Helpers\EnumTypes\IShouldHaveTypeKeywordOnEnd;
use Doctrineum\Tests\Scalar\Helpers\EnumTypes\WithoutEnumIsThisType;
use Doctrineum\Tests\Scalar\Helpers\EnumWithSubNamespace;
use Doctrineum\Tests\Scalar\Helpers\WithToStringTestObject;
use Doctrineum\Tests\SelfRegisteringType\AbstractSelfRegisteringTypeTest;

class ScalarEnumTypeTest extends AbstractSelfRegisteringTypeTest
{

    /**
     * This is called after every test
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (Type::hasType($this->getExpectedTypeName())) {
            $enumType = Type::getType($this->getExpectedTypeName());
            /** @var ScalarEnumType $enumType */
            if ($enumType::hasSubTypeEnum($this->getSubTypeEnumClass())) {
                self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
            }
        }
    }

    /**
     * @test
     */
    public function I_can_register_it()
    {
        parent::I_can_register_it(); // just wraps parent to provide "proper" order of tests execution
    }

    /**
     * @test
     * @depends I_can_register_it
     */
    public function I_can_get_instance()
    {
        return parent::I_can_get_instance(); // just wraps parent to provide "proper" order of tests execution
    }

    /**
     * @test
     * @depends I_can_register_it
     */
    public function I_get_false_if_self_registering_it_again()
    {
        $typeClass = $this->getTypeClass();
        self::assertTrue(Type::hasType($this->getExpectedTypeName()));
        self::assertFalse($typeClass::registerSelf());
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function sql_declaration_is_valid(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $sql = $enumType->getSQLDeclaration([], $platform);
        self::assertSame('VARCHAR(64)', $sql);
    }

    /**
     * @return AbstractPlatform|\Mockery\MockInterface
     */
    protected function getPlatform()
    {
        return $this->mockery(AbstractPlatform::class);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function null_to_database_value_is_null(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        self::assertNull($enumType->convertToDatabaseValue(null, $platform));
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function enum_as_database_value_is_string_value_of_that_enum(ScalarEnumType $enumType)
    {
        $value = 'foo';
        $platform = $this->getPlatform();
        self::assertSame($value, $enumType->convertToDatabaseValue(ScalarEnum::getEnum($value), $platform));
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function null_from_database_gives_null(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        self::assertNull($enumType->convertToPHPValue(null, $platform));
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function string_to_php_value_is_enum_with_that_string(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($string = 'foo', $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($string, $enum->getValue());
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_get_enum_with_empty_string_on_conversion(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($emptyString, $enum->getValue());
    }

    // CONVERSION-TO-PHP TESTS

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_pure_integer_in_enum(ScalarEnumType $enumType)
    {

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($integer = 12345, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($integer, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_enum_with_pure_integer_zero(ScalarEnumType $enumType)
    {

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($zeroInteger = 0, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($zeroInteger, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_enum_with_pure_float(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($float = 12345.6789, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($float, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_enum_with_pure_float_zero(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($zeroFloat = 0.0, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($zeroFloat, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_enum_with_pure_false(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($false = false, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($false, $enum->getValue());
    }

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_get_enum_with_pure_true(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($true = true, $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($true, $enum->getValue());
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function object_with_to_string_to_php_value_is_enum_with_that_string(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue(new WithToStringTestObject($value = 'foo'), $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($value, $enum->getValue());
        self::assertSame($value, (string)$enum);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function array_to_php_value_cause_exception(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enumType->convertToPHPValue([], $platform);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function resource_to_php_value_cause_exception(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enumType->convertToPHPValue(tmpfile(), $platform);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function object_to_php_value_cause_exception(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enumType->convertToPHPValue(new \stdClass(), $platform);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToEnum
     */
    public function callback_to_php_value_cause_exception(ScalarEnumType $enumType)
    {
        $platform = $this->getPlatform();
        $enumType->convertToPHPValue(
            function () {
            },
            $platform
        );
    }

    /**
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToDatabaseValue
     */
    public function conversion_of_non_object_to_database_cause_exception()
    {
        $enumType = Type::getType($this->getExpectedTypeName());
        $enumType->convertToDatabaseValue('foo', $this->getPlatform());
    }

    /**
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToDatabaseValue
     */
    public function conversion_of_non_enum_to_database_cause_exception()
    {
        $enumType = Type::getType($this->getExpectedTypeName());
        $enumType->convertToDatabaseValue(new \stdClass(), $this->getPlatform());
    }

    /**
     * @test
     * @depends I_can_get_instance
     */
    public function I_get_same_enum_type_name_as_enum_type_instance_name()
    {
        $enumType = Type::getType($this->getExpectedTypeName());
        self::assertSame($this->getExpectedTypeName(), $enumType->getName());
    }

    /**
     * @test
     * @depends I_can_get_instance
     */
    public function It_requires_sql_comment_hint()
    {
        $enumType = Type::getType($this->getExpectedTypeName());
        self::assertTrue($enumType->requiresSQLCommentHint($this->getPlatform()));
    }

    // SUBTYPE TESTS

    /**
     * @param ScalarEnumType $enumType
     *
     * @return ScalarEnumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function I_can_register_subtype(ScalarEnumType $enumType)
    {
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~foo~'));
        self::assertTrue($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));

        self::assertFalse($enumType::registerSubTypeEnum($this->getSubTypeEnumClass(), $regexp));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::registerSubTypeEnum($this->getSubTypeEnumClass(), $regexp));

        return $enumType;
    }

    /**
     * @return string|TestSubTypeScalarEnum
     */
    protected function getSubTypeEnumClass()
    {
        return TestSubTypeScalarEnum::class;
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_register_subtype
     */
    public function I_can_remove_subtype(ScalarEnumType $enumType)
    {
        /**
         * The subtype is unregistered because of tearDown clean up
         *
         * @see EnumTypeTestTrait::tearDown
         */
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_register_subtype
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumIsNotRegistered
     */
    public function I_can_not_remove_not_registered_subtype(ScalarEnumType $enumType)
    {
        /**
         * The subtype is unregistered because of tearDown clean up
         *
         * @see EnumTypeTestTrait::tearDown
         */
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass())); // twice the same
    }

    /**
     *
     * @test
     * @depends I_can_register_subtype
     * @param ScalarEnumType $subType
     */
    public function I_get_registered_subtype_enum_on_match(ScalarEnumType $subType)
    {
        self::assertTrue($subType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = $this->getPlatform();
        $matchingValueToConvert = 'A string with some specific string inside.';
        self::assertRegExp($regexp, $matchingValueToConvert);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         *
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enumFromSubType = $subType->convertToPHPValue($matchingValueToConvert, $abstractPlatform);
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumFromSubType);
        self::assertSame($matchingValueToConvert, (string)$enumFromSubType);
    }

    /**
     * @test
     * @depends I_can_register_subtype
     * @param ScalarEnumType $enumType
     */
    public function I_get_default_enum_class_if_subtype_regexp_does_not_match(ScalarEnumType $enumType)
    {
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        $platform = $this->getPlatform();
        $nonMatchingValue = 'A string without that specific string.';
        self::assertNotRegExp($regexp, $nonMatchingValue);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         *
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enum = $enumType->convertToPHPValue($nonMatchingValue, $platform);
        self::assertInstanceOf(ScalarEnumInterface::class, $enum);
        self::assertNotInstanceOf($this->getSubTypeEnumClass(), $enum);
        self::assertSame($nonMatchingValue, (string)$enum);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumIsAlreadyRegistered
     */
    public function registering_same_subtype_again_throws_exception(ScalarEnumType $enumType)
    {
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        // registering twice - should thrown an exception
        $enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~');
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumIsAlreadyRegistered
     * @expectedExceptionMessageRegExp /~foo~.*~bar~/
     */
    public function I_can_not_register_same_subtype_by_easy_registrar_with_different_regexp(ScalarEnumType $enumType)
    {
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::registerSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        try {
            self::assertFalse($enumType::registerSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        $enumType::registerSubTypeEnum($this->getSubTypeEnumClass(), '~bar~');
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumClassNotFound
     */
    public function I_can_not_register_non_existing_type(ScalarEnumType $enumType)
    {
        $enumType::addSubTypeEnum('whoAmI', '~foo~');
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\SubTypeEnumHasToBeEnum
     */
    public function I_can_not_register_invalid_subtype_class(ScalarEnumType $enumType)
    {
        $enumType::addSubTypeEnum('stdClass', '~foo~');
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     * @expectedException \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     * @expectedExceptionMessage The given regexp is not enclosed by same delimiters and therefore is not valid: 'foo~'
     */
    public function I_can_not_register_subtype_with_invalid_regexp(ScalarEnumType $enumType)
    {
        $enumType::addSubTypeEnum(
            $this->getSubTypeEnumClass(),
            'foo~' // missing opening delimiter
        );
    }

    /**
     * @test
     */
    public function Subtypes_with_same_regexp_but_different_parent_types_lives_separately()
    {
        /** @var ScalarEnumType $enumTypeClass */
        $enumTypeClass = $this->getTypeClass();
        $regexp = '~searching pattern~';
        $matchingValue = 'some string fitting to "searching pattern"';
        self::assertRegExp($regexp, $matchingValue);

        // first sub-type
        if ($enumTypeClass::hasSubTypeEnum($this->getSubTypeEnumClass())) {
            $enumTypeClass::removeSubTypeEnum($this->getSubTypeEnumClass());
        }
        $enumTypeClass::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp);

        // second sub-type
        $anotherEnumTypeClass = $this->getAnotherEnumTypeClass();
        if ($anotherEnumTypeClass::hasSubTypeEnum($this->getAnotherSubTypeEnumClass())) {
            $anotherEnumTypeClass::removeSubTypeEnum($this->getAnotherSubTypeEnumClass());
        }
        // regexp is same but sub-type AND enum class are NOT
        $anotherEnumTypeClass::addSubTypeEnum($this->getAnotherSubTypeEnumClass(), $regexp);

        $enumType = Type::getType($this->getExpectedTypeName());
        $enumSubType = $enumType->convertToPHPValue($matchingValue, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        self::assertSame($matchingValue, (string)$enumSubType);

        TestAnotherScalarEnumType::registerSelf();
        $anotherEnumType = Type::getType(TestAnotherScalarEnumType::DIFFERENT_NAME);
        $anotherEnumSubType = $anotherEnumType->convertToPHPValue($matchingValue, $this->getPlatform());
        self::assertInstanceOf($this->getAnotherSubTypeEnumClass(), $anotherEnumSubType);
        self::assertSame($matchingValue, (string)$anotherEnumSubType);

        // registered sub-types were different, just regexp was the same - let's test if they are kept separately
        self::assertNotSame($enumSubType, $anotherEnumSubType);
    }

    /**
     * @return string|TestAnotherScalarEnumType
     */
    protected function getAnotherEnumTypeClass()
    {
        return TestAnotherScalarEnumType::class;
    }

    /**
     * @return string|TestAnotherSubTypeScalarEnum
     */
    protected function getAnotherSubTypeEnumClass()
    {
        return TestAnotherSubTypeScalarEnum::class;
    }

    /**
     * Warning, this behaviour is undefined.
     *
     * @test
     */
    public function I_can_register_subtypes_with_same_regexp()
    {
        /** @var ScalarEnumType $enumTypeClass */
        $enumTypeClass = $this->getTypeClass();
        $regexp = '~searching pattern~';
        $matchingValue = 'some string fitting to "searching pattern"';
        self::assertRegExp($regexp, $matchingValue);

        // first sub-type
        if ($enumTypeClass::hasSubTypeEnum($this->getSubTypeEnumClass())) {
            $enumTypeClass::removeSubTypeEnum($this->getSubTypeEnumClass());
        }
        $enumTypeClass::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp);

        // second sub-type
        if ($enumTypeClass::hasSubTypeEnum($this->getAnotherSubTypeEnumClass())) {
            $enumTypeClass::removeSubTypeEnum($this->getAnotherSubTypeEnumClass());
        }
        // regexp AND enum class are same but sub-type is NOT
        $enumTypeClass::addSubTypeEnum($this->getAnotherSubTypeEnumClass(), $regexp);

        $enumType = Type::getType($this->getExpectedTypeName());
        $enumSubType = $enumType->convertToPHPValue($matchingValue, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        self::assertSame($matchingValue, (string)$enumSubType);

        $anotherEnumSubType = $enumType->convertToPHPValue($matchingValue, $this->getPlatform());
        self::assertSame($matchingValue, (string)$anotherEnumSubType);
        // despite their DIFFERENT sub-type classes the result is unwillingly the same because of same regexp
        self::assertSame($enumSubType, $anotherEnumSubType);
    }

    /**
     * @test
     * @depends I_can_register_it
     */
    public function repeated_self_registration_returns_false()
    {
        self::assertFalse(ScalarEnumType::registerSelf());
    }

    /**
     * @test
     * @depends I_can_register_it
     */
    public function I_can_use_subtype()
    {
        ScalarEnumType::addSubTypeEnum($this->getSubTypeEnumClass(), $pattern = '~foo~');
        self::assertRegExp($pattern, $enumValue = 'foo bar baz');
        $enumBySubType = ScalarEnumType::getType(ScalarEnumType::SCALAR_ENUM)
            ->convertToPHPValue($enumValue, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumBySubType);
    }

    /**
     * @test
     * @depends I_can_register_it
     * @expectedException \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     * @expectedExceptionMessageRegExp ~null~i
     */
    public function I_can_not_add_subtype_with_invalid_regexp()
    {
        ScalarEnumType::addSubTypeEnum($this->getSubTypeEnumClass(), null);
    }

    /**
     * @test
     */
    public function I_can_use_enum_type_from_sub_namespace()
    {
        EnumWithSubNamespaceType::registerSelf();
        $enum = EnumWithSubNamespaceType::getType(EnumWithSubNamespaceType::WITH_SUB_NAMESPACE)
            ->convertToPHPValue('foo', $this->getPlatform());
        self::assertInstanceOf(EnumWithSubNamespace::class, $enum);
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\EnumClassNotFound
     */
    public function I_am_stopped_by_exception_on_conversion_to_unknown_enum()
    {
        WithoutEnumIsThisType::registerSelf();
        $type = WithoutEnumIsThisType::getType(WithoutEnumIsThisType::WITHOUT_ENUM_IS_THIS_TYPE);
        $type->convertToPHPValue('foo', $this->getPlatform());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\CouldNotDetermineEnumClass
     */
    public function I_can_not_use_type_with_unexpected_name_structure()
    {
        IShouldHaveTypeKeywordOnEnd::registerSelf();
        $type = IShouldHaveTypeKeywordOnEnd::getType(IShouldHaveTypeKeywordOnEnd::I_SHOULD_HAVE_TYPE_KEYWORD_ON_END);
        $type->convertToPHPValue('foo', $this->getPlatform());
    }

    /**
     * @test
     * @depends I_can_get_instance
     * @param ScalarEnumType $enumType
     * @expectedException  \Doctrineum\Scalar\Exceptions\InvalidRegexpFormat
     * @expectedExceptionMessageRegExp ~bar~
     */
    public function I_can_not_ask_for_registered_subtype_by_invalid_regexp(ScalarEnumType $enumType)
    {
        $enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~');
        $enumType::hasSubTypeEnum($this->getSubTypeEnumClass(), '~bar'); // intentionally missing trailing tilde
    }
}

/** inner */
class TestSubTypeScalarEnum extends ScalarEnum
{

}

class TestAnotherSubTypeScalarEnum extends ScalarEnum
{

}

class TestAnotherScalarEnumType extends ScalarEnumType
{
    const DIFFERENT_NAME = 'different_name';

    public function getName()
    {
        return self::DIFFERENT_NAME;
    }
}

class IAmUsingOccupiedName extends ScalarEnumType
{
    // without overwriting parent name
}