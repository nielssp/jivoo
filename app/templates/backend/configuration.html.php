<?php
// Render the header
$this->renderTemplate('backend/header.html');
?>


      <div class="section">
        <div class="container">
          <h1>Configuration</h1>
        </div>
      </div>
      <div class="section light_section">
        <div class="container">
          <h2>General</h2>
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

<?php
$this->renderTemplate('backend/footer.html');
?>

