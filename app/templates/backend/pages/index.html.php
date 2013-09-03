<?php
$this->extend('backend/index-layout.html');
?>

<?php if (count($pages) < 1) : ?>
          <div class="center">
          <?php echo tr('No pages matched your search criteria.') ?>
          </div>
<?php endif; ?>
<?php $first = true; ?>
<?php foreach ($pages as $page) : ?>

          <div class="record<?php if ($first) {
    echo ' first';
    $first = false;
  } ?>">
            <div class="checkbox">
              <input type="checkbox" />
            </div>
            <div class="header">
          <span class="title block30 margin5">
<?php echo $Html->link(h($page->title),
      array('action' => 'edit', 'parameters' => array($page->id))); ?>
          </span>
          <span class="state block10 margin5">
            <?php echo ucfirst($page->state); ?>
          </span>
          <div class="actions">
           <ul class="menubutton">
             <li class="first">
               <?php echo $Html->link('Edit',
      array('action' => 'edit', 'parameters' => array($page->id))); ?>
             </li>
             <li>
               <?php echo $Html->link('View',
      array('action' => 'view', 'parameters' => array($page->id))); ?>
             </li>
             <li class="last red">
               <?php echo $Html->link('Delete',
      array('action' => 'delete', 'parameters' => array($page->id))); ?>
             </li>
           </ul>
          </div>
        </div>
        <div class="clearl"></div>
        <div class="body">
          <div class="content block100">
            <?php echo $page->encode('content',
      array('stripAll' => true, 'maxLength' => 250, 'append' => '[...]'));
            ?>
          </div>
        </div>
            <div class="clearl"></div>
          </div>
<?php endforeach; ?>

