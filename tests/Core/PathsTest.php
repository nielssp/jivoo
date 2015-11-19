<?php

namespace Jivoo\Core;

class PathsTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}

  public function testGettersAndSetters() {
    $paths = new Paths('/');
    $this->assertEquals('/test', $paths->test);
    $this->assertEquals('/', $paths['']);
    $this->assertEquals('/', $paths['/']);
    $this->assertFalse(isset($paths['test']));
    $paths->test = 'a/subdir';
    $this->assertTrue(isset($paths['test']));
    $this->assertEquals('/a/subdir', $paths->test);
    $paths['test'] = '/a/subdir';
    $this->assertEquals('/a/subdir', $paths->test);
    unset($paths['test']);
    $this->assertFalse(isset($paths['test']));
    
    $paths = new Paths('');
    $this->assertEquals('test', $paths->test);
    $this->assertEquals('', $paths->__get(''));
    $this->assertEquals('', $paths->__get('/'));
    $paths->test = 'a/subdir';
    $this->assertEquals('a/subdir', $paths->test);

    $paths = new Paths('the/base/path');
    $this->assertEquals('the/base/path/test', $paths->test);
    $paths->test = '/a/subdir';
    $this->assertEquals('/a/subdir', $paths->test);

    $paths = new Paths('the/base/path', '/another/path');
    $this->assertEquals('/another/path/test', $paths->test);
    $paths->test = 'a/subdir';
    $this->assertEquals('the/base/path/a/subdir', $paths->test);
    $paths->test = '/a/subdir';
    $this->assertEquals('/a/subdir', $paths->test);
  }
  
  public function testP() {
    $paths = new Paths('the/base/path');
    $this->assertEquals('the/base/path', $paths->p(''));
    $this->assertEquals('the/base/path', $paths->p('.'));
    $this->assertEquals('the/base/path/test', $paths->p('test'));
    $this->assertEquals('the/base/path/test/test', $paths->p('test/test'));
    $this->assertEquals('the/base/path/test', $paths->p('/test'));
    $paths->test = 'a/subdir';
    $this->assertEquals('the/base/path/a/subdir', $paths->p('test'));
    $this->assertEquals('the/base/path/a/subdir/test', $paths->p('test/test'));
    $this->assertEquals('the/base/path/test', $paths->p('/test'));
    $paths->test = '/a/subdir';
    $this->assertEquals('/a/subdir', $paths->p('test'));
    $this->assertEquals('/a/subdir/test', $paths->p('test/test'));
  }
  
  public function testCombinePaths() {
    $this->assertEquals('', Paths::combinePaths('', ''));
    $this->assertEquals('', Paths::combinePaths('', '/'));
    $this->assertEquals('/', Paths::combinePaths('/', ''));
    $this->assertEquals('foo/bar', Paths::combinePaths('foo', 'bar'));
  }
  
  public function testConverPath() {
    $this->assertEquals('C:/windows/system32', Paths::convertPath('C:\windows\system32'));
  }
  
  public function testIsAbsolutePath() {
    $this->assertTrue(Paths::isAbsolutePath('C:\windows\system32'));
    $this->assertTrue(Paths::isAbsolutePath('http://google.com'));
    $this->assertTrue(Paths::isAbsolutePath('/usr/share'));
    $this->assertTrue(Paths::isAbsolutePath('/'));
    $this->assertFalse(Paths::isAbsolutePath(''));
    $this->assertFalse(Paths::isAbsolutePath('./test'));
    $this->assertFalse(Paths::isAbsolutePath('foo/bar'));
    $this->assertFalse(Paths::isAbsolutePath('../foo'));
  }
}
