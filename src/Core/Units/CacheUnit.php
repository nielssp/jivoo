<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\Cache\Cache;
use Jivoo\Core\Store\SerializedStore;
use Jivoo\Core\Cache\StorePool;
use Jivoo\Core\Cache\NullPool;

/**
 * Initializes the cache system.
 */
class CacheUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->cache = new Cache();
    
    if ($app->paths->dirExists('cache')) {
      $app->m->cache->setDefaultProvider(function($pool) use($app) {
        $store = new SerializedStore($app->p('cache/' . $pool . '.s'));
        if ($store->touch())
          return new StorePool($store);
        return new NullPool();
      });
    }
  }
}