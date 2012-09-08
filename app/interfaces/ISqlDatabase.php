<?php

interface ISqlDatabase extends IDatabase {
  public function rawQuery($sql);
}
