<?php
namespace Doctrineum\Tests\Scalar\Helpers;

use Granam\ScalarEnum\ScalarEnum;

class TestInheritedScalarEnum extends ScalarEnum
{
    public function __construct($enumValue)
    {
        parent::__construct($enumValue);
    }
}