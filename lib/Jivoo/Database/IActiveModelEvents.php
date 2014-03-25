<?php
interface IActiveModelEvents {
  public function beforeSave(ActiveRecord $record);
  public function afterSave(ActiveRecord $record);
  
  public function beforeValidate(ActiveRecord $record);
  public function afterValidate(ActiveRecord $record);
  
  public function afterCreate(ActiveRecord $record);
  
  public function afterLoad(ActiveRecord $record);
  
  public function beforeDelete(ActiveRecord $record);

  public function install();
}
