<?php
declare(strict_types=1);

namespace Doctrineum\Tests\Scalar\Helpers;

use Granam\Scalar\ScalarInterface;
use Granam\ScalarEnum\ScalarEnumInterface;
use Granam\Strict\Object\StrictObject;

class TestSubTypeScalarEnum extends StrictObject implements ScalarEnumInterface
{
    public static function getEnum($enumValue): TestSubTypeScalarEnum
    {
        static $instances;
        if ($instances === null || ($instances[$enumValue] ?? null) === null) {
            $instances[$enumValue] = new static($enumValue);
        }

        return $instances[$enumValue];
    }

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function is($enum, bool $sameClassOnly = true): bool
    {
        if (!($enum instanceof ScalarInterface)) {
            return $this->getValue() === $enum;
        }
        return $this->getValue() === $enum->getValue()
            && (!$sameClassOnly || static::class === \get_class($enum));
    }

    public function __toString()
    {
        return (string)$this->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }

}