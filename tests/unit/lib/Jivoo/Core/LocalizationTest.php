<?php

namespace Jivoo\Core;

class LocalizationTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}

  public function testSetAndGet() {
    $l = new Localization();
    $this->assertEquals('Hello, World!', $l->get('Hello, World!'));
    $l->set('Hello, World!', 'Hej, Verden!');
    $this->assertEquals('Hej, Verden!', $l->get('Hello, World!'));
    $this->assertEquals('Hej, Verden!', $l->get('Hello, World!', 1));
    
    $this->assertEquals('Hello, World!', $l->get('Hello, %1!', 'World'));
    $l->set('Hello, %1!', 'Hej, %1!');
    $this->assertEquals('Hej, World!', $l->get('Hello, %1!', 'World'));
    
    $l->set('Hello %1 world!', 'Hej fem verdener!', '/^5$/');
    $l->set('Hello %1 world!', 'Hej %1 verden!', '/^1$/');
    $l->set('Hello %1 world!', 'Hej %1 verdener!', '/^[0-9]+$/');
    $this->assertEquals('Hej 1 verden!', $l->get('Hello %1 world!', 1));
    $this->assertEquals('Hej fem verdener!', $l->get('Hello %1 world!', 5));
    $this->assertEquals('Hej 4 verdener!', $l->get('Hello %1 world!', 4));
    $this->assertEquals('Hello NaN world!', $l->get('Hello %1 world!', 'NaN'));
  }
  
  public function testGetNumeric() {
    $l = new Localization();
    $this->assertEquals('There are 5 users', $l->getNumeric('There are %1 users', 'There is %1 user', 5));
    $this->assertEquals('There are 0 users', $l->getNumeric('There are %1 users', 'There is %1 user', 0));
    $this->assertEquals('There is 1 user', $l->getNumeric('There are %1 users', 'There is %1 user', 1));
    $this->assertEquals('There is -1 user', $l->getNumeric('There are %1 users', 'There is %1 user', -1));
    
    $l->set('There are %1 users', 'Der er %1 bruger', '/^-?1$/');
    $l->set('There are %1 users', 'Der er %1 brugere');
    $this->assertEquals('Der er 5 brugere', $l->getNumeric('There are %1 users', 'There is %1 user', 5));
    $this->assertEquals('Der er 0 brugere', $l->getNumeric('There are %1 users', 'There is %1 user', 0));
    $this->assertEquals('Der er 1 bruger', $l->getNumeric('There are %1 users', 'There is %1 user', 1));
    $this->assertEquals('Der er -1 bruger', $l->getNumeric('There are %1 users', 'There is %1 user', -1));
  }
  
  public function testReplacePlaceholders() {
    $l = new Localization();
    $this->assertEquals('test', $l->replacePlaceholders('test', array(1)));
    $this->assertEquals('1', $l->replacePlaceholders('%1', array(1)));
    $this->assertEquals('123', $l->replacePlaceholders('%1%2%3', array(1, 2, 3)));
    $this->assertEquals('321', $l->replacePlaceholders('%3%2%1', array(1, 2, 3)));
    
    $this->assertEquals(
      'The user user01 is online',
      $l->replacePlaceholders('The user %1{, }{ and } is online', array(array('user01')))
    );
    $this->assertEquals(
      'The users user01 and user02 are online',
      $l->replacePlaceholders('The users %1{, }{ and } are online', array(array('user01', 'user02')))
    );
    $this->assertEquals(
      'The users user01, user02 and user03 are online',
      $l->replacePlaceholders('The users %1{, }{ and } are online', array(array('user01', 'user02', 'user03')))
    );
  }
  
  public function testExtend() {
    $l1 = new Localization();
    $l1->set('Hello, World!', 'Hej, Verden!');
    $l1->set('Hello, %1!', 'Hej, %1!');
    $l1->set('There are %1 users', 'Der er %1 bruger', '/^-?1$/');
    $l1->set('There are %1 users', 'Der er %1 brugere');
    
    $l2 = new Localization();
    $l2->set('Create user', 'Opret bruger');
    $l2->set('Hello, %1!', 'Halløj, %1!');
    $l2->set('There are %1 users', 'Der er ingen brugere', '/^0$/');
    
    $l2->extend($l1);
    
    $this->assertEquals('Hej, Verden!', $l2->get('Hello, World!'));
    $this->assertEquals('Hej, World!', $l2->get('Hello, %1!', 'World'));
    $this->assertEquals('Der er 0 brugere', $l2->get('There are %1 users', 0));
    $this->assertEquals('Der er 1 bruger', $l2->get('There are %1 users', 1));
    $this->assertEquals('Der er 2 brugere', $l2->get('There are %1 users', 2));
  }
  
  public function testMagicGettersAndSetters() {
    $l = new Localization();
    $l->dateFormat = 'Ymd';
    $this->assertEquals('Ymd', $l->dateFormat);
    
    $l->longFormat = '%DATE';
    $this->assertEquals('Ymd', $l->longFormat);
    
    unset($l->longFormat);
    $this->assertFalse(isset($l->longFormat));
    
    try {
      $l->notAProperty = true;
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (\InvalidPropertyException $e) {}
    try {
      $l->notAProperty;
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (\InvalidPropertyException $e) {}
    try {
      isset($l->notAProperty);
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (\InvalidPropertyException $e) {}
  }
}
