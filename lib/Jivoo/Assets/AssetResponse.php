<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Assets;

use Jivoo\Core\Utilities;
use Jivoo\Routing\Response;
use Jivoo\Routing\Http;

/**
 * Responds with the content of an asset.
 */
class AssetResponse extends Response {
  /**
   * @var string File path.
   */
  private $file;

  /**
   * Construct asset response.
   * @param string $file Path to asset.
   */
  public function __construct($file) {
    parent::__construct(Http::OK, Utilities::getContentType($file));
    $this->file = $file;
    $this->modified = filemtime($file);
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return file_get_contents($this->file);
  }

}
