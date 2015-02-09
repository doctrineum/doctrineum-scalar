<?php
namespace Doctrineum\Exceptions;

class UnexpectedValueToDatabaseValueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @expectedException \Doctrineum\Exceptions\UnexpectedValueToConvert
     */
    public function is_local_unexpected_value_to_convert_exception()
    {
        throw new UnexpectedValueToDatabaseValue();
    }

}
