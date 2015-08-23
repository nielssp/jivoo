<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\I18n\I18n;

/**
 * Initializes the internationalization and localization system.
 */
class I18nUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    if (isset($confog['language']))
      I18n::setLanguage($config['language']);
    I18n::loadFrom($this->p('Core/languages'));
    I18n::loadFrom($this->p('app/languages'));
  }
}