<?php
namespace Doctrineum\Exceptions;

class CanNotBeClonedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new CanNotBeCloned();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\Logic
     */
    public function is_local_logic_exception()
    {
        throw new CanNotBeCloned();
    }

}
