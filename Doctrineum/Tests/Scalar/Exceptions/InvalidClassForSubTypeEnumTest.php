<?php
namespace Doctrineum\Scalar\Exceptions;

class InvalidClassForSubTypeEnumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new InvalidClassForSubTypeEnum();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\Logic
     */
    public function is_local_logic_exception()
    {
        throw new InvalidClassForSubTypeEnum();
    }

}
