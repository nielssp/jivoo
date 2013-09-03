<?php $this->extend('backend/layout.html'); ?>
      <div class="section">
        <div class="container">

          <p>
            <?php foreach ($tags as $tag) : ?>

            <?php echo $Html->link($tag->tag, $tag); ?>
            (<?php echo $tag->countPosts(); ?>)

            <?php endforeach; ?>
          </p>

        </div>
      </div>
