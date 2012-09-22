<?php
class SessionTest extends PHPUnit_Framework_TestCase {

  public function testArrayAccess() {
    $session = new Session();
    $_SESSION = array();
    $this->assertFalse($session->offsetExists('test'));
    $_SESSION['test'] = 2;
    $this->assertTrue($session->offsetExists('test'));
    $this->assertTrue(isset($session['test']));
    $this->assertEquals(2, $session->offsetGet('test'));
    $this->assertEquals(2, $session['test']);
    $session['test'] = 3;
    $session->offsetSet('test2', 'foo');
    $this->assertEquals(3, $session['test']);
    $this->assertEquals('foo', $_SESSION['test2']);
    unset($session['test']);
    $this->assertNull($session['test']);

    $before = $_SESSION;
    $session->offsetSet(null, 2);
    $session[] = 3;
    $this->assertEquals($before, $_SESSION);
  }
}
