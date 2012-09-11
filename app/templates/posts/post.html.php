<?php
$classes = '';
if ($first) {
  $classes .= ' first';
}
if ($post->status != 'published') {
  $classes .= ' yellow';
}
?>
          <div class="record<?php echo $classes; ?>" id="record-<?php echo $post->id; ?>">
            <div class="checkbox">
              <input type="checkbox" />
            </div>
            <div class="header">
          <span class="title block30 margin5">
<?php echo $Html->link(h($post->title), array('action' => 'edit', 'parameters' => array($post->id))); ?>
          </span>
          <span class="state block15 margin5">
            <?php echo ucfirst($post->status); ?>
          </span>
          <span class="date block15 margin5">
            <?php echo $post->formatDate(); ?>
          </span>
          <div class="actions">
             <ul class="menubutton">
               <li class="first">
<?php
if ($post->status == 'published') {
  echo $Html->link(
    tr('Conceal'),
    array('action' => 'edit', 'parameters' => array($post->id)),
    array('class' => 'conceal-action')
  );
}
else {
  echo $Html->link(
    tr('Publish'),
    array('action' => 'edit', 'parameters' => array($post->id)),
    array('class' => 'publish-action')
  );
}
?>
               </li>
               <li>
                 <?php echo $Html->link('Edit', array('action' => 'edit', 'parameters' => array($post->id))); ?>
               </li>
               <li>
                 <?php echo $Html->link('View', array('action' => 'view', 'parameters' => array($post->id))); ?>
               </li>
               <li class="last red"><?php
                 echo $Html->link('Delete',
                   array('action' => 'delete', 'parameters' => array($post->id)),
                   array('class' => 'delete-action')
                 ); 
                 ?></li>
             </ul>
           </div>
         </div>
         <div class="clearl"></div>
         <div class="body">
           <div class="content block100">
              <?php echo $post->encode(
                'content',
                array('stripAll' => true, 'maxLength' => 250, 'append' => '[...]')
              ); ?>
              </div>
          </div>
              <div class="clearl"></div>
            </div>