<?php
class R20141005223700_RenameDate extends Migration {
  public function up() {
    $this->renameColumn('Post', 'createdAt', 'created');
    $this->renameColumn('Post', 'updatedAt', 'updated');
    $this->renameColumn('Comment', 'createdAt', 'created');
    $this->renameColumn('Comment', 'updatedAt', 'updated');
    $this->renameColumn('Page', 'createdAt', 'created');
    $this->renameColumn('Page', 'updatedAt', 'updated');
    $this->renameColumn('User', 'createdAt', 'created');
    $this->renameColumn('User', 'updatedAt', 'updated');
    $this->deleteIndex('Post', 'createdAt');
    $this->createIndex('Post', 'created', array('unique' => false, 'columns' => array('created')));
    $this->deleteIndex('Page', 'createdAt');
    $this->createIndex('Page', 'created', array('unique' => false, 'columns' => array('created')));
  }
  public function down() {
    $this->renameColumn('Post', 'created', 'createdAt');
    $this->renameColumn('Post', 'updated', 'updatedAt');
    $this->renameColumn('Comment', 'created', 'createdAt');
    $this->renameColumn('Comment', 'updated', 'updatedAt');
    $this->renameColumn('Page', 'created', 'createdAt');
    $this->renameColumn('Page', 'updated', 'updatedAt');
    $this->renameColumn('User', 'created', 'createdAt');
    $this->renameColumn('User', 'updated', 'updatedAt');
    $this->deleteIndex('Post', 'created');
    $this->createIndex('Post', 'createdAt', array('unique' => false, 'columns' => array('createdAt')));
    $this->deleteIndex('Page', 'created');
    $this->createIndex('Page', 'createdAt', array('unique' => false, 'columns' => array('createdAt')));
  }
}
