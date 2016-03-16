<?php
namespace Doctrineum\Scalar;

use Granam\Scalar\ScalarInterface;

interface Enum extends ScalarInterface
{
    /**
     * @param Enum $enum
     * @return bool
     */
    public function is(Enum $enum);
}
