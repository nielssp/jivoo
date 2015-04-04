<?php
namespace Jivoo\Core;


class LocalizationTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSetAndGet()
    {
      $l = new Localization();
      $this->assertEquals('Hello, World!', $l->get('Hello, World!'));
      $l->set('Hello, World!', 'Hej, Verden!');
      $this->assertEquals('Hej, Verden!', $l->get('Hello, World!'));
      
      $this->assertEquals('Hello, World!', $l->get('Hello, %1!', 'World'));
      $l->set('Hello, %1!', 'Hej, %1!');
      $this->assertEquals('Hej, World!', $l->get('Hello, %1!', 'World'));
    }

}
