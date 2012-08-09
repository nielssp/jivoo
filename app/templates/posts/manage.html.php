<?php
// Render the header
$this->render('backend/header.html');
?>


      <div class="section light_section">
        <div class="container pagination">
          <span class="older block15">
<?php if (!$Pagination->isLast()) echo $Html->link('&#8592; Older', $Pagination->nextLink()); ?>
          &nbsp;</span>
          <span class="pages block15">
            Page <?php echo $Pagination->getPage(); ?>
            of <?php echo $Pagination->getPages(); ?>
          </span>
          <form action="<?php echo $this->link(array()); ?>" method="get">
          <span class="filter block30">
            <input type="text" class="text" name="filter" value="<?php echo h($filter); ?>" />
          </span>
          </form>
          <span class="newer">&nbsp;
<?php if (!$Pagination->isFirst()) echo $Html->link('Newer &#8594;', $Pagination->prevLink()); ?>
          </span>
          <div class="clearl"></div>
        </div>
      </div>
      <div class="section light_section">
        <div class="container">
<?php if (count($posts) < 1): ?>
          <div class="center">
          <?php echo tr('No posts matched your search criteria.')?>
          </div>
<?php endif; ?>
<?php $first = TRUE; ?>
<?php foreach ($posts as $post): ?>

          <div class="record<?php if ($first) { echo ' first'; $first = FALSE; } ?>">
          <span class="title block30 margin5">
<?php echo $Html->link(h($post->title), array('action' => 'edit', 'parameters' => array($post->id))); ?>
          </span>
          <span class="state block15 margin5">
            <?php echo ucfirst($post->state); ?>
          </span>
          <span class="date block15 margin5">
            <?php echo $post->formatDate(); ?>
          </span>
          <span class="actions">
<?php echo $Html->link('Edit', array('action' => 'edit', 'parameters' => array($post->id))); ?>

<?php echo $Html->link('View', array('action' => 'view', 'parameters' => array($post->id))); ?>

<?php echo $Html->link('Delete', array('action' => 'delete', 'parameters' => array($post->id))); ?>
          </span>
            <div class="clearl"></div>
            <div class="content block100 topspace">
            <?php echo $post->encode(
              'content',
              array('stripAll' => TRUE, 'maxLength' => 250, 'append' => '[...]')
            ); ?>
            </div>
            <div class="clearl"></div>
          </div>
<?php endforeach; ?>
        </div>
      </div>

<?php
$this->render('backend/footer.html');
?>

