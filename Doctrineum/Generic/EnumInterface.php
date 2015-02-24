<?php
namespace Doctrineum\Generic;

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
