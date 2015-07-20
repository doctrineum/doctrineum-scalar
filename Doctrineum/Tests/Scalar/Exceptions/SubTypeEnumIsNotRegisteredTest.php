<?php
namespace Doctrineum\Tests\Scalar\Exceptions;

use Doctrineum\Scalar\Exceptions\SubTypeEnumIsNotRegistered;

class SubTypeEnumIsNotRegisteredTest extends \PHPUnit_Framework_TestCase
{

    /**
    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new SubTypeEnumIsNotRegistered();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\Logic
     */
    public function is_local_logic_exception()
    {
        throw new SubTypeEnumIsNotRegistered();
    }
}
