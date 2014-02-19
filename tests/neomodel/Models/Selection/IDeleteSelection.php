<?php
interface IDeleteSelection extends IBasicSelection {
  /**
   * @return int Number of deleted records
   */
  public function delete();
}