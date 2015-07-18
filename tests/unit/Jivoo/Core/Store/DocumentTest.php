<?php

namespace Jivoo\Core\Store;

class DocumentTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}
  
  public function testSetAndGet() {
    $a = new Document();
    $this->assertNull($a->parent);
    $this->assertSame($a, $a->root);
    
    $this->assertFalse(isset($a['a']));
    $a['a'] = 'foo';
    $this->assertEquals('foo', $a['a']);
    $this->assertTrue(isset($a['a']));
    unset($a['a']);
    $this->assertFalse(isset($a['a']));
    $this->assertInstanceOf('Jivoo\Core\Store\Document', $a['a']);

    $a['a'] = null;
    $this->assertFalse(isset($a['a']));
    
    $a[] = 'nope';
    $this->assertNotContains('nope', $a->toArray());
    
    $this->assertInstanceOf('Iterator', $a->getIterator());
  }
  
  public function testDefaults() {
    $a = new Document();
    
    $this->assertEquals('bar', $a->get('a', 'bar'));
    $this->assertEquals('bar', $a->get('a'));
    $a['a'] = 'foo';
    $this->assertEquals('foo', $a->get('a', 'bar'));
    
    $a['b'] = 'bar';
    $a->defaults = array(
      'b' => 'baz',
      'c' => array(
        'd' => 'foobar',
        'e' => 'foobaz'
      )
    );
    $this->assertEquals('bar', $a->get('b', 'foo'));
    $this->assertEquals('foobar', $a['c']['d']);
    
    // types
    $a['a'] = 'foo';
    $this->assertEquals(12, $a->get('a', 12));
    $this->assertEquals(5, $a->get('a', 5)); 
  }
  
  public function testConvert() {
    $a = new Document();
    $a['b'] = 'foo';
    $a['c']['d'] = 'bar';
    $a['c']['e'] = 'baz';
    $a['f']['g']['h'] = 'foobar';
    
    $b = $a->toDocument();
    
    $this->assertEquals($a->toArray(), $b->toArray());
  }
  
  public function testSubsets() {
    $a = new Document();
    $b = $a['b'];
    $c = $b['c'];
    
    $this->assertSame($a, $b->parent);
    $this->assertSame($b, $c->parent);
    $this->assertSame($a, $b->root);
    $this->assertSame($a, $c->root);
    
    $c['d'] = 'foo';
    $this->assertEquals('foo', $a['b']['c']['d']);
    
    $a->override = array(
      'a' => 'bar',
      'b' => array(
        'c' => array(
          'd' => 'baz',
        ),
        'e' => 'foobar'
      )
    );
    $this->assertEquals('baz', $a['b']['c']['d']);
    $this->assertEquals('foobar', $a['b']['e']);
  }
}