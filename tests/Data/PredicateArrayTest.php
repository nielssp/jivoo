<?php

namespace Jivoo\Data;

class PredicateArayTest extends \Jivoo\TestCase {

  public function testExists() {
    $a = new PredicateArray(array(1, 2, 3, 4, 5), function($x) { return $x > 3; });
    $this->assertFalse(isset($a[0]));
    $this->assertFalse(isset($a[1]));
    $this->assertFalse(isset($a[2]));
    $this->assertTrue(isset($a[3]));
    $this->assertTrue(isset($a[4]));
    $this->assertFalse(isset($a[5]));
    $this->assertTrue(isset($a[4]));
  }
  
}
