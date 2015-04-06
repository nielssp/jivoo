<?php

namespace Jivoo\Core;

class EventManagerTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}
  
  public function testAttachAndDetach() {
    $subject1 = $this->getMockBuilder('Jivoo\Core\IEventSubject')->getMock();
    $subject2 = $this->getMockBuilder('Jivoo\Core\IEventSubject')->getMock();
    $em1 = new EventManager($subject1);
    $em2 = new EventManager($subject2, $em1);
    $c = function() { return false; };
    $em1->attachHandler('someEvent', $c);
    $this->assertFalse($em1->trigger('someEvent'));
    $em1->detachHandler('someEvent', $c);
    $this->assertTrue($em1->trigger('someEvent'));
    $em2->attachHandler(get_class($subject1) . '.someEvent', $c);
    $this->assertFalse($em2->trigger(get_class($subject1) . '.someEvent'));
    $em2->detachHandler(get_class($subject1) . '.someEvent', $c);
    $this->assertTrue($em2->trigger(get_class($subject1) . '.someEvent'));
  }
}
