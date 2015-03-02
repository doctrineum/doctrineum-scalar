<?php
namespace Doctrineum\Scalar;

interface EnumInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getEnumValue();

}
