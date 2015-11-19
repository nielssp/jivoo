<?php

namespace Jivoo\Core;

class EventManagerTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}
  
  public function testAttachAndDetach() {
    $subject1 = $this->getMockBuilder('Jivoo\Core\EventSubject')->getMock();
    $subject1->method('getEvents')
      ->willReturn(array('someEvent'));
    $subject2 = $this->getMockBuilder('Jivoo\Core\EventSubject')->getMock();
    $subject2->method('getEvents')
      ->willReturn(array('someEvent'));
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

    $this->assertThrows('Jivoo\Core\EventException', function() use($em1) {
      $em1->attachHandler('someOtherEvent', null);
    });
    $this->assertThrows('Jivoo\Core\EventException', function() use($em1) {
      $em1->trigger('someOtherEvent');
    });
  }
  
  public function testListener() {
    $subject1 = $this->getMockBuilder('Jivoo\Core\EventSubject')->getMock();
    $subject1->method('getEvents')
      ->willReturn(array('someEvent'));
    $subject2 = $this->getMockBuilder('Jivoo\Core\EventSubject')->getMock();
    $subject2->method('getEvents')
      ->willReturn(array('someEvent'));
    $em1 = new EventManager($subject1);
    $em2 = new EventManager($subject2, $em1);
    $l = $this->getMockBuilder('Jivoo\Core\EventListener')
      ->setMethods(array('getEventHandlers', 'someEvent'))
      ->getMock();
    $l->method('getEventHandlers')
      ->wilLReturn(array(get_class($subject2) . '.someEvent'));
    $l->expects($this->once())
      ->method('someEvent')
      ->willReturn(false);
    $em1->attachListener($l);
    $this->assertFalse($em2->trigger('someEvent'));
    $em1->detachListener($l);
    $this->assertTrue($em2->trigger('someEvent'));
  }
}
