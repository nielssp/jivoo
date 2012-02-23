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

class Selector {
  private $orderBy;
  private $descending;
  private $limit;
  private $where;
  private $offset;
  
  /* Properties begin */
  private $_getters = array('orderBy', 'descending', 'limit', 'where', 'offset');
  private $_setters = array();
  
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
  
  private function _get_ascending() {
    return !$this->descending;
  }
  /* Properties end */
  
  private function __construct() {
    $this->limit = -1;
    $this->offset = 0;
    $this->where = array();
    $this->orderBy = 'id';
    $this->descending = false;
  }
  
  public static function create() {
    return new self();
  }
  
  public function limit($limit) {
    $this->limit = $limit;
    return $this;
  }
  
  public function offset($offset) {
    $this->offset = $offset;
    return $this;
  }
  
  public function where($column, $value) {
    $this->where[$column] = $value;
    return $this;
  }
  
  public function orderBy($column) {
    $this->orderBy = $column;
    return $this;
  }
  
  public function desc() {
    $this->descending = true;
    return $this;
  }
  
  public function asc() {
    $this->descending = false;
    return $this;
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
  
  private function _get_path() {
    global $PEANUT;
    $permalink = $PEANUT['configuration']->get('postPermalink');
    if (is_array($permalink)) {
      $time = $this->date;
      $replace = array('%name%' => $this->name,
                       '%id%' => $this->id,
                       '%year%' => $PEANUT['i18n']->date('Y', $time),
                       '%month%' => $PEANUT['i18n']->date('m', $time),
                       '%day%' => $PEANUT['i18n']->date('d', $time));
      $search = array_keys($replace);
      $replace = array_values($replace);
      $path = array();
      foreach ($permalink as $dir) {
        $path[] = str_replace($search, $replace, $dir);
      }
      return $path;
    }
  }
  
  private function _get_link() {
    global $PEANUT;
    return $PEANUT['http']->getLink($this->path);
  }
  /* Properties end */

  private function __construct() {
    
  }
  
  public function formatTime($format = null) {
  global $PEANUT;
  if (!isset($format))
  $format = $PEANUT['i18n']->timeFormat();
  return $PEANUT['i18n']->date($format, $this->date);
  }
  
  public function formatDate($format = null) {
  global $PEANUT;
  if (!isset($format))
  $format = $PEANUT['i18n']->dateFormat();
  return $PEANUT['i18n']->date($format, $this->date);
  }
  
  
  
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
  public static function create($title, $content, $state = 'unpublished', $name = null, $tags = array(), $commenting = null) {
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
    $obj = new self();
    $row = $PEANUT['flatfiles']->getRow('posts', $id);
    $obj->id = $id;
    foreach ($row as $column => $value) {
      $obj->$column = $value;
    }
    return $obj;
  }
  
  public static function getByName($name) {
    global $PEANUT;
    $id = $PEANUT['flatfiles']->indexFind('posts', 'name', $name);
    return self::getById($id);
  }
  
  public static function all($selector = null) {
    global $PEANUT;
    if (!isset($selector) OR !is_a($selector, 'Selector'))
      $selector = Selector::create()->orderBy('date')->desc();
    $index = $PEANUT['flatfiles']->getIndex('posts', $selector->orderBy);
    if ($selector->descending)
      arsort($index);
    else
      asort($index);
    reset($index);
    $all = array();
    $i = 0;
    foreach ($index as $id => $date) {
      if ($i < $selector->offset) {
        $i++;
        continue;
      }
      if ($selector->limit != -1 AND ($i - $selector->offset) >= $selector->limit)
        break;
      $get = self::getById($id);
      $add = true;
      foreach ($selector->where as $column => $value) {
        if ($get->$column != $value) {
          $add = false;
          break;
        }
      }
      if ($add) {
        $all[] = $get;
        $i++;
      }
    }
    reset($all);
    return $all;
  }
  /*
   * e.g.:
   * 
   * $posts = Post::all(
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
      return;
    echo 'Updating database';
  }

}
