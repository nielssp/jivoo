<?php

namespace Jivoo\Core;

use Jivoo\InvalidPropertyException;
use Jivoo\InvalidMethodException;

class ModuleTest extends \Jivoo\TestCase {
  
  protected function _before() {}

  protected function _after() {}

  public function testConstruction() {
    $app = $this->getMockBuilder('Jivoo\Core\App')
      ->disableOriginalConstructor()
      ->getMock();
    $mloader = $this->getMockBuilder('Jivoo\Core\ModuleLoader')
      ->disableOriginalConstructor()
      ->getMock();
    $mloader->method('__isset')
      ->willReturn(true);
    $mloader->method('__get')
      ->will($this->returnCallback(function($property) {
        if ($property == 'View')
          return 'view';
        if ($property == 'Routing')
          return 'routing';
      }));
    $app->method('__get')
      ->will($this->returnCallback(function($property) use($mloader) {
        if ($property == 'm')
          return $mloader;
      }));

    $m = new A($app);
    
    $app->expects($this->once())
      ->method('p')
      ->willReturn('ptest');
    $this->assertEquals('ptest', $m->p('a/b'));
    $this->assertEquals($m->getEvents(), array('someEvent'));
    $l = $this->getMockBuilder('Jivoo\Core\EventListener')
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
    $moduleLoader = $this->getMockBuilder('Jivoo\Core\ModuleLoader')
      ->disableOriginalConstructor()->getMock();
    $app->method('__get')
      ->will($this->returnCallback(function($property) use($moduleLoader) {
        if ($property == 'm')
          return $moduleLoader;
      }));;
    $m = new A($app);
    $this->assertThrows('Jivoo\InvalidPropertyException', function() use($m) {
      $m->invalidProp;
    });
    $this->assertThrows('Jivoo\InvalidPropertyException', function() use($m) {
      $m->invalidProp = true;
    });
    $this->assertThrows('Jivoo\InvalidPropertyException', function() use($m) {
      unset($m->invalidProp);
    });
    $this->assertThrows('Jivoo\InvalidMethodException', function() use($m) {
      $m->invalidMethod();
    });
  }
  
  public function testInheritElements() {
    $app = $this->getMockBuilder('Jivoo\Core\App')
      ->disableOriginalConstructor()
      ->getMock();
    $moduleLoader = $this->getMockBuilder('Jivoo\Core\ModuleLoader')
      ->disableOriginalConstructor()->getMock();
    $app->method('__get')
      ->will($this->returnCallback(function($property) use($moduleLoader) {
        if ($property == 'm')
          return $moduleLoader;
      }));;
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
  
  protected $events = array('someEvent');
  public function __construct(App $app) {
    parent::__construct($app);
    $this->inheritElements('modules');
  }
}
class B extends A {
  protected $modules = array('C', 'D');
}
