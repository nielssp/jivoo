<?php
class RequestTest extends PHPUnit_Framework_TestCase {

  public function testQuery() {
    $_GET = array('test' => 2, 'a' => 'hello, world');
    $request = new Request();
    $this->assertEquals(2, $request->query['test']);
    $this->assertEquals('hello, world', $request->query['a']);
    $this->assertNull($request->query['test2']);

    $request->query = array('test' => 'a', 'form' => array('hello' => 23));
    $this->assertEquals(23, $request->query['form']['hello']);

    $request->unsetQuery('form');
    $this->assertNull($this->query['form']);

    $request->unsetQuery();
    $this->assertEmpty($this->query);
  }

  public function testGet() {
    $request = new Request();
    $this->assertNull($request->notAProperty);
  }

  public function testPathAndFragment() {
    $_SERVER['REQUEST_URI'] = 'http://example.com' . WEBPATH . 'hello/world?a=2&b=2#testFragment';
    $request = new Request();
    $this->assertEquals(array('hello', 'world'), $request->path);
    $this->assertEquals(array('hello', 'world'), $request->realPath);
    $this->assertEquals('testFragment', $request->fragment);

    $request->fragment = 'monkey';
    $request->path = array('another', 'path');

    $this->assertEquals(array('another', 'path'), $request->path);
    $this->assertEquals(array('hello', 'world'), $request->realPath);
    $this->assertEquals('monkey', $request->fragment);
  }

  public function testIpUrlAndReferer() {
    $request = new Request();
    $this->assertNull($request->ip);
    $this->assertNull($request->url);
    $this->assertNull($request->referer);

    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $this->assertEquals('127.0.0.1', $request->ip);

    $_SERVER['REQUEST_URI'] = 'http://example.com';
    $this->assertEquals('http://example.com', $request->url);

    $_SERVER['HTTP_REFERER'] = 'http://example.net';
    $this->assertEquals('http://example.net', $request->referer);
  }

  public function testToken() {
    $_SESSION = array();
    $request = new Request();
    $this->assertFalse($request->checkToken());
    $_POST = array('access_token' => $request->getToken());
    $request = new Request();
    $this->assertTrue($request->checkToken());
  }

  public function testIs() {
    $request = new Request();

    $this->assertFalse($request->isGet());
    $this->assertFalse($request->isPost());
    $this->assertFalse($request->isAjax());

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $this->assertTrue($request->isGet());
    $this->assertFalse($request->isPost());
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $this->assertFalse($request->isGet());
    $this->assertTrue($request->isPost());
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $this->assertTrue($request->isAjax());
  }

}
