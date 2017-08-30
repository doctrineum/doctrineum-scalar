<?php
namespace Doctrineum\Scalar;

use Granam\Scalar\ScalarInterface;

interface ScalarEnumInterface extends ScalarInterface
{
    /**
     * @param ScalarEnumInterface $enum
     * @param bool $sameClassOnly = true
     * @return bool
     */
    public function is(ScalarEnumInterface $enum, bool $sameClassOnly = true): bool;
}