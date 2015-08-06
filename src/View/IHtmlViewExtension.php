<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\View;

/**
 * A view extension that produces HTML.
 */
interface IHtmlViewExtension extends IViewExtension {
  /**
   * Output extension.
   * @return string HTML.
   */
  public function html();
}