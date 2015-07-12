<?php
ini_set('display_errors', true);

require '../lib/Jivoo/bootstrap.php';

Lib::import('Core');

$app = new App(require '../app/app.php', basename(__FILE__));

$routing = $app->loadModule('Jivoo/Routing');
$helpers = $app->loadModule('Jivoo/Helpers');

// header('Content-Type: text/plain');

$routing->addRoute('GET search', 'App::search');

$Form = new FormHelper($routing);

?>

<pre><?php var_dump($_GET); ?></pre>

<?php echo $Form->form('App::search', array('method' => 'get', 'id' => 'search')); ?>
  <?php echo $Form->label('q', 'Search for'); ?>
  <?php echo $Form->text('q', array('type' => 'search')); ?>
  <?php echo $Form->date('createdAt'); ?>
  <?php echo $Form->time('createdAt'); ?>
  <?php echo $Form->radioLabel('what', 'yes', 'Yes'); ?>
  <?php echo $Form->radio('what', 'yes'); ?>
  <?php echo $Form->radioLabel('what', 'no', 'No'); ?>
  <?php echo $Form->radio('what', 'no'); ?>
  <?php echo $Form->select('s'); ?>
    <?php echo $Form->optgroup('My group')?>
      <?php echo $Form->option('val', 'Val')?>
    <?php echo $Form->end(); ?>
  <?php echo $Form->end(); ?>
  <?php echo $Form->selectOf('s2', array('val' => 'Val')); ?>
  
  <?php echo $Form->submit(tr('Search')); ?>
<?php echo $Form->end(); ?>
