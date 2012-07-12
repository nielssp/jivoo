<?php

class Page extends ActiveRecord implements ILinkable {

//   protected $hasMany = array(
//   	'Comment' => array('thisKey' => 'post_id',
//                        'count' => 'comments'),
//   );

  protected $belongsTo = array(
    'User' => array('connection' => 'this',
                    'otherKey' => 'user_id')
  );

//   protected $hasOne = array(
//     'Category' => array('class' => 'Tag')
//   );

  protected $validate = array(
    'title' => array(
      'presence' => true,
      'minLength' => 4,
      'maxLength' => 25,
    ),
    'name' => array(
      'presence' => true,
      'minLength' => 1,
      'maxLength' => 25,
      array(
        'match' => '/^[a-z-\/]+$/',
        'message' => 'Only lowercase letters, numbers, dashes and slashes allowed.'
      ),
    ),
    'content' => array(
      'presence' => true,
    ),
  );

  protected $fields = array(
    'title' => 'Title',
    'name' => 'Permalink',
    'content' => 'Content',
  );
  
  protected $defaults = array(
    'date' => array('time'),
  );

  public function getRoute() {
    return array(
      'controller' => 'Pages',
      'action' => 'view',
      'parameters' => array($this->id)
    );
  }

  public function formatDate() {
    return fdate($this->date);
  }

  public function formatTime() {
    return ftime($this->date);
  }
}
