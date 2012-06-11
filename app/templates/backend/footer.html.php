<?php $this->setHtmlIndent(4); ?>

<?php
if (DEBUG AND is_array($log = $this->errors->getErrorLog())) {
  foreach ($log as $error) {
    echo '<div class="section"><div class="container notification notification-' . $error['type'] .'">'; 
    echo '<strong>' . ucfirst($error['type']) . '</strong> ';
    echo $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];
    echo '</div></div>';
  }
}
?>

    </div>


<?php if (isset($aboutLink)): ?>
    <div class="footer" id="poweredby">
      Powered by
      <a href="<?php echo $aboutLink; ?>">
        PeanutCMS
<?php if (!$this->hideVersion()) echo PEANUT_VERSION; ?>
      </a>
    </div>
<?php endif; ?>

    <div class="footer" id="links">
      <a href="#">Help</a>
    </div>

<?php $this->outputHtml('body-bottom'); ?>

  </body>
</html>
