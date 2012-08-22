<?php
class ConfigurationTest extends PHPUnit_Framework_TestCase {

  protected $configuration;

  public function setUp() {
    $core = new Core();
    $this->configuration = $core->loadModule('Configuration');
  }

  public function tearDown() {
    unset($this->configuration);
  }

  public function testSetAndGet() {
    $this->configuration->set('test.testValue', '123456');

    $this->assertTrue($this->configuration->exists('test.testValue'));

    $value = $this->configuration->get('test.testValue');

    $this->assertEquals('123456', $value);

    $this->configuration->delete('test.testValue');

    $this->assertFalse($this->configuration->exists('test.testValue'));

    $value = $this->configuration->get('test.testValue');

    $this->assertEquals(false, $value);

  }
}
