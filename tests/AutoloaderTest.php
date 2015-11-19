<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo;

class AutoloaderTest extends TestCase {
  public function testSingleton() {
    $instance = Autoloader::getInstance();
    $this->assertSame($instance, Autoloader::getInstance());
  }
  
  public function testRegister() {
    $autoloader = $this->getMockBuilder('Jivoo\Autoloader')
      ->setMethods(array('load'))
      ->getMock();
    $autoloader->expects($this->once())
      ->method('load')
      ->with($this->equalTo('Jivoo\Foo\Bar'))
      ->willReturn(false);;
    $this->assertFalse(class_exists('\Jivoo\Foo\Bar'));
    $autoloader->register();
    $this->assertFalse(class_exists('\Jivoo\Foo\Bar'));
    $autoloader->unregister();
    $this->assertFalse(class_exists('\Jivoo\Foo\Bar'));
  }
  
  public function testLoad() {
    $autoloader = new Autoloader();
    $autoloader->addPath('Foo', 'src/Models');
    $autoloader->addPath('Foo', 'src/Helpers');
    $autoloader->addPath('Foo\Selection', 'src/Models/Selection');
    $this->assertFalse($autoloader->load('Bar'));
    $this->assertFalse($autoloader->load('Foo\Bar'));
    $this->assertTrue($autoloader->load('Foo\Helper'));
    $this->assertTrue($autoloader->load('Foo\Model'));
    $this->assertTrue($autoloader->load('Foo\Selection\SelectionBuilder'));
    
    // TODO: mock loadFrom and test paths etc.
  }
}
