<?php
/**
 * Automatically generated schema for Posts table
 * @package PeanutCMS\Schemas
 */
class PostSchema extends Schema {
  
  const REVISION = 1;
  
  protected function createSchema() {
    $this->addAutoIncrementId();
    $this->name = DataType::string(255);
    $this->title = DataType::string(255);
    $this->content = DataType::text();
    $this->contentText = DataType::text();
    $this->status = DataType::enum('PostStatus', false, 'published');
    $this->commenting = DataType::boolean(false, false);
    $this->userId = DataType::integer(DataType::UNSIGNED, true);
    $this->addTimestamps();
    $this->addUnique('name', 'name');
    $this->addIndex('createdAt', 'createdAt');
  }
  
  // Add column contentText
  protected function up1(MigratableDatabase $db) {
    $db->addColumn('Post', 'contentText', DataType::text(true));
    foreach ($db->getTable('Post', $this) as $post) {
      $post->contentText = strip_tags($post->content);
      $post->save();
    }
    $db->alterColumn('Post', 'contentText', DataType::text());
  }
  protected function down1(MigratableDatabase $db) {
    $db->deleteColumn('Post', 'contentText');
  }
}
