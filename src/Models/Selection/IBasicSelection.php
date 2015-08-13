<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

use Jivoo\Models\Condition\ICondition;

/**
 * The most basic selection.
 */
interface IBasicSelection extends ICondition {
  /**
   * Order selection by a column or expression.
   * @param string|null $expression Expression or column. If null all ordering
   * will be removed from selection.
   * @return IBasicSelection A selection.
   */
  public function orderBy($expression);

  /**
   * Order selection by a column or expression, in descending order.
   * @param string $expression Expression/column
   * @return IBasicSelection A selection.
  */
  public function orderByDescending($expression);
  
  /**
   * Reverse the ordering.
   * @return IBasicSelection A selection.
   */
  public function reverseOrder();

  /**
   * Limit number of records.
   * @param int Number of records.
   * @return IBasicSelection A selection.
  */
  public function limit($limit);
}