<?php

namespace Jivoo\Core\I18n;

use Jivoo\InvalidPropertyException;

class LocaleTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}

  public function testSetAndGet() {
    $l = new Locale();
    $this->assertEquals('Hello, World!', $l->get('Hello, World!'));
    $l->set('Hello, World!', 'Hej, Verden!');
    $this->assertEquals('Hej, Verden!', $l->get('Hello, World!'));
    $this->assertEquals('Hej, Verden!', $l->get('Hello, World!', 1));
    
    $this->assertEquals('Hello, World!', $l->get('Hello, %1!', 'World'));
    $l->set('Hello, %1!', 'Hej, %1!');
    $this->assertEquals('Hej, World!', $l->get('Hello, %1!', 'World'));
  }
  
  public function testNget() {
    $l = new Locale();
    $this->assertEquals('There are 5 users', $l->nget('There are %1 users', 'There is %1 user', 5));
    $this->assertEquals('There are 0 users', $l->nget('There are %1 users', 'There is %1 user', 0));
    $this->assertEquals('There is 1 user', $l->nget('There are %1 users', 'There is %1 user', 1));
    $this->assertEquals('There is -1 user', $l->nget('There are %1 users', 'There is %1 user', -1));
    
    $l->set('There are %1 users', array('Der er %1 bruger', 'Der er %1 brugere'));
    $this->assertEquals('Der er 5 brugere', $l->nget('There are %1 users', 'There is %1 user', 5));
    $this->assertEquals('Der er 0 brugere', $l->nget('There are %1 users', 'There is %1 user', 0));
    $this->assertEquals('Der er 1 bruger', $l->nget('There are %1 users', 'There is %1 user', 1));
//     $this->assertEquals('Der er -1 bruger', $l->getn('There are %1 users', 'There is %1 user', -1));

    // from https://www.gnu.org/software/gettext/manual/html_node/Translating-plural-forms.html

    $l->pluralForms = 'nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 &&'
                    . ' n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;';
    
    $l->set('%1 files removed', array(
      '%1 slika je uklonjena',
      '%1 datoteke uklonjenih',
      '%1 slika uklonjenih'
    ));

    $this->assertEquals(
      '1 slika je uklonjena',
      $l->nget('%1 files removed', 'One file removed', 1)
    );
    $this->assertEquals(
      '21 slika je uklonjena',
      $l->nget('%1 files removed', 'One file removed', 21)
    );
    $this->assertEquals(
      '2 datoteke uklonjenih',
      $l->nget('%1 files removed', 'One file removed', 2)
    );
    $this->assertEquals(
      '25 slika uklonjenih',
      $l->nget('%1 files removed', 'One file removed', 25)
    );
    $this->assertEquals(
      '11 slika uklonjenih',
      $l->nget('%1 files removed', 'One file removed', 11)
    );
    $this->assertEquals(
      '12 slika uklonjenih',
      $l->nget('%1 files removed', 'One file removed', 12)
    );
  }
  
  public function testReplacePlaceholders() {
    $l = new Locale();
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
    $l1 = new Locale();
    $l1->set('Hello, World!', 'Hej, Verden!');
    $l1->set('Hello, %1!', 'Hej, %1!');
    $l1->set('There are %1 users', array('Der er %1 bruger', 'Der er %1 brugere'));
    
    $l2 = new Locale();
    $l2->set('Create user', 'Opret bruger');
    $l2->set('Hello, %1!', 'Halløj, %1!');
    
    $l2->extend($l1);
    
    $this->assertEquals('Hej, Verden!', $l2->get('Hello, World!'));
    $this->assertEquals('Hej, World!', $l2->get('Hello, %1!', 'World'));
    $this->assertEquals('Der er 0 brugere', $l2->nget('There are %1 users', 'There is %1 user', 0));
    $this->assertEquals('Der er 1 bruger', $l2->nget('There are %1 users', 'There is %1 user', 1));
    $this->assertEquals('Der er 2 brugere', $l2->nget('There are %1 users', 'There is %1 user', 2));
  }
  
  public function testMagicGettersAndSetters() {
    $l = new Locale();
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
    catch (InvalidPropertyException $e) {}
    try {
      $l->notAProperty;
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
    try {
      isset($l->notAProperty);
      $this->fail('InvalidPropertyException not thrown');
    }
    catch (InvalidPropertyException $e) {}
  }
}
