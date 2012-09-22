<?php
class DictionaryTest extends PHPUnit_Framework_TestCase {

  /**
   * @expectedException DictionaryKeyInvalidException
   */
  public function testInvalidKey() {
    $data = array('foo' => 23, 'bar' => 'baz');
    $dictionary = new Dictionary($data, false);
    $this->assertFalse($dictionary->isReadOnly());
    $this->assertEquals(23, $dictionary->foo);
    $dictionary->foobar = 42;
    $this->assertTrue(isset($dictionary->foobar));
    $this->assertEquals(42, $dictionary->foobar);
    $dictionary->bar = 'foo';
    $this->assertEquals('foo', $dictionary->bar);
    unset($dictionary->foobar);
    $this->assertFalse(isset($dictionary->foobar));
    // Expect exception
    $this->assertNull($dictionary->foobar);
  }

  /**
   * @expectedException DictionaryReadOnlyException
   */
  public function testReadOnlyUnset() {
    $data = array('foo' => 23, 'bar' => 'baz');
    $dictionary = new Dictionary($data, true);
    $this->assertTrue($dictionary->isReadOnly());
    $this->assertTrue(isset($dictionary->bar));
    $this->assertEquals(23, $dictionary->foo);
    unset($dictionary->bar);
  }

  /**
   * @expectedException DictionaryReadOnlyException
   */
  public function testReadOnlySet() {
    $data = array('foo' => 23, 'bar' => 'baz');
    $dictionary = new Dictionary($data, true);
    $dictionary->bar = 42;
  }
}
