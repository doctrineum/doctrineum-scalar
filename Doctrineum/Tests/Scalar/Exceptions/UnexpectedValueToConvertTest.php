<?php
namespace Doctrineum\Tests\Scalar\Exceptions;

use Doctrineum\Scalar\Exceptions\UnexpectedValueToConvert;

class UnexpectedValueToConvertTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function is_invalid_argument_exception()
    {
        throw new UnexpectedValueToConvert();
    }

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\InvalidArgument
     */
    public function is_local_invalid_argument_exception()
    {
        throw new UnexpectedValueToConvert();
    }

}
