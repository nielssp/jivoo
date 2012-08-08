<?php
// Render the header
$this->render('backend/header.html');
?>


      <div class="section light_section">
        <div class="container">
          <div class="input">
            <p class="label"><label for="post_permalink">Search</label></p>
            <div class="element">
              <input type="text" class="text" />
            </div>
            <div class="clearl"></div>
          </div>
        </div>
      </div>
      <div class="section light_section">
        <div class="container">
<?php foreach ($posts as $post): ?>
          <div class="record">
            <strong><?php echo h($post->title); ?></strong><br/>
            <?php echo $post->encode(
              'content',
              array('stripAll' => TRUE, 'maxLength' => 200, 'append' => '...')
            ); ?>
            <div class="clearl"></div>
          </div>
          <div class="separator"></div>
<?php endforeach; ?>
        </div>
      </div>

<?php
$this->render('backend/footer.html');
?>

