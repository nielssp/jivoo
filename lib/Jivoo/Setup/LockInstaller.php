<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

class LockInstaller extends InstallerSnippet {
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('configure');
  }

  public function check($data) {
    return $this->render();
  }

  public function configure($data) {
    return $this->render();
  }
}