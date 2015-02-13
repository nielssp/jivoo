<?php
/**
 * A delete selection.
 */
interface IDeleteSelection extends IBasicSelection {
  /**
   * Delete record in selection.
   * @return int Number of deleted records.
   */
  public function delete();
}