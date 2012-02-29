<?php
/**
 * @brief Blog comment data model
 */

class Comment extends BaseModel {
  protected $id;
  protected $content;
  protected $date;
  protected $state;
  
  private $updated;
  /* PROPERTIES BEGIN */
  
  /**
   * Array of readable property names
   * @var array
   */
  protected $_getters = array(
      'id', 'content', 'date', 'state',
  );
  /**
   * Array of writable property names
   * @var array
   */
  protected $_setters = array(
      'content', 'date', 'state',
  );
  /* PROPERTIES END */
  
  public static function getById($id) {
    
  }
  
  public static function select(Selector $selector = NULL) {
    
  }
  
  public function commit() {
    if (!$this->updated)
      return;
    echo 'Updating database';
  }
  
  public function delete() {
    
  }

}
