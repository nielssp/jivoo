<?php
/**
 * Automatically generated schema for posts_tags table
 * @package PeanutCMS
 * @subpackage Schemas
 */
class posts_tagsSchema extends Schema {
  public $post_id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'primary',
    'null' => false,
  );

  public $tag_id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'primary',
    'null' => false,
  );

  public $indexes = array(
    'PRIMARY' => array(
      'columns' => array('post_id', 'tag_id'),
      'unique' => true
    ),
  );
}
