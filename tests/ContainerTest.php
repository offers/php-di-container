<?php
class ContainerTest extends PHPUnit\Framework\TestCase
{
    public function testGetAutoResolver()
    {
        $diContainer = new \Offers\Di\Container();
        $this->assertInstanceOf(\Offers\Di\AutoResolver::class, $diContainer->getAutoResolver());
    }

    public function testService()
    {
        $dic = new \Offers\Di\Container();
        $dic->service(stdClass::class, function() {
            return new stdClass();
        });
        $this->assertTrue($dic->get(stdClass::class) === $dic->get(stdClass::class));
    }

    public function testFactory()
    {
        $dic = new \Offers\Di\Container();
        $dic->factory(stdClass::class, function() {
            return new stdClass();
        });
        $this->assertFalse($dic->get(stdClass::class) === $dic->get(stdClass::class));
    }

    public function testHas()
    {
        $dic = new \Offers\Di\Container();
        $dic->factory(stdClass::class, function() {
            return new stdClass();
        });
        $this->assertTrue($dic->has(stdClass::class));
    }

}