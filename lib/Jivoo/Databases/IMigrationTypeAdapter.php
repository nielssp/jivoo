<?php
interface IMigrationTypeAdapter extends IMigratable, ITypeAdapter {
  public function getTableSchema($table);
}
