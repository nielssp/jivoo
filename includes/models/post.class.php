<?php
/**
 * @brief Blog post data model
 */



interface Selectable {
  
  public static function getById($id);
  
  public static function select(Selector $selector = NULL);

}

abstract class BaseModel implements Selectable {
  
  public abstract function commit();

  public abstract function delete();

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

  private function __construct() {
    
  }  

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
  
  public static function select(Selector $selector = null) {
    global $PEANUT;
    if (!isset($selector)) {
      $selector = Selector::create()->orderBy('date')->desc();
    }
    $index = $PEANUT['flatfiles']->getIndex('posts', $selector->orderBy);
    if ($index === FALSE) {
      throw new Exception("Can't order by '" . $selector->orderBy . "' since no index exists for that column.");
    }
    if ($selector->descending) {
      arsort($index);
    }
    else {
      asort($index);
    }
    reset($index);
    $all = array();
    $i = 0;
    foreach ($index as $id => $date) {
      if ($i < $selector->offset) {
        $i++;
        continue;
      }
      if ($selector->limit != -1
          AND ($i - $selector->offset) >= $selector->limit) {
        break;
      }
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
  
  public function formatTime($format = null) {
    global $PEANUT;
    if (!isset($format)) {
      $format = $PEANUT['i18n']->timeFormat();
    }
    return $PEANUT['i18n']->date($format, $this->date);
  }
  
  public function formatDate($format = null) {
    global $PEANUT;
    if (!isset($format)) {
      $format = $PEANUT['i18n']->dateFormat();
    }
    return $PEANUT['i18n']->date($format, $this->date);
  }
  
  public function commit() {
    if (!$this->updated) {
      return;
    }
    echo 'Updating database';
  }
  
  public function delete() {
    echo 'Deletig?';
  }
  
  /* PROPERTIES BEGIN { */
  
  /**
   * Array of readable property names
   * @var array
   */
  private $_getters = array(
  	'id', 'name', 'title', 'content',
  	'date', 'state', 'comments', 'commenting',
  );
  /**
   * Array of writable property names
   * @var array
   */
  private $_setters = array(
  	'name', 'title', 'content', 'date',
  	'state', 'commenting',
  );
  
  /**
   * Magic getter method
   *
   * @param string $property Property name
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property)) {
      return call_user_func(array($this, '_get_' . $property));
    }
    else if (in_array($property, $this->_setters)
             OR method_exists($this, '_set_' . $property)) {
      throw new PropertyWriteOnlyException(
      	tr('Property "%1" is write-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      ); 
    }
  }
  
  /**
   * Magic setter method
   *
   * @param string $property Property name
   * @param string $value New property value
   * @throws Exception
   */
  public function __set($property, $value) {
    $this->updated = true;
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property)) {
      call_user_func(array($this, '_set_' . $property), $value);
    }
    else if (in_array($property, $this->_getters)
             OR method_exists($this, '_get_' . $property)) {
      throw new PropertyReadOnlyException(
      	tr('Property "%1" is read-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
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
  /* PROPERTIES END */

}
