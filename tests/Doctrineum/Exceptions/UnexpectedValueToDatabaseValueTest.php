<?php
namespace Doctrineum\Exceptions;

class UnexpectedValueToDatabaseValueTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     * @expectedException \Exception
     */
    public function is_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function is_invalid_argument_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\Exception
     */
    public function is_local_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\InvalidArgument
     */
    public function is_local_invalid_argument_exception_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\UnexpectedValueToConvert
     */
    public function is_local_unexpected_value_to_convert_exception_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

}
