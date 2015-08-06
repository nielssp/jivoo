<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\ISchema;

/**
 * A database schema.
 */
interface IDatabaseSchema {
  /**
   * Get table names.
   * @return string[] List of table names.
   */
  public function getTables();
  
  /**
   * Get schema for table.
   * @param string $table Table name.
   * @return ISchema Table schema.
   */
  public function getSchema($table);
  
  /**
   * Add table schema.
   * @param ISchema $schema Table schema;
   */
  public function addSchema(ISchema $schema);
}