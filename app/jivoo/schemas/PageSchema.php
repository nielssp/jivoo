<?php
/**
 * @package PeanutCMS\Schemas
 */
class PageSchema extends Schema {
  
  const REVISION = 1;
  
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->content = DataType::text();
    $this->contentText = DataType::text();
    $this->published = DataType::boolean(false, true);
    $this->addTimeStamps();
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
  
  // Add column contentText
  protected function up1(MigratableDatabase $db) {
    $db->addColumn('Page', 'contentText', DataType::text(true));
    foreach ($db->getTable('Page', $this) as $page) {
      $page->contentText = strip_tags($page->content);
      $page->save();
    }
    $db->alterColumn('Page', 'contentText', DataType::text());
  }
  protected function down1(MigratableDatabase $db) {
    $db->deleteColumn('Page', 'contentText');
  }
}
