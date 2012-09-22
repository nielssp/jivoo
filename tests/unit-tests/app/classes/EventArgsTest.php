<?php

class EventArgsTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    require 'ConcreteEventArgs.php';
  }

  public function testConstruct() {
    $eventArgs = new ConcreteEventArgs(42, 23);

    $this->assertEquals(42, $eventArgs->foo);
    $this->assertEquals(23, $eventArgs->bar);
    $this->assertEquals('Hello, World', $eventArgs->baz);
    $this->assertNull($eventArgs->foobar);

    $eventArgs = new ConcreteEventArgs(null, null, 'Hello');

    $this->assertEquals(23, $eventArgs->foo);
    $this->assertEquals(42, $eventArgs->bar);
    $this->assertEquals('Hello', $eventArgs->baz);

    $eventArgs = new ConcreteEventArgs(null, null, null, 23);

    $this->assertEquals(23, $eventArgs->foo);
    $this->assertEquals(42, $eventArgs->bar);
    $this->assertEquals('Hello, World', $eventArgs->baz);
  }
}
