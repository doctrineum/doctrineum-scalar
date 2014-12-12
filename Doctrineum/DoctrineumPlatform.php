<?php
namespace Doctrineum;

interface DoctrineumPlatform
{
    /**
     * Gives SQL declaration of the field for enum, as VARCHAR() for example
     *
     * @return string
     */
    public function getEnumSQLDeclaration();

    /**
     * @param string|null $valueFromDatabase
     * @return Enum|null
     */
    public function convertToEnum($valueFromDatabase);
} 
