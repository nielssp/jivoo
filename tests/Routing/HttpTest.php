<?php

namespace Jivoo\Routing;

class HttpTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}

  public function testEncodeQuery() {
    $this->assertEquals('', Http::encodeQuery(array()));
    $this->assertEquals('', Http::encodeQuery(array(''), false));
    $this->assertEquals('', Http::encodeQuery(array('' => '')));
    $this->assertEquals('', Http::encodeQuery(array('' => 'foo')));
    $this->assertEquals('foo=bar', Http::encodeQuery(array('foo' => 'bar')));
    $this->assertEquals('foo', Http::encodeQuery(array('foo' => '')));
    $this->assertEquals('0=foo', Http::encodeQuery(array('foo')));
    $this->assertEquals('0=foo&1=bar', Http::encodeQuery(array('foo', 'bar')));
    $this->assertEquals('foo=bar&0=baz', Http::encodeQuery(array('foo' => 'bar', 'baz')));

    
    $this->assertEquals('', Http::encodeQuery(array(), false));
    $this->assertEquals('', Http::encodeQuery(array(''), false));
    $this->assertEquals('', Http::encodeQuery(array('', ''), false));
    $this->assertEquals('bar', Http::encodeQuery(array('foo' => 'bar'), false));
    $this->assertEquals('foo', Http::encodeQuery(array('foo'), false));
    $this->assertEquals('foo&bar', Http::encodeQuery(array('foo', 'bar'), false));
    $this->assertEquals('bar&baz', Http::encodeQuery(array('foo' => 'bar', 'baz'), false));
    

    $this->assertEquals('foo+bar=baz%26%3Dfoobar', Http::encodeQuery(array('foo bar' => 'baz&=foobar')));
  }
  
  public function testDecodeQuery() {
    $this->assertEquals(array(), Http::decodeQuery(''));
    $this->assertEquals(array(), Http::decodeQuery('?'));
    $this->assertEquals(array(), Http::decodeQuery('?&'));
    $this->assertEquals(array(), Http::decodeQuery('?&&&'));
    $this->assertEquals(array('??????' => ''), Http::decodeQuery('???????'));
    
    $this->assertEquals(array('foo' => ''), Http::decodeQuery('foo'));
    $this->assertEquals(array('foo' => ''), Http::decodeQuery('foo='));
    $this->assertEquals(array('foo' => ''), Http::decodeQuery('foo=&'));
    $this->assertEquals(array('foo' => 'bar'), Http::decodeQuery('foo=bar'));
    $this->assertEquals(array('foo' => 'bar', 'baz' => 'foobar'), Http::decodeQuery('foo=bar&baz=foobar'));
    $this->assertEquals(array('foo' => 'bar', 'foobar'), Http::decodeQuery('foo=bar&0=foobar'));
//     $this->assertEquals(array('foo' => array('bar', 'foobar')), Http::decodeQuery('foo[]=bar&foo[]=foobar'));
    $this->assertEquals(array('bar'), Http::decodeQuery('0=bar'));
    

    $this->assertEquals(array(), Http::decodeQuery('', false));
    $this->assertEquals(array(), Http::decodeQuery('&', false));
    $this->assertEquals(array('foo'), Http::decodeQuery('foo', false));
    $this->assertEquals(array('bar'), Http::decodeQuery('foo=bar', false));
    $this->assertEquals(array('bar', 'foobar'), Http::decodeQuery('foo=bar&baz=foobar', false));
    $this->assertEquals(array('bar', 'foobar', 'baz'), Http::decodeQuery('foo=bar&0=foobar&baz', false));
    $this->assertEquals(array('bar'), Http::decodeQuery('0=bar', false));
    

    $this->assertEquals(array('foo bar' => 'baz&=foobar'), Http::decodeQuery('foo+bar=baz%26%3Dfoobar'));
  }
}
