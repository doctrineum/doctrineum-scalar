<?php
namespace Doctrineum;

interface EnumInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return static
     */
    public function __clone();

    /**
     * @return string
     */
    public function getValue();

}
