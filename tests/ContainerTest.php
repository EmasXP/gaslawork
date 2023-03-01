<?php

namespace Gaslawork\Tests;

use PHPUnit\Framework\TestCase;
use \Gaslawork\Container\Container;
use \Gaslawork\Container\ClassName;
use \Gaslawork\Container\AutoWire;

class DummyClass {

    /** @var int */
    public $id;

    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

}

class DummyParentClass {

    /** @var DummyClass */
    public $child;

    public function __construct(DummyClass $child)
    {
         $this->child = $child;
    }

}

class UnsatisfiableClass {

    /** @var mixed */
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

}

class TypedUnsatisfiableClass {

    /** @var int */
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

}

class ParentUnsatisfiableClass {

    /** @var UnsatisfiableClass */
    public $child;

    public function __construct(UnsatisfiableClass $child)
    {
        $this->child = $child;
    }

}


final class ContainerTest extends TestCase {

    public function testSetGetProperty(): void
    {
        $container = new Container;

        $container->setProperty("foo", "bar");
        $this->assertEquals("bar", $container->getProperty("foo"));

        $container->setProperty("a", "");
        $this->assertEquals("", $container->getProperty("a"));

        $cls = new DummyClass(123);
        $container->setProperty("b", $cls);
        $this->assertEquals($cls, $container->getProperty("b"));

        $container->setProperty("c", false);
        $this->assertEquals(false, $container->getProperty("c"));

        $container->setProperty("d", null);
        $this->assertEquals(null, $container->getProperty("d"));

        $container->setProperty("e", 123);
        $this->assertEquals(123, $container->getProperty("e"));

        $container->setProperty("f", true);
        $this->assertEquals(true, $container->getProperty("f"));

        $container->setProperty("g", 0);
        $this->assertEquals(0, $container->getProperty("g"));
    }

    public function testSetGetSingleton(): void
    {
        $container = new Container;
        $container->setSingleton(DummyClass::class, function($c){
            return new DummyClass;
        });
        $this->assertInstanceOf(DummyClass::class, $container->get(DummyClass::class));
    }

    public function testChangingProperty(): void
    {
        $container = new Container;
        $container->setProperty("foo", "bar");
        $this->assertEquals("bar", $container->getProperty("foo"));
        $container->setProperty("foo", "hello");
        $this->assertEquals("hello", $container->getProperty("foo"));
    }

    public function testHasProperty(): void
    {
        $container = new Container;

        $this->assertFalse($container->hasProperty("foo"));

        $container->setProperty("foo", "bar");
        $this->assertTrue($container->hasProperty("foo"));

        $this->assertFalse($container->hasProperty("hello"));

        $container->setProperty("a", true);
        $this->assertTrue($container->hasProperty("a"));

        $container->setProperty("b", false);
        $this->assertTrue($container->hasProperty("b"));

        $container->setProperty("c", null);
        $this->assertTrue($container->hasProperty("c"));

        $container->setProperty("d", "");
        $this->assertTrue($container->hasProperty("d"));

        $container->setProperty("e", 0);
        $this->assertTrue($container->hasProperty("e"));
    }

    public function testGetSingletonSameInstance(): void
    {
        $counter = 0;

        $container = new Container;
        $container->setSingleton(
            DummyClass::class,
            function($c) use (&$counter) {
                $counter ++;
                return new DummyClass($counter);
            }
        );
        $this->assertEquals(
            $container->get(DummyClass::class),
            $container->get(DummyClass::class)
        );
    }

    public function testSetGetCreate(): void
    {
        $container = new Container;
        $container->setCreate(DummyClass::class, function($c){
            return new DummyClass;
        });
        $this->assertInstanceOf(DummyClass::class, $container->get(DummyClass::class));
    }

    public function testGetCreateNotSameInstance(): void
    {
        $counter = 0;

        $container = new Container;
        $container->setCreate(
            DummyClass::class,
            function($c) use (&$counter) {
                $counter ++;
                return new DummyClass($counter);
            }
        );
        $this->assertNotEquals(
            $container->get(DummyClass::class),
            $container->get(DummyClass::class)
        );
    }

    public function testGetPropertyFromCreateFactory(): void
    {
        $container = new Container;
        $container->setProperty("id", 1234);
        $container->setCreate(
            DummyClass::class,
            function(Container $c) {
                return new DummyClass($c->getProperty("id"));
            }
        );
        $this->assertEquals(
            1234,
            $container->get(DummyClass::class)->id
        );
    }

    public function testGetPropertyFromSingletonFactory(): void
    {
        $container = new Container;
        $container->setProperty("id", 1234);
        $container->setSingleton(
            DummyClass::class,
            function(Container $c) {
                return new DummyClass($c->getProperty("id"));
            }
        );
        $this->assertEquals(
            1234,
            $container->get(DummyClass::class)->id
        );
    }

    public function testSingletonResetAfterAlter(): void
    {
        $container = new Container;

        $container->setSingleton(
            DummyClass::class,
            function($c) {
                return new DummyClass(1);
            }
        );

        $first = $container->get(DummyClass::class);

        $container->setSingleton(
            DummyClass::class,
            function($c) {
                return new DummyClass(2);
            }
        );

        $second = $container->get(DummyClass::class);

        $this->assertNotEquals($first, $second);
        $this->assertEquals(1, $first->id);
        $this->assertEquals(2, $second->id);
    }

    public function testSingletonResetAfterClear(): void
    {
        $counter = 0;

        $container = new Container;
        $container->setSingleton(
            DummyClass::class,
            function($c) use (&$counter) {
                $counter ++;
                return new DummyClass($counter);
            }
        );

        $first = $container->get(DummyClass::class);

        $container->clearSingletons();

        $second = $container->get(DummyClass::class);

        $this->assertNotEquals($first, $second);
        $this->assertEquals(1, $first->id);
        $this->assertEquals(2, $second->id);
    }

    public function testSingletonResetAfterChangedToCreate(): void
    {
        $container = new Container;

        $container->setSingleton(
            DummyClass::class,
            function($c) {
                return new DummyClass(1);
            }
        );

        $first = $container->get(DummyClass::class);

        $container->setCreate(
            DummyClass::class,
            function($c) {
                return new DummyClass(2);
            }
        );

        $second = $container->get(DummyClass::class);

        $this->assertNotEquals($first, $second);
        $this->assertEquals(1, $first->id);
        $this->assertEquals(2, $second->id);
    }

    public function testSingletonSameAfterChangedFromCreate(): void
    {
        $container = new Container;

        $container->setCreate(
            DummyClass::class,
            function($c) {
                return new DummyClass(-1);
            }
        );

        $first = $container->get(DummyClass::class);

        $counter = 0;

        $container = new Container;
        $container->setSingleton(
            DummyClass::class,
            function($c) use (&$counter) {
                $counter ++;
                return new DummyClass($counter);
            }
        );

        $second = $container->get(DummyClass::class);
        $third = $container->get(DummyClass::class);

        $this->assertNotEquals($first, $second);
        $this->assertEquals($second, $third);
        $this->assertEquals(-1, $first->id);
        $this->assertEquals(1, $second->id);
        $this->assertEquals(1, $third->id);
    }

    public function testHas(): void
    {
        $container = new Container;

        $this->assertFalse($container->has("foo"));
        $this->assertFalse($container->has("moo"));

        $container->setSingleton("foo", function(){});
        $container->setSingleton("moo", function(){});

        $this->assertTrue($container->has("foo"));
        $this->assertTrue($container->has("moo"));

        $this->assertFalse($container->has(DummyClass::class));
        $container->setSingleton(DummyClass::class, function(){});
        $this->assertTrue($container->has(DummyClass::class));
    }

    public function testClassName(): void
    {
        $classname = new ClassName(DummyClass::class);
        $this->assertEquals(DummyClass::class, $classname->getName());

        $moo = new ClassName("moo");
        $this->assertEquals("moo", $moo->getName());
    }

    public function testAutoWire(): void
    {
        $container = new Container;

        $aw = new AutoWire(DummyClass::class);

        $this->assertInstanceOf(
            DummyClass::class,
            $aw->create($container)
        );

        $this->assertEquals(
            0,
            $aw->create($container)->id
        );
    }

    public function testAutoWireDependency(): void
    {
        $container = new Container;

        $aw = new AutoWire(DummyParentClass::class);

        $this->assertInstanceOf(
            DummyParentClass::class,
            $aw->create($container)
        );

        $this->assertInstanceOf(
            DummyClass::class,
            $aw->create($container)->child
        );

        $this->assertEquals(
            0,
            $aw->create($container)->child->id
        );
    }

    public function testAutoWireFactoryDependency(): void
    {
        $container = new Container;

        $container->setCreate(
            DummyClass::class,
            function($c){
                return new DummyClass(123);
            }
        );

        $aw = new AutoWire(DummyParentClass::class);

        $this->assertEquals(
            123,
            $aw->create($container)->child->id
        );
    }

    public function testAutoWireMixes(): void
    {
        $container = new Container;

        $container->setCreate(
            DummyParentClass::class,
            function($c){
                return new DummyParentClass(
                    $c->get(DummyClass::class)
                );
            }
        );

        $this->assertInstanceOf(
            DummyParentClass::class,
            $container->get(DummyParentClass::class)
        );

        $this->assertEquals(
            0,
            $container->get(DummyParentClass::class)->child->id
        );

        $container->setCreate(
            DummyParentClass::class,
            function($c){
                return new DummyParentClass(
                    new DummyClass(123)
                );
            }
        );

        $this->assertInstanceOf(
            DummyParentClass::class,
            $container->get(DummyParentClass::class)
        );

        $this->assertEquals(
            123,
            $container->get(DummyParentClass::class)->child->id
        );

        $container->setProperty("id", 1024);

        $container->setCreate(
            DummyParentClass::class,
            function(Container $c){
                return new DummyParentClass(
                    new DummyClass(
                        $c->getProperty("id")
                    )
                );
            }
        );

        $this->assertInstanceOf(
            DummyParentClass::class,
            $container->get(DummyParentClass::class)
        );

        $this->assertEquals(
            1024,
            $container->get(DummyParentClass::class)->child->id
        );
    }

    public function testUnsatisfiableDependency(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency \$id of Gaslawork\Tests\UnsatisfiableClass");
        new AutoWire(UnsatisfiableClass::class);
    }

    public function testUnsatisfiableTypedDependency(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency int \$id of Gaslawork\Tests\TypedUnsatisfiableClass");
        new AutoWire(TypedUnsatisfiableClass::class);
    }

    public function testUnsatisfiableDependencyFromContainer(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency \$id of Gaslawork\Tests\UnsatisfiableClass");
        $container = new Container;
        $container->get(UnsatisfiableClass::class);
    }

    public function testUnsatisfiableTypedDependencyFromContainer(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency int \$id of Gaslawork\Tests\TypedUnsatisfiableClass");
        $container = new Container;
        $container->get(TypedUnsatisfiableClass::class);
    }

    public function testUnsatisfiableDependencyWorksWithFactory(): void
    {
        $container = new Container;

        $container->setCreate(
            UnsatisfiableClass::class,
            function($c){
                return new UnsatisfiableClass(123);
            }
        );

        $container->setCreate(
            TypedUnsatisfiableClass::class,
            function($c){
                return new UnsatisfiableClass(1024);
            }
        );

        $first = $container->get(UnsatisfiableClass::class);
        $second = $container->get(TypedUnsatisfiableClass::class);

        $this->assertEquals(123, $first->id);
        $this->assertEquals(1024, $second->id);
    }

    public function testUnsatisfiableChild(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency \$id of Gaslawork\Tests\UnsatisfiableClass");

        $container = new Container;
        $aw = new AutoWire(ParentUnsatisfiableClass::class);
        $aw->create($container);
    }

    public function testUnsatisfiableChildWorksWithFactory(): void
    {
        $container = new Container;

        $container->setCreate(
            UnsatisfiableClass::class,
            function($c){
                return new UnsatisfiableClass(123);
            }
        );

        $aw = new AutoWire(ParentUnsatisfiableClass::class);

        $this->assertEquals(123, $aw->create($container)->child->id);
    }

    public function testUnsatisfiableChildFromContainer(): void
    {
        $this->expectException(\Gaslawork\Container\UnsatisfiableDependencyException::class);
        $this->expectExceptionMessage("Cannot satisfy dependency \$id of Gaslawork\Tests\UnsatisfiableClass");

        $container = new Container;
        $container->get(ParentUnsatisfiableClass::class);
    }

    public function testUnsatisfiableChildWorksWithFactoryFromContainer(): void
    {
        $container = new Container;

        $container->setCreate(
            UnsatisfiableClass::class,
            function($c){
                return new UnsatisfiableClass(123);
            }
        );

        $this->assertEquals(
            123,
            $container->get(ParentUnsatisfiableClass::class)->child->id
        );
    }

    /*
    getProperty non-existing property
    get() non-existing
    AutoWire
        Fallbacks to AutoWire if not Factory
    Autowire cache reset
    */
}
