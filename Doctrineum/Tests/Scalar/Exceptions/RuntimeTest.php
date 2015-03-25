<?php
namespace Doctrineum\Scalar\Exceptions;

class RuntimeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function is_interface()
    {
        $this->assertTrue(interface_exists(Runtime::class));
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
class TestRuntimeInterface extends \Exception implements Runtime
{

}
