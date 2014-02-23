<?php
/**
 * Automatically generated schema for posts_tags table
 * @package PeanutCMS\Schemas
 */
class posts_tagsSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('post_id', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->addInteger('tag_id', Schema::NOT_NULL | Schema::UNSIGNED);
    $this->setPrimaryKey('post_id', 'tag_id');
  }
}
