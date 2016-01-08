<?php
namespace Jivoo\Core\Parse;

use Jivoo\TestCase;

class RegexLexerTest extends TestCase {
  public function testEmpty() {
    $lex = new RegexLexer(true);
    
    $this->assertEmpty($lex(''));
    $this->assertEmpty($lex(" \t\n"));

    $this->assertThrows('Jivoo\Core\Parse\ParseException', function() use($lex) {
      $lex('a');
    });
  }
  
  public function testSkip() {
    $lex = new RegexLexer(true);
    $lex->operator = '[-+]';
    $lex->integer = '[0-9]+';
    
    $tokens = $lex('5-7');
    $this->assertCount(3, $tokens);
    $this->assertEquals('integer', $tokens[0][0]);
    $this->assertEquals('operator', $tokens[1][0]);
    $this->assertEquals('integer', $tokens[2][0]);
    $this->assertEquals('5', $tokens[0][1]);
    $this->assertEquals('-', $tokens[1][1]);
    $this->assertEquals('7', $tokens[2][1]);
    $this->assertEquals(0, $tokens[0][3]);
    $this->assertEquals(1, $tokens[1][3]);
    $this->assertEquals(2, $tokens[2][3]);
    
    $tokens = $lex("   5 \t- \n7");
    $this->assertCount(3, $tokens);
    $this->assertEquals('integer', $tokens[0][0]);
    $this->assertEquals('operator', $tokens[1][0]);
    $this->assertEquals('integer', $tokens[2][0]);
    $this->assertEquals('5', $tokens[0][1]);
    $this->assertEquals('-', $tokens[1][1]);
    $this->assertEquals('7', $tokens[2][1]);
    $this->assertEquals(3, $tokens[0][3]);
    $this->assertEquals(6, $tokens[1][3]);
    $this->assertEquals(9, $tokens[2][3]);
  }
  
  public function testMap() {
    $lex = new RegexLexer(true);
    $lex->operator = '[-+]';
    $lex->integer = '[0-9]+';
    
    $lex->map('integer', function($value, $matches, $offset) {
      return intval($value) + 2;
    });
    
    $tokens = $lex('5-7');
    $this->assertCount(3, $tokens);
    $this->assertEquals('integer', $tokens[0][0]);
    $this->assertEquals('operator', $tokens[1][0]);
    $this->assertEquals('integer', $tokens[2][0]);
    $this->assertEquals(7, $tokens[0][1]);
    $this->assertEquals('-', $tokens[1][1]);
    $this->assertEquals(9, $tokens[2][1]);
    $this->assertEquals(0, $tokens[0][3]);
    $this->assertEquals(1, $tokens[1][3]);
    $this->assertEquals(2, $tokens[2][3]);
  }
}