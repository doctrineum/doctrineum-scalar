<?php
namespace Doctrineum\Scalar;

use Doctrineum\Tests\Scalar\EnumTestTrait;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    use EnumTestTrait;

    protected function getInheritedEnum($value)
    {
        return new TestInheritedEnum($value);
    }
}

/** inner */
class TestInheritedEnum extends Enum
{

}
