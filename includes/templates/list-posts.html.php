<?php
/*
 * Template for blog post listing
 */


$posts = Post::select(
  Selector::create()
    //->where('state', 'unpublished')
    ->orderBy('date')
    ->desc()
    ->limit(3)
    ->offset(0)
);



abstract class Object {
  protected $_getters = array();
  protected $_setters = array();
  
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

  public function __set($property, $value) {
    print_r($this->_setters);
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
}

class Test extends Object {
  protected $_getters = array('test1', 'test2');
  protected $_setters = array('test2', 'test3');

  private $test1;
  private $test2;
  private $test3;
}

$t = new Test();

$t->test2 = "FUCK?";

// Render the header
$this->renderTemplate('header.html');

?>



<p>Blog listing</p>

<?php foreach ($posts as $post): ?>

<h2>
  <a href="<?php echo $post->link; ?>">
    <?php echo $post->title; ?>
  </a>
</h2>

<p>
  Published <?php echo $post->formatDate(); ?> 
  @ <?php echo $post->formatTime(); ?>
</p>

<?php echo $post->content; ?>

<?php endforeach; ?>



<?php
// Render the footer
$this->renderTemplate('footer.html');
?>