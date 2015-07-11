<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

/**
 * Mixin for adding meta data stored in a separate model to records.
 *
 * The settings are:
 * <code>
 * array(
 *   'model' => // the meta data model, e.g. 'UserMeta'
 *   'thisKey' => // the foreign key in the above model, e.g. 'userId'
 *   'otherKey' => // the primary key of the current model, e.g. 'id'
 * )
 * </code>
 */
class MetaMixin extends ActiveModelMixin {
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'model' => null,
    'thisKey' => null,
    'otherKey' => null
  );
}
