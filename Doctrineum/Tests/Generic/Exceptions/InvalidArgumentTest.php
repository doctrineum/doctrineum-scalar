<?php
namespace Doctrineum\Generic\Exceptions;

class InvalidArgumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function is_standard_invalid_argument_exception()
    {
        throw new InvalidArgument();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\Logic
     */
    public function extends_local_logic_interface()
    {
        throw new InvalidArgument();
    }
}

