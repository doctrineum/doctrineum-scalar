<?php
namespace Doctrineum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Granam\StrictObject\Tests\StrictObjectTestTrait;

class EnumTypeTest extends \PHPUnit_Framework_TestCase
{
    use StrictObjectTestTrait;

    protected function setUp()
    {
        if (\Doctrine\DBAL\Types\Type::hasType(EnumType::TYPE)) {
            \Doctrine\DBAL\Types\Type::overrideType(EnumType::TYPE, EnumType::class);
        } else {
            \Doctrine\DBAL\Types\Type::addType(EnumType::TYPE, EnumType::class);
        }
    }

    /**
     * @return \Doctrine\DBAL\Types\Type
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createObjectInstance()
    {
        return EnumType::getType(EnumType::TYPE);
    }

    /** @test */
    public function instance_can_be_obtained()
    {
        $instance = EnumType::getType(EnumType::TYPE);
        $this->assertInstanceOf(EnumType::class, $instance);
    }

    /** @test */
    public function sql_declaration_is_valid(){
        $enumType = EnumType::getType(EnumType::TYPE);
        $sql = $enumType->getSQLDeclaration([], \Mockery::mock(AbstractPlatform::class));
        $this->assertSame('VARCHAR(64)', $sql);
    }
}
