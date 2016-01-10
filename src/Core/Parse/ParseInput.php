<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Parse;

/**
 * Input sequence for an LL(k) parser.
 */
class ParseInput {
  /**
   * @var array
   */
  private $input;

  /**
   * @var int
   */
  private $pos = 0;

  /**
   * Construct parser input.
   * @param array $input Input sequence.
   */
  public function __construct(array $input) {
    $this->input = $input;
  }

  /**
   * Reset input pointer.
   */
  public function reset() {
    $this->pos = 0;
  }

  /**
   * Inspect the `$n`'th element in the sequence.
   * @param int $n
   * @return mixed|null The element or null if end of sequence.
   */
  public function peek($n = 0) {
    $n += $this->pos;
    if (isset($this->input[$n]))
      return $this->input[$n];
    return null;
  }

  /**
   * Return the current element and advance the sequence position.
   * @return mxied|null The element or null if end of sequence.
   */
  public function pop() {
    if (isset($this->input[$this->pos]))
      return $this->input[$this->pos++];
    return null;
  }
  
  /**
   * Accept the current element if it is equal to the given element.
   * @param mixed $element Element to accept.
   * @return bool True if accepted, false otherwise.
   */
  public function accept($element) {
    if ($this->peek() !== $element)
      return false;
    $this->pop();
    return true;
  }
  
  /**
   * Like {@see accept}, but throws an exception if the current element does not
   * match. 
   * @param mixed $element Element to accept.
   * @throws ParseException If comparison fails.
   */
  public function expect($element) {
    if (!$this->accept($element))
      throw new ParseException('unexpected "' . $this->peek() . '", expected "' . $element . '"');
  }

  /**
   * Accept the current element if it is a token (as produced by
   * {@see RegexLexer}) of the given type. 
   * @param string $type Token type to accept.
   * @param array $token Output parameter for accepted token.
   * @return bool True if accepted, false otherwise.
   */
  public function acceptToken($type, &$token = null) {
    $token = $this->peek();
    if (!isset($token[0]) or $token[0] != $type)
      return false;
    $this->pop();
    return true;
  }

  
  /**
   * Like {@see accept}, but throws an exception if the current element does not
   * match. 
   * @param string $type Token type to accept.
   * @return array The accepted token.
   * @throws ParseException If comparison fails.
   */
  public function expectToken($type) {
    if (!$this->acceptToken($type, $token))
      throw new ParseException('unexpected "' . $this->peek() . '", expected "' . $type . '"');
    return $token;
  }
}