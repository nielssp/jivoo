<?php


class Tag extends BaseModel {

  /**
  * This is neccesary for inherited addToCache() method to work properly.
  * @var array
  */
  public static $cache = array();

  protected $id;
  protected $name;
  protected $tag;

  protected $_getters = array('id', 'name', 'tag');

  public static function getById($id) {
    global $PEANUT;
    if (isset(self::$cache[$id])) {
      return self::$cache[$id];
    }
    $obj = new self();
    $row = $PEANUT['flatfiles']->getRow('tags', $id);
    if ($row == FALSE) {
      throw new TagNotFoundException(tr('A tag with id "%1" was not found.', $id));
    }
    $obj->id = $id;
    foreach ($row as $column => $value) {
      $obj->$column = $value;
    }
    return $obj;
  }

  public static function getByName($name) {
    global $PEANUT;
    $id = $PEANUT['flatfiles']->indexFind('tags', 'name', $name);
    if ($id == FALSE) {
      throw new TagNotFoundException(tr('A tag with name "%1" was not found.', $name));
    }
    return self::getById($id);
  }

  public static function create($tag) {
    global $PEANUT;
    $name = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-)]/', '', $tag)));
    try {
      return self::getByName($name);
    }
    catch (TagNotFoundException $exception) {
      $new = new Tag();
      $id = $PEANUT['flatfiles']->incrementId('tags');
      $PEANUT['flatfiles']->insertRow('tags', $id, array('name' => $name, 'tag' => $tag));
      $new->id = $id;
      $new->name = $name;
      $new->tag = $tag;
      return $new;
//      $PEANUT['flatfiles']->addRelation('tags', 'posts', $tagId, $id);
    }
  }

  public static function select(Selector $selector = null) {
    $selectHelper = new SelectHelper(get_class(), 'tags');
    $selectHelper->defaultSelector->orderBy('name')->asc();
    return $selectHelper->select($selector);
  }

  public function commit() {

  }

  public function delete() {

  }
}


class TagNotFoundException extends Exception { }