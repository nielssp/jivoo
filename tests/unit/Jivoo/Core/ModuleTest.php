<?php

namespace Jivoo\Core;

use Jivoo\InvalidPropertyException;
use Jivoo\InvalidMethodException;

class ModuleTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;
  
  protected function _before() {}

  protected function _after() {}

  public function testConstruction() {
    $app = $this->getMockBuilder('Jivoo\Core\App')
      ->disableOriginalConstructor()
      ->getMock();
    $app->method('getModules')
      ->wilLReturn((object)array(
        'Routing' => (object)array(
          'request' => (object)array(
            'session' => 'session'
          )
        ),
        'View' => 'view'
      ));
    $m = $this->getMockForAbstractClass('Jivoo\Core\Module', array($app));
    $this->assertAttributeEquals('session', 'session', $m);
    $this->assertAttributeEquals('view', 'view', $m);
    
    $app->expects($this->once())
      ->method('p')
      ->willReturn('ptest');
    $this->assertEquals('ptest', $m->p('a', 'b'));
    $this->assertEmpty($m->getEvents());
    $this->assertFalse($m->hasEvent('someEvent'));
    $l = $this->getMockBuilder('Jivoo\Core\IEventListener')
      ->setMethods(array('getEventHandlers', 'someEvent'))
      ->getMock();
    $l->method('getEventHandlers')
      ->wilLReturn(array('someEvent'));
    $l->expects($this->once())
      ->method('someEvent')
      ->willReturn(false);
    $m->attachEventListener($l);
    $e = new Event($this);
    $this->assertfalse($m->triggerEvent('someEvent', $e));
    $m->detachEventListener($l);
    $this->assertTrue($m->triggerEvent('someEvent', $e));
    $c = function() { return false; };
    $m->attachEventHandler('someEvent', $c);
    $this->assertfalse($m->triggerEvent('someEvent', $e));
    $m->detachEventHandler('someEvent', $c);
    $this->assertTrue($m->triggerEvent('someEvent', $e));
  }
  
  public function testMagicMethods() {
    $app = $this->getMockBuilder('Jivoo\Core\App')
      ->disableOriginalConstructor()
      ->getMock();
    $app->method('getModules')
      ->wilLReturn(array());
    $m = new A($app);
    try {
      $m->invalidProp;
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
    try {
      $m->invalidProp =  true;
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
    try {
      unset($m->invalidProp);
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
    try {
      isset($m->invalidProp);
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
    try {
      $m->invalidMethod();
      $this->fail('InvalidMethodException not thrown');
    }
    catch (InvalidMethodException $e) {}
  }
  
  public function testInheritElements() {
    $app = $this->getMockBuilder('Jivoo\Core\App')
      ->disableOriginalConstructor()
      ->getMock();
    $app->expects($this->once())
      ->method('getModules')
      ->wilLReturn(array());
    $b = new B($app);
    $this->assertAttributeCount(4, 'modules', $b);
    $this->assertAttributeContains('A', 'modules', $b);
    $this->assertAttributeContains('B', 'modules', $b);
    $this->assertAttributeContains('C', 'modules', $b);
    $this->assertAttributeContains('D', 'modules', $b);
  }
}

class A extends Module {
  protected $modules = array('A', 'B');
  public function __construct(App $app) {
    parent::__construct($app);
    $this->inheritElements('modules');
  }
}
class B extends A {
  protected $modules = array('C', 'D');
}
