<?php
/**
 * Automatically generated schema for comments table
 */
class commentsSchema extends Schema {
  public $id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'primary',
    'autoIncrement' => true,
    'null' => false,
  );

  public $post_id = array(
    'type' => 'integer',
    'length' => 10,
    'key' => 'index',
    'null' => false,
  );

  public $user_id = array(
    'type' => 'integer',
    'length' => 10,
    'default' => '0',
    'null' => false,
  );

  public $parent_id = array(
    'type' => 'integer',
    'length' => 10,
    'default' => '0',
    'null' => false,
  );

  public $author = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $email = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $website = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );
  
  public $ip = array(
    'type' => 'string',
    'length' => 255,
    'null' => false,
  );

  public $content = array(
    'type' => 'text',
    'null' => false,
  );
  
  public $content_text = array(
      'type' => 'text',
      'null' => false,
  );

  public $date = array(
    'type' => 'integer',
    'length' => 10,
    'null' => false,
  );

  public $status = array(
    'type' => 'string',
    'length' => 50,
    'null' => false,
  );

  public $indexes = array(
    'PRIMARY' => array(
      'columns' => array('id'),
      'unique' => true
    ),
    'post_id' => array(
      'columns' => array('post_id'),
      'unique' => false
    ),
  );
  
  public function addColumn_content_text(IDatabase $db) {
    $db->addColumn('comments', 'content_text', $this->content_text);
    $rows = $db->comments->select()->execute();
    $encoder = new Encoder();
    while ($row = $rows->fetchAssoc()) {
      $contentText = $encoder->encode($row['content']);
      $db->comments->update()->where('id = ?', $row['id'])
        ->set('content_text', $contentText)->execute();
    }
  }
}
