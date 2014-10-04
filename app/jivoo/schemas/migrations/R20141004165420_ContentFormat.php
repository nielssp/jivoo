<?php
class R20141004165420_ContentFormat extends Migration {

  public function addContentFormat($table) {
    $this->addColumn($table, 'contentHtml', DataType::text(true));
    $this->addColumn($table, 'contentFormat', DataType::string(255, false, 'html'));
    $this->$table->set('contentHtml = content')->update();
    $this->alterColumn($table, 'contentHtml', DataType::text());
  }

  public function up() {
    $this->addContentFormat('Post');
    $this->addContentFormat('Comment');
    $this->addContentFormat('Page');
  }
  public function down() {
    $this->deleteColumn('Post', 'contentHtml');
    $this->deleteColumn('Post', 'contentFormat');
    $this->deleteColumn('Comment', 'contentHtml');
    $this->deleteColumn('Comment', 'contentFormat');
    $this->deleteColumn('Page', 'contentHtml');
    $this->deleteColumn('Page', 'contentFormat');
  }
}
