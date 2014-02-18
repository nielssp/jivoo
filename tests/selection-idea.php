<?php

interface IActiveSelecteion extends Iterator, ArrayAccess, ICondition {
  public function select($column);
  public function set($column, $value);
  public function delete();
  public function update();
  public function count();
}

interface IActiveRecord {
  public function addData($data, $allowedFields = null);
  public function set($field, $value);
  public function save();
  public function delete();
}

interface IActiveCollection extends IActiveSelection {
  public function add(IActiveRecord $record);
  public function has(IActiveRecord $record);
  public function remove(IActiveRecord $record);
}

class Selection  implements Iterator { //, ICondition {
  
   
  
//   public function join();
  
  // with selection: (actions)
//   public function delete();
//   public function update();
  
  // Iterator implementation:
  private $position = 0;
  
  private $array = array(
    "firstelement",
    "secondelement",
    "lastelement",
  );
  
  public function __construct() {
    $this->position = 0;
  }
  
  public function orderBy($column) {
    var_dump(__METHOD__);
    return $this;
  }
  
  function rewind() {
    var_dump(__METHOD__);
    $this->position = 0;
  }
  
  function current() {
    var_dump(__METHOD__);
    return $this->array[$this->position];
  }
  
  function key() {
    var_dump(__METHOD__);
    return $this->position;
  }
  
  function next() {
    var_dump(__METHOD__);
    ++$this->position;
  }
  
  function valid() {
    var_dump(__METHOD__);
    return isset($this->array[$this->position]);
  }
}

class ActiveModel implements IDataSource {
  
}


// Actual table-name: Users
class Users extends ActiveModel {
  
}

header('Content-Type: text/plain');

// DataSources should also be selections

$db->posts->where('id = 5')->set('title', 'Hello, World')->update();
// or:
$db->posts->where('id = 5')[0]->set('title', 'Hello, World')->save();
// or:
$post = $db->posts->where('id = 5')[0];
$post->title = 'Hello, World';
$post->save();

// post hasMany comments
$post->comments; // Returns ActiveCollection or something
$post->comments[] = $comment; // ArrayAccess might be stupid: ..comments[1] = $comment (doesn't make sense)
$post->comments->add($comment);
$post->comments->has($comment);
$post->comments->remove($comment);
$post->comments->count();

$post->category = $category; // Same as:
$post->categoryId = $category->id;
// May do some lazy evaluation so that:
$post->category == $category;
// is just as fast as
$post->categoryId == $category->id;
// not actually possible though. An alternative:
$post->category->equals($category);
// ActiveRecords should implement some sort of lazy evaluation

// Define aliases so that the following is possible:
$posts->where('id > 5')->and(where('name = foo')->or('name = bar'));

$selection->groupBy('something')->select('COUNT(something)');

$selection->set('name', 'test')->update();

$posts = new Selection();

foreach ($posts->orderBy('name') as $post) {} // performs query