<?php
interface IMigratableDatabase extends IDatabase, IMigratable {
  public function refreshSchema();
  
  public function setSchema(IDatabaseSchema $schema);
}