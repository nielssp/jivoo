<?php
/**
 * Used for setting up application database connections
 */
class AppDatabase extends Database {
  protected function init() {
    // Will connect to 'default'-database using user configuration and attach
    // all table schemas found in the schemas folder
    $this->attachDefault();
  }
}