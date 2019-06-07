<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use \Gaslawork\Container;


final class RoutingTest extends TestCase {

    public function testGetValue()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $this->assertEquals("bar", $container->get("foo"));
    }

    public function testGetClosure()
    {
        $container = new Container;
        $container->set("hello", function($c){
            return "world";
        });
        $this->assertEquals("world", $container->get("hello"));
    }

    public function testGetClosureTwice()
    {
        $container = new Container;
        $container->set("hello", function($c){
            return "world";
        });
        $this->assertEquals("world", $container->get("hello"));
        $this->assertEquals("world", $container->get("hello"));
    }

    public function testGetNested()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("hello", function($c){
            return $c->get("foo");
        });
        $this->assertEquals("bar", $container->get("hello"));
    }

    public function testGetNestedTwice()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("hello", function($c){
            return $c->get("foo");
        });
        $this->assertEquals("bar", $container->get("hello"));
        $this->assertEquals("bar", $container->get("hello"));
    }

    public function testGetFactory()
    {
        $container = new Container;
        $container->factory("foo", function($c){
            return "bar";
        });
        $this->assertEquals("bar", $container->get("foo"));
    }

    public function testGetFactoryTwice()
    {
        $container = new Container;
        $container->factory("hello", function($c){
            return "world";
        });
        $this->assertEquals("world", $container->get("hello"));
        $this->assertEquals("world", $container->get("hello"));
    }

    public function testGetFactoryNested()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->factory("hello", function($c){
            return $c->get("foo");
        });
        $this->assertEquals("bar", $container->get("hello"));
    }

    public function testGetFactoryNestedTwice()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->factory("hello", function($c){
            return $c->get("foo");
        });
        $this->assertEquals("bar", $container->get("hello"));
        $this->assertEquals("bar", $container->get("hello"));
    }

    public function testSingletonSame()
    {
        $container = new Container;
        $container->set("time", function(){
            return microtime((true));
        });

        $first = $container->get("time");
        usleep(2);
        $this->assertEquals($first, $container->get("time"));
        usleep(2);
        $this->assertEquals($first, $container->get("time"));
    }

    public function testFactoryNotSame()
    {
        $container = new Container;
        $container->factory("time", function(){
            return microtime((true));
        });

        $first = $container->get("time");
        usleep(2);
        $this->assertNotEquals($first, $container->get("time"));
        usleep(2);
        $this->assertNotEquals($first, $container->get("time"));
    }

    public function testFetchingNonExisting()
    {
        $this->expectException(\Gaslawork\Exception\ContainerEntryNotFoundException::class);

        $container = new Container;
        $container->get("ohno");
    }

    public function testModifyFrozenEntry()
    {
        $this->expectException(\Gaslawork\Exception\ContainerEntryUsedException::class);

        $container = new Container;
        $container->set("foo", "bar");
        $container->get("foo");
        $container->set("foo", "world");
    }

    public function testModifyFrozenEntryWhenNested()
    {
        $this->expectException(\Gaslawork\Exception\ContainerEntryUsedException::class);

        $container = new Container;
        $container->set("foo", "bar");
        $container->get("foo");

        $container->factory("ohno", function($c){
            $c->set("foo", "world");
        });
        $container->get("ohno");
    }

    public function testModifyFrozenEntryWhenNestedDifferentOrder()
    {
        $this->expectException(\Gaslawork\Exception\ContainerEntryUsedException::class);

        $container = new Container;
        $container->set("foo", "bar");
        $container->factory("ohno", function($c){
            $c->set("foo", "world");
        });

        $container->get("foo");
        $container->get("ohno");
    }

    public function testGetObjectValue()
    {
        $obj = new \stdClass;

        $container = new Container;
        $container->set("foo", $obj);

        $this->assertSame($obj, $container->get("foo"));

    }

    public function testGetObjectViaClosure()
    {
        $obj = new \stdClass;

        $container = new Container;
        $container->set("foo", function($c) use ($obj) {
            return $obj;
        });

        $this->assertSame($obj, $container->get("foo"));
    }

    public function testGetObjectViaFactory()
    {
        $obj = new \stdClass;

        $container = new Container;
        $container->factory("foo", function($c) use ($obj) {
            return $obj;
        });

        $this->assertSame($obj, $container->get("foo"));
    }

    public function testGetIntValue()
    {
        $container = new Container;
        $container->set("foo", 12345);
        $this->assertSame(12345, $container->get("foo"));

    }

    public function testGetIntViaClosure()
    {
        $container = new Container;
        $container->set("foo", function($c) {
            return 12345;
        });

        $this->assertSame(12345, $container->get("foo"));
    }

    public function testGetIntViaFactory()
    {
        $container = new Container;
        $container->factory("foo", function($c) {
            return 12345;
        });

        $this->assertSame(12345, $container->get("foo"));
    }

    public function testGetFloatValue()
    {
        $container = new Container;
        $container->set("foo", 12.345);
        $this->assertSame(12.345, $container->get("foo"));

    }

    public function testGetFloatViaClosure()
    {
        $container = new Container;
        $container->set("foo", function($c) {
            return 12.345;
        });

        $this->assertSame(12.345, $container->get("foo"));
    }

    public function testGetFloatViaFactory()
    {
        $container = new Container;
        $container->factory("foo", function($c) {
            return 12.345;
        });

        $this->assertSame(12.345, $container->get("foo"));
    }

    public function testNestSeveralLevels()
    {
        $container = (new Container)
            ->set("greeting", "Hello!")
            ->set("say", function($c){
                return $c->get("greeting");
            })
            ->factory("yo", function($c){
                return $c->get("say");
            });

        $this->assertEquals("Hello!", $container->get("yo"));
    }

    public function testNestSeveralLevelsReversedOrder()
    {
        $container = (new Container)
            ->factory("yo", function($c){
                return $c->get("say");
            })
            ->set("say", function($c){
                return $c->get("greeting");
            })
            ->set("greeting", "Hello!");

        $this->assertEquals("Hello!", $container->get("yo"));
    }

    public function testHas()
    {
        $container = (new Container)
            ->set("a", "Hello")
            ->set("b", 1234)
            ->set("c", 12.345)
            ->set("d", 0)
            ->set("e", 0.0)
            ->set("f", -1)
            ->set("g", false)
            ->set("h", true)
            ->set("i", new \stdClass)
            ->set("j", "")
            ->set("k", null);

        $this->assertTrue($container->has("a"));
        $this->assertTrue($container->has("b"));
        $this->assertTrue($container->has("c"));
        $this->assertTrue($container->has("d"));
        $this->assertTrue($container->has("e"));
        $this->assertTrue($container->has("f"));
        $this->assertTrue($container->has("g"));
        $this->assertTrue($container->has("h"));
        $this->assertTrue($container->has("i"));
        $this->assertTrue($container->has("j"));
        $this->assertTrue($container->has("k"));

        $this->assertFalse($container->has("nope"));
    }

    public function testOverwriteValueToValue()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("foo", "world");
        $this->assertEquals("world", $container->get("foo"));
    }

    public function testOverwriteValueToClosure()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("foo", function($c){
            return "abc!";
        });
        $this->assertEquals("abc!", $container->get("foo"));
    }

    public function testOverwriteValueToFactory()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->factory("foo", function($c){
            return "pizza!";
        });
        $this->assertEquals("pizza!", $container->get("foo"));
    }

    public function testOverwriteClosureToValue()
    {
        $container = new Container;
        $container->set("foo", function($c){
            return "bar";
        });
        $container->set("foo", "world");
        $this->assertEquals("world", $container->get("foo"));
    }

    public function testOverwriteClosureToClosure()
    {
        $container = new Container;
        $container->set("foo", function($c){
            return "bar";
        });
        $container->set("foo", function($c){
            return "abc!";
        });
        $this->assertEquals("abc!", $container->get("foo"));
    }

    public function testOverwriteClosureToFactory()
    {
        $container = new Container;
        $container->set("foo", function($c){
            return "bar";
        });
        $container->factory("foo", function($c){
            return "pizza!";
        });
        $this->assertEquals("pizza!", $container->get("foo"));
    }

    public function testOverwriteFactoryToValue()
    {
        $container = new Container;
        $container->factory("foo", function($c){
            return "bar";
        });
        $container->set("foo", "world");
        $this->assertEquals("world", $container->get("foo"));
    }

    public function testOverwriteFactoryToClosure()
    {
        $container = new Container;
        $container->factory("foo", function($c){
            return "bar";
        });
        $container->set("foo", function($c){
            return "abc!";
        });
        $this->assertEquals("abc!", $container->get("foo"));
    }

    public function testOverwriteFactoryToFactory()
    {
        $container = new Container;
        $container->factory("foo", function($c){
            return "bar";
        });
        $container->factory("foo", function($c){
            return "pizza!";
        });
        $this->assertEquals("pizza!", $container->get("foo"));
    }

    public function testOverwriteValueMulitple()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("foo", "world");
        $container->set("foo", "abc!");
        $container->set("foo", "pizza!");
        $this->assertEquals("pizza!", $container->get("foo"));
    }

    public function testOverwriteValueMulitpleAgain()
    {
        $container = new Container;
        $container->set("foo", "bar");
        $container->set("foo", "world");
        $container->set("foo", function($c){
            return "abc!";
        });
        $container->set("foo", function($c){
            return "pizza!";
        });
        $this->assertEquals("pizza!", $container->get("foo"));
    }


    public function testIntAsIdInGet()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->get(1234);
    }

    public function testFloatAsIdInGet()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->get(12.34);
    }

    public function testObjectAsIdInGet()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->get(new \stdClass);
    }

    public function testIntStringAsIdInGet()
    {
        $container = (new Container)
            ->set("1234", "Yes!");

        $this->assertEquals("Yes!", $container->get("1234"));
    }

    public function testFloatStringAsIdInGet()
    {
        $container = (new Container)
            ->set("12.34", "Yes!");

        $this->assertEquals("Yes!", $container->get("12.34"));
    }

    public function testIntAsIdInHas()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->has(1234);
    }

    public function testFloatAsIdInHas()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->has(12.34);
    }

    public function testObjectAsIdInHas()
    {
        $this->expectException(\Gaslawork\Exception\ContainerIdInvalidTypeException::class);

        (new Container)
            ->has(new \stdClass);
    }

    public function testIntStringAsIdInHas()
    {
        $container = (new Container)
            ->set("1234", "Yes!");

        $this->assertTrue($container->has("1234"));
    }

    public function testFloatStringAsIdInHas()
    {
        $container = (new Container)
            ->set("12.34", "Yes!");

        $this->assertTrue($container->has("12.34"));
    }
}
