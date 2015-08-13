<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

/**
 * A view extension.
 */
interface IViewExtension {
  /**
   * Prepare extension.
   * @return bool Whether or not the extension should be displayed.
   */
  public function prepare();
}