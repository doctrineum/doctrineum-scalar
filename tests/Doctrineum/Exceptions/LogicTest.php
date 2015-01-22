<?php
namespace Doctrineum\Exceptions;

class LogicTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @expectedException \Exception
     */
    public function is_exception()
    {
        throw new Logic();
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new Logic();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\Exception
     */
    public function is_local_exception()
    {
        throw new Logic();
    }
}
