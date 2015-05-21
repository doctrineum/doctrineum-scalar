<?php
namespace Doctrineum\Scalar\Exceptions;

class LogicTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function is_interface()
    {
        $this->assertTrue(interface_exists('Doctrineum\Scalar\Exceptions\Logic'));
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\Exception
     */
    public function extends_local_mark_interface()
    {
        throw new TestLogicInterface();
    }
}

/** inner */
class TestLogicInterface extends \Exception implements Logic
{

}
