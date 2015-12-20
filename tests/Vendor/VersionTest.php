<?php
namespace Jivoo\Vendor;

use Jivoo\TestCase;
use Jivoo\Core\Parse\ParseInput;

class VersionTest extends TestCase {
  public function testParseWhitespace() {
    $input = new ParseInput(array(' ', "\t", ' '));
    Version::parseWhitespace($input);
    $this->assertNull($input->peek());
  }

  public function testParseOperator() {
    $input = new ParseInput(array('<', '=', '<', '>', '>', '=', '!', '=', '~', '^'));
    $this->assertEquals('<=', Version::parseOperator($input));
    $this->assertEquals('<', Version::parseOperator($input));
    $this->assertEquals('>', Version::parseOperator($input));
    $this->assertEquals('>=', Version::parseOperator($input));
    $this->assertEquals('!=', Version::parseOperator($input));
    $this->assertEquals('~', Version::parseOperator($input));
    $this->assertEquals('^', Version::parseOperator($input));
    $this->assertNull(Version::parseOperator($input));

    $this->assertThrows('Jivoo\Core\Parse\ParseException', function() {
      $input = new ParseInput(array('!'));
      Version::parseOperator($input);
    });
  }

  public function testParseInt() {
    $input = new ParseInput(array('1', '2', '3'));
    $this->assertEquals('123', Version::parseInt($input));

    $input = new ParseInput(array('a', '2', '3'));
    $this->assertNull(Version::parseInt($input));
  }

  public function testParseNonInt() {
    $input = new ParseInput(array('1', '2', '3'));
    $this->assertNull(Version::parseNonInt($input));

    $input = new ParseInput(array('a', '2', '3'));
    $this->assertEquals('a', Version::parseNonInt($input));

    $input = new ParseInput(array('f', 'o', 'o'));
    $this->assertEquals('foo', Version::parseNonInt($input));

    $input = new ParseInput(array('a', '.', '3'));
    $this->assertEquals('a', Version::parseNonInt($input));
  }

  public function testParseVersionPart() {
    $input = new ParseInput(array('1', '2', 'a', 'b'));
    $this->assertEquals('12', Version::parseVersionPart($input));
    $this->assertEquals('ab', Version::parseVersionPart($input));
    $this->assertNull(Version::parseVersionPart($input));
  }

  public function testParseExact() {
    $input = new ParseInput(array());
    $this->assertEquals('', Version::parseExact($input));

    $input = new ParseInput(str_split(' 1.12-beta a-b-c 25beta.1'));
    $this->assertEquals('1.12.beta', Version::parseExact($input));
    $this->assertEquals('a.b.c', Version::parseExact($input));
    $this->assertEquals('25.beta.1', Version::parseExact($input));
  }

  public function testParseWildcard() {
    $input = new ParseInput(str_split('1.0.0'));
    $this->assertTrue(Version::parseWildcard($input, '1.0.0'));
    $input->reset();
    $this->assertFalse(Version::parseWildcard($input, '1.0'));
    $input->reset();
    $this->assertFalse(Version::parseWildcard($input, '1.0.1'));

    $input = new ParseInput(str_split('1.*'));
    $this->assertTrue(Version::parseWildcard($input, '1.0'));
    $input->reset();
    $this->assertTrue(Version::parseWildcard($input, '1.5.1'));
    $input->reset();
    $this->assertFalse(Version::parseWildcard($input, '2.0'));
    $input->reset();
    $this->assertFalse(Version::parseWildcard($input, '2.0.1'));
  }
}
