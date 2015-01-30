<?php
namespace Doctrineum;

use Granam\StrictObject\StrictObject;

/**
 * Inspired by @link http://github.com/marc-mabe/php-enum
 */
class Enum extends StrictObject implements EnumInterface
{
    use EnumTrait;
}
