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

class ScalarEnumTypeTest extends AbstractTypeTest
{
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
        self::assertTrue($typeClass::isRegistered());
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
     * @return AbstractPlatform
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
    public function empty_string_to_php_value_is_enum_with_that_empty_string(ScalarEnumType $enumType)
    {

        $platform = $this->getPlatform();
        $enum = $enumType->convertToPHPValue($emptyString = '', $platform);
        self::assertInstanceOf($this->getRegisteredClass(), $enum);
        self::assertSame($emptyString, $enum->getValue());
    }

    // CONVERSION_TO-PHP TESTS

    /**
     * The Enum class does NOT cast non-string scalars into string (integers, floats etc).
     * (But saving the value into database and pulling it back probably will.)
     *
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_get_instance
     */
    public function integer_to_php_value_is_enum_with_that_integer(ScalarEnumType $enumType)
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
    public function zero_integer_to_php_value_is_enum_with_that_zero_integer(ScalarEnumType $enumType)
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
    public function float_to_php_value_is_enum_with_that_float(ScalarEnumType $enumType)
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
    public function zero_float_to_php_value_is_enum_with_that_zero_float(ScalarEnumType $enumType)
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
    public function false_to_php_value_is_enum_with_that_false(ScalarEnumType $enumType)
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
    public function true_to_php_value_is_enum_with_that_true(ScalarEnumType $enumType)
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
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_register_subtype
     */
    public function I_can_remove_subtype(ScalarEnumType $enumType)
    {
        /**
         * The subtype is unregistered because of tearDown clean up
         * @see EnumTypeTestTrait::tearDown
         */
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
    }

    /**
     * SUBTYPE TESTS
     */

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
         * @see EnumTypeTestTrait::tearDown
         */
        self::assertFalse($enumType::hasSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), '~foo~'));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass()));
        self::assertTrue($enumType::removeSubTypeEnum($this->getSubTypeEnumClass())); // twice the same
    }

    /**
     * @param ScalarEnumType $subType
     *
     * @test
     * @depends I_can_register_subtype
     */
    public function subtype_returns_proper_enum(ScalarEnumType $subType)
    {
        self::assertTrue($subType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = $this->getPlatform();
        $matchingValueToConvert = 'A string with some specific string inside.';
        self::assertRegExp($regexp, $matchingValueToConvert);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enumFromSubType = $subType->convertToPHPValue($matchingValueToConvert, $abstractPlatform);
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumFromSubType);
        self::assertSame($matchingValueToConvert, (string)$enumFromSubType);
    }

    /**
     * @param ScalarEnumType $enumType
     *
     * @test
     * @depends I_can_register_subtype
     */
    public function default_enum_is_given_if_subtype_does_not_match(ScalarEnumType $enumType)
    {
        self::assertTrue($enumType::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~some specific string~'));
        /** @var AbstractPlatform $abstractPlatform */
        $abstractPlatform = $this->getPlatform();
        $nonMatchingValueToConvert = 'A string without that specific string.';
        self::assertNotRegExp($regexp, $nonMatchingValueToConvert);
        /**
         * Used TestSubTypeEnum returns as an "enum" the given value, which is $valueToConvert in this case,
         * @see \Doctrineum\Tests\Scalar\TestSubTypeEnum::getEnum
         */
        $enum = $enumType->convertToPHPValue($nonMatchingValueToConvert, $abstractPlatform);
        self::assertNotSame($nonMatchingValueToConvert, $enum);
        self::assertInstanceOf(ScalarEnumInterface::class, $enum);
        self::assertSame($nonMatchingValueToConvert, (string)$enum);
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
    public function I_am_stopped_on_registering_of_non_existing_type(ScalarEnumType $enumType)
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
    public function registering_invalid_subtype_class_throws_exception(ScalarEnumType $enumType)
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
    public function registering_subtype_with_invalid_regexp_throws_exception(ScalarEnumType $enumType)
    {
        $enumType::addSubTypeEnum(
            $this->getSubTypeEnumClass(),
            'foo~' // missing opening delimiter
        );
    }

    /**
     * @test
     *
     * @depends I_can_register_it
     */
    public function I_can_register_another_enum_type()
    {
        $anotherEnumType = $this->getAnotherEnumTypeClass();
        if (!$anotherEnumType::isRegistered()) {
            self::assertTrue($anotherEnumType::registerSelf());
        } else {
            self::assertFalse($anotherEnumType::registerSelf());
        }

        self::assertTrue($anotherEnumType::isRegistered());
        self::assertTrue(Type::hasType(TestAnotherScalarEnumType::DIFFERENT_NAME));
    }

    /**
     * @return string|TestAnotherScalarEnumType
     */
    protected function getAnotherEnumTypeClass()
    {
        return TestAnotherScalarEnumType::getClass();
    }

    /**
     * @test
     *
     * @depends I_can_register_another_enum_type
     */
    public function different_types_with_same_subtype_regexp_distinguish_them()
    {
        /** @var ScalarEnumType $enumTypeClass */
        $enumTypeClass = $this->getTypeClass();
        if ($enumTypeClass::hasSubTypeEnum($this->getSubTypeEnumClass())) {
            $enumTypeClass::removeSubTypeEnum($this->getSubTypeEnumClass());
        }
        $enumTypeClass::addSubTypeEnum($this->getSubTypeEnumClass(), $regexp = '~searching pattern~');

        $anotherEnumTypeClass = $this->getAnotherEnumTypeClass();
        if ($anotherEnumTypeClass::hasSubTypeEnum($this->getAnotherSubTypeEnumClass())) {
            $anotherEnumTypeClass::removeSubTypeEnum($this->getAnotherSubTypeEnumClass());
        }
        // regexp is same, sub-type is not
        $anotherEnumTypeClass::addSubTypeEnum($this->getAnotherSubTypeEnumClass(), $regexp);

        $value = 'some string fitting to searching pattern';
        self::assertRegExp($regexp, $value);

        $enumType = Type::getType($this->getExpectedTypeName());
        $enumSubType = $enumType->convertToPHPValue($value, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        self::assertSame($value, "$enumSubType");

        $anotherEnumType = Type::getType(TestAnotherScalarEnumType::DIFFERENT_NAME);
        $anotherEnumSubType = $anotherEnumType->convertToPHPValue($value, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumSubType);
        self::assertSame($value, "$anotherEnumSubType");

        // registered sub-types were different, just regexp was the same - let's test if they are kept separately
        self::assertNotSame($enumSubType, $anotherEnumSubType);
    }

    /**
     * @return string|TestAnotherSubTypeScalarEnum
     */
    protected function getAnotherSubTypeEnumClass()
    {
        return TestAnotherSubTypeScalarEnum::getClass();
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
        $enumBySubType = ScalarEnumType::getType(ScalarEnumType::SCALAR_ENUM)->convertToPHPValue($enumValue, $this->getPlatform());
        self::assertInstanceOf($this->getSubTypeEnumClass(), $enumBySubType);
    }

    /**
     * @test
     */
    public function I_can_use_enum_type_from_sub_namespace()
    {
        EnumWithSubNamespaceType::registerSelf();
        $enum = EnumWithSubNamespaceType::getType(EnumWithSubNamespaceType::WITH_SUB_NAMESPACE)
            ->convertToPHPValue('foo', $this->getPlatform());
        self::assertInstanceOf(EnumWithSubNamespace::getClass(), $enum);
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
     * @depends I_can_register_it
     * @expectedException \Doctrineum\Scalar\Exceptions\TypeNameOccupied
     */
    public function I_can_not_silently_rewrite_type_by_same_name()
    {
        IAmUsingOccupiedName::registerSelf();
    }

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
     * @return string|TestSubTypeScalarEnum
     */
    protected function getSubTypeEnumClass()
    {
        return TestSubTypeScalarEnum::getClass();
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
