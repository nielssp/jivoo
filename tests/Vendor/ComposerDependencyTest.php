<?php
namespace Jivoo;

use Jivoo\Vendor\ComposerDependency;

class ComposerDependencyTest extends TestCase {
  public function testLessThan() {
    $d1 = new ComposerDependency('pkg', '<1.2.3');
    $this->assertTrue($d1->checkVersion('1.2.2'));
    $this->assertTrue($d1->checkVersion('1.2'));
    $this->assertTrue($d1->checkVersion('0.1'));
    $this->assertTrue($d1->checkVersion('1.2.3-beta'));
    $this->assertFalse($d1->checkVersion('1.2.3'));
    $this->assertFalse($d1->checkVersion('1.3'));
    $this->assertFalse($d1->checkVersion('1.2.4'));
  }
}