<?php
// Render the header
$this->renderTemplate('backend/header.html');
?>


      <div class="section">
        <div class="container">
          <p>
            <label class="small">Title</label>
            <input type="text" class="text bigtext" id="post_title" />
          </p>
          <p>
            <label class="small">Content</label>
            <textarea id="text" class="wysiwyg" cols="50" rows="15" ></textarea>
          </p>
          <p>
            <label class="small">Tags</label>
            <input type="text" class="text" id="post_tags" />
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
                  /PeanutCMS/index.php/2012/01/<input type="text" id="post_permalink" name="permalink"
                  data-title-id="post_title" class="text permalink permalink-allow-slash" value="" />
                </div>
              </div>
              <div class="clearl"></div>
            </div>
            <div class="separator"></div>
            <div class="input">
              <p class="label">Allow Comments</p>
              <div class="element">
                <div class="radioset">
                  <input type="radio" id="allow_comments_yes" checked="checked" name="allow_comments"/>
                  <label for="allow_comments_yes">Yes</label>
                  <input type="radio" id="allow_comments_no" name="allow_comments"/>
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
            <input type="submit" class="button" value="Save draft" />
            <input type="submit" class="button publish" value="Publish" />
          </div>
        </div>
      </div>

<?php
$this->renderTemplate('backend/footer.html');
?>

