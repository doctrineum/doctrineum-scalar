<?php
namespace Doctrineum\Scalar\Exceptions;

class SubTypeEnumHasToBeSelfRegisteringTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\InvalidClassForSubTypeEnum
     */
    public function is_invalid_class_for_sub_type_enum_test_exception()
    {
        throw new SubTypeEnumHasToBeSelfRegistering();
    }

}
