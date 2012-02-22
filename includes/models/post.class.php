<?php
/**
 * @brief Blog post data model
 */

class BaseModel {
  public static function create() {
    
  }
  
  public static function getById() {
    
  }
  
  public static function all() {
    
  }
}

class Post extends BaseModel {
  private $id;
  private $name;
  private $title;
  private $content;
  private $date;
  private $state;
  private $comments;
  private $commenting;
    
  private $updated;
  
  /* Properties begin */
  private $_getters = array('id', 'name', 'title', 'content', 'date', 'state', 'comments', 'commenting');
  private $_setters = array('name', 'title', 'content', 'date', 'state', 'commenting');
  
  /**
   * Magic method
   * @param string $property
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property))
      return call_user_func(array($this, '_get_' . $property));
    else if (in_array($property, $this->_setters) OR method_exists($this, '_set_' . $property))
      throw new Exception('Property "' . $property . '" is write-only.');
    else
      throw new Exception('Property "' . $property . '" is not accessible.');
  }
  
  public function __set($property, $value) {
    $this->updated = true;
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property))
      call_user_func(array($this, '_set_' . $property), $value);
    else if (in_array($property, $this->_getters) OR method_exists($this, '_get_' . $property))
      throw new Exception('Property "' . $property . '" is read-only.');
    else
      throw new Exception('Property "' . $property . '" is not accessible.');
  }
  
  private function _get_link() {
    return 'http://example.com';
  }
  /* Properties end */
  
  
  
  /*
   * MAYBE:
   * 
   * To create post:
   * $post = Post::create('title', 'content, ...)
   * instead of
   * $post = new Post('title', 'content', ...)
   * 
   * e.g. make __construct private
   * 
   * then:
   * $post = Post::getById(23)
   * could also use the constructor without creating a new post
   */

  private function __construct() {
    
  }
  
  public static function create($title, $content, $state = 'unpuplished', $name = null, $tags = array(), $commenting = null) {
    global $PEANUT;
    $new = new Post();
    $date = time();
    $id = $PEANUT['flatfiles']->incrementId('posts');
    if ($id === false)
      return false;
    if (!isset($name)) // Remove all non-alphanumeric characters, replace whitespaces with dashes and convert to lowercase
      $name = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-)]/', '', $title)));
    if ($PEANUT['configuration']->get('commentingDefault') == 'on' AND (!isset($commenting) OR $commenting == 'off')
        OR (isset($commenting) AND $commenting == 'on'))
      $commenting = 'on';
    else
      $commenting = 'off';
    $new->id = $id;
    $new->name = $name;
    $new->title = $title;
    $new->date = $date;
    $new->state = $state;
    $new->commenting = $commenting;
    $new->comments = 0;
    $new->content = $content;
    $post = array(
        		'name' => $name,
                'title' => $title,
        		'date' => $date,
        		'state' => $state,
                'commenting' => $commenting,
        		'comments' => 0,
                'content' => $content
    );
    $PEANUT['flatfiles']->insertRow('posts', $id, $post);
    foreach ($tags as $tag) {
      $tagName = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-)]/', '', $tag)));
      $tagId = $PEANUT['flatfiles']->indexFind('tags', 'name', $tagName);
      if ($PEANUT['flatfiles']->getRow('tags', $tagId) !== false) {
        $PEANUT['flatfiles']->addRelation('tags', 'posts', $tagId, $id);
      }
      else {
        $tagId = $PEANUT['flatfiles']->incrementId('tags');
        $PEANUT['flatfiles']->insertRow('tags', $tagId, array('name' => $tagName, 'tag' => $tag));
        $PEANUT['flatfiles']->addRelation('tags', 'posts', $tagId, $id);
      }
    }
    return $new;
  }
  
  public static function getById($id) {
    global $PEANUT;
    $post = new Post();
    $row = $PEANUT['flatfiles']->getRow('posts', $id);
    $post->id = $id;
    foreach ($row as $column => $value) {
      $post->$column = $value;
    }
    return $post;
  }
  
  public static function getByName($name) {
    
  }
  
  public static function all() {
    global $PEANUT;
    $index = $PEANUT['flatfiles']->getIndex('posts', 'date');
    arsort($index);
    reset($index);
    $all = array();
    foreach ($index as $id => $date) {
      $all[] = self::getById($id);
    }
    reset($all);
    return $all;
  }
  
  /*
   * e.g.:
   * 
   * $posts = Post::getAll(array('limit' => 10, 'order' => 'asc', 'by' => 'date'));
   * 
   * $posts = Post::all()->limit(10)->order('asc')
   */

  public function __destruct() {
    return true;
  }
  
  public function commit() {
    if (!$this->updated)
      return $this;
    echo 'Updating database';
  }
  
  public function dies() {
    echo "derp?";
  } 

}
