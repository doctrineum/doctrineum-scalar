<?php
namespace Doctrineum\Tests\Scalar\Exceptions;

use Doctrineum\Scalar\Exceptions\UnexpectedValueToDatabaseValue;

class UnexpectedValueToDatabaseValueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \Doctrineum\Scalar\Exceptions\UnexpectedValueToConvert
     */
    public function is_local_unexpected_value_to_convert_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

}
