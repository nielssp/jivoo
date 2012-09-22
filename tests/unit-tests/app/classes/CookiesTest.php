<?php
class CookiesTest extends PHPUnit_Framework_TestCase {
  public function testArrayAccess() {
    $data = array('test_foo' => '23', 'test_bar' => 42);
    $cookies = new Cookies($data, 'test_');

    $this->assertFalse($cookies->offsetExists('test_foo'));
    $this->assertTrue(isset($cookies['foo']));

    $this->assertEquals(23, $cookies['foo']);
    $this->assertEquals(42, $cookies->offsetGet('bar'));

    $cookies['foobar'] = 'baz';
    $this->assertTrue(isset($cookies['foobar']));
    $this->assertEquals('baz', $cookies['foobar']);

    unset($cookies['bar']);
    $this->assertFalse(isset($cookies['bar']));
    $this->assertTrue(isset($cookies['foobar']));

    $cookies[] = 23;
  }
}
