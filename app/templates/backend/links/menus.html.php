<?php
$this->title = tr('Menu');
$this->extend('backend/layout.html');
?>


<div class="section light_section">
<div class="container">

<?php foreach ($links as $link): ?>

<p><?php echo $Html->link($link->title, $link); ?> : 
[<?php echo $link->type; ?>]
:
<?php echo $link->path; ?>
</p>

<?php endforeach; ?>

</div>
</div>