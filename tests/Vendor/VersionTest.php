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
}
