<?php
class RequestTest extends PHPUnit_Framework_TestCase {

  private $request;

  public function setUp() {
    $this->request = new Request();
  }

  public function tearDown() {
  }

  public function testGet() {
    print_r($this->request->query);
  }
}
