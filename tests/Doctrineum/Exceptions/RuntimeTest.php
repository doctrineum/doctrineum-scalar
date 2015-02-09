<?php
namespace Doctrineum\Exceptions;

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
     * @expectedException \Doctrineum\Exceptions\Exception
     */
    public function extends_base_mark_interface()
    {
        throw new TestRuntimeInterface();
    }
}

/** inner */
class TestRuntimeInterface extends \Exception implements Runtime
{

}
