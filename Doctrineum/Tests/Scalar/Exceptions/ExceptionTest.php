<?php
namespace Doctrineum\Tests\Scalar\Exceptions;

use Doctrineum\Scalar\Exceptions\Exception as LocalException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function is_interface()
    {
        $this->assertTrue(interface_exists(LocalException::class));
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\Exception
     */
    public function is_local_mark_interface()
    {
        throw new TestExceptionInterface();
    }
}

/** inner */
class TestExceptionInterface extends \Exception implements LocalException
{

}
