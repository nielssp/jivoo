<?php
// Render the header
$this->render('backend/header.html');
?>


      <div class="section light_section">
        <div class="container">
          <h2>Filter</h2>
        </div>
      </div>
      <div class="section light_section">
        <div class="container">
          <h2>Site</h2>
          <div class="input">
            <p class="label"><label for="post_permalink">Site Title</label></p>
            <div class="element">
              <input type="text" class="text" />
            </div>
            <div class="clearl"></div>
          </div>
          <div class="separator"></div>
          <div class="input">
            <p class="label"><label for="post_permalink">Site Subtitle</label></p>
            <div class="element">
              <input type="text" class="text" />
            </div>
            <div class="clearl"></div>
          </div>
        </div>
      </div>

<?php
$this->render('backend/footer.html');
?>

