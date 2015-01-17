<?php
/**
 * A delete selection.
 * @package Jivoo\Models\Condition
 */
interface IDeleteSelection extends IBasicSelection {
  /**
   * Delete record in selection.
   * @return int Number of deleted records.
   */
  public function delete();
}