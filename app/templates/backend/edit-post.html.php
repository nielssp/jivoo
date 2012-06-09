<?php
// Render the header
$this->renderTemplate('backend/header.html');
?>

<div class="section">
  <div class="container notification notification-error">
    <strong>Error</strong>
    The force is not with you today.
  </div>
</div>
      
    <form action="<?php echo $action; ?>" method="post">

      <div class="section">
        <div class="container">
          <p>
            <label class="small">Title</label>
            <input type="text" name="title" class="text bigtext"
              id="post_title" value="<?php echo $values['title']; ?>" />
          </p>
          <p>
            <label class="small">Content</label>
            <textarea id="text" name="content" class="wysiwyg" cols="50" rows="15"><?php echo $values['content']; ?></textarea>
          </p>
          <p>
            <label class="small">Tags</label>
            <input type="text" name="tags" class="text"
              id="post_tags" value="<?php echo $values['tags']; ?>" />
            <span class="description">Comma-separated list of tags</span>
          </p>
        </div>
      </div>
      
      <div id="settings">
        <div class="top_shadow"></div>
        <div class="section dark_section">
          <div class="container">
            <div class="input">
              <p class="label"><label for="post_permalink">Permalink</label></p>
              <div class="element">
                <div class="permalink-wrapper">
                  <?php echo $beforePermalink;
                        if ($nameInPermalink): ?><input type="text" id="post_permalink" name="permalink"
                      data-title-id="post_title" class="text permalink permalink-allow-slash"
                      value="<?php echo $values['permalink']; ?>" /><?php endif;
                        echo $afterPermalink; ?>
                </div>
              </div>
              <div class="clearl"></div>
            </div>
            <div class="separator"></div>
            <div class="input">
              <p class="label">Allow Comments</p>
              <div class="element">
                <div class="radioset">
                  <input type="radio" id="allow_comments_yes" value="yes"
                    <?php echo $values['allow_comments'] ? 'checked="checked"' : ''; ?>
                    name="allow_comments"/>
                  <label for="allow_comments_yes">Yes</label>
                  <input type="radio" id="allow_comments_no" value="no"
                    <?php echo !$values['allow_comments'] ? 'checked="checked"' : ''; ?>
                    name="allow_comments"/>
                  <label for="allow_comments_no">No</label>
                </div>
              </div>
              <div class="clearl"></div>
            </div>
          </div>
        </div>
        <div class="bottom_shadow"></div>
      </div>
      
      <div class="section">
        <div class="container">
          <div class="left">
            <input type="checkbox" class="button" id="check_settings" /> <label for="check_settings">Settings</label>
          </div>
          <div class="aright">
            <input type="submit" name="save" class="button" value="Save draft" />
            <input type="submit" name="publish" class="button publish" value="Publish" />
          </div>
        </div>
      </div>
    </form>

<?php
$this->renderTemplate('backend/footer.html');
?>

