<?php
namespace Doctrineum\Scalar;

use Granam\Scalar\ScalarInterface;

interface ScalarEnumInterface extends ScalarInterface
{
    /**
     * @param ScalarEnumInterface $enum
     * @return bool
     */
    public function is(ScalarEnumInterface $enum);
}
