<?php
namespace Doctrineum\Generic\Exceptions;

class MissingCurrentlyBuiltEnumNamespaceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \LogicException
     */
    public function is_logic_exception()
    {
        throw new MissingCurrentlyBuiltEnumNamespace();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Generic\Exceptions\Logic
     */
    public function is_local_logic_exception()
    {
        throw new MissingCurrentlyBuiltEnumNamespace();
    }

}
