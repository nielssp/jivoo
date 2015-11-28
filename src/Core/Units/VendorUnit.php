<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Vendor\ComposerPackageReader;
use Jivoo\Vendor\VendorLoader;

/**
 * Initializes the third-party library loading system.
 */
class VendorUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->vendor = new VendorLoader($app);
    $vendor = $this->p('app/../vendor');
    if (is_dir($vendor))
      $app->m->vendor->addPath($vendor, new ComposerPackageReader());
    $vendor = $this->p('share/vendor');
    if (is_dir($vendor))
      $app->m->vendor->addPath($vendor, new ComposerPackageReader());
    
    $app->m->addProperty('vendor', $app->m->vendor);
  }
}