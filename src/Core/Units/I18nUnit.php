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
  protected $requires = array('Cache');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    I18n::setCache($app->m->cache->i18n);
    
    if (isset($app->config['i18n']['language']))
      I18n::setLanguage($app->config['i18n']['language']);
    I18n::loadFrom($this->p('Core/languages'));
    I18n::loadFrom($this->p('app/languages'));
  }
}