<?php
/**
 * @brief Blog post data model
 */

class Post {
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
  /* Properties end */

  public function __construct($title, $content, $state = 'unpuplished', $name = null, $tags = array(), $commenting = null) {
    global $PEANUT;
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
    $this->id = $id;
    $this->name = $name;
    $this->title = $title;
    $this->date = $date;
    $this->state = $state;
    $this->commenting = $commenting;
    $this->comments = 0;
    $this->content = $content;
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
  }

  public function __destruct() {
    return true;
  }
  
  public function commit() {
    if (!$this->updated)
      return;
    echo 'Updating database';
  }

}
