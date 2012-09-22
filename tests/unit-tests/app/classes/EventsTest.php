<?php
class EventsTest extends PHPUnit_Framework_TestCase {

  private $counter = 0;

  private $events = null;

  public function onTestEvent1($h) { $this->events->attach($h); }
  public function onTestEvent2() { $this->events->attach(); }

  public function setUp() {
    $this->events = new Events($this);
  }

  public function testAttachAndTrigger() {
    $this->onTestEvent1(array($this, 'eventHandler'));
    $this->onTestEvent2(array($this, 'eventHandler'));

    $this->events->trigger('onTestEvent1');
    $this->events->trigger('onSomethingElse');

    $this->assertEquals(1, $this->counter);

    $this->events->trigger('onTestEvent2');

    $this->assertEquals(2, $this->counter);

    $this->onTestEvent1(array($this, 'eventHandler'));
    $this->onTestEvent1(array($this, 'eventHandler'));

    $this->events->trigger('onTestEvent1');

    $this->assertEquals(5, $this->counter);
  }

  public function eventHandler($sender, $eventArgs) {
    $this->counter++;
  }
}
