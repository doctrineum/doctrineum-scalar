<?php
namespace Doctrineum\Scalar\Exceptions;

class TypeNameOccupiedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new TypeNameOccupied();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\Logic
     */
    public function is_local_logic_exception()
    {
        throw new TypeNameOccupied();
    }

}
