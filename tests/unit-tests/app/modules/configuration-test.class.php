<?php
class ConfigurationTest extends PHPUnit_Framework_TestCase {

  protected $configuration;

  public function setUp() {
    $core = new Core();
    $this->configuration = $core->loadModule('configuration');
  }

  public function tearDown() {
    unset($this->configuration);
  }

  public function testSetAndGet() {
    $this->configuration->set('test.testValue', '123456');

    $value = $this->configuration->get('test.testValue');

    $this->assertEquals('123456', $value);
  }
}
