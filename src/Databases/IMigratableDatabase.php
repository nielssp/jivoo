<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

/**
 * A database implementing migration methods.
 */
interface IMigratableDatabase extends IDatabase, IMigratable {
  /**
   * Refresh schemas, i.e. update database schema to match actual database
   * schema.
   */
  public function refreshSchema();
  
  /**
   * Change schema of database.
   * @param IDatabaseSchema $schema New database schema.
   */
  public function setSchema(IDatabaseSchema $schema);
}