<?php $this->setHtmlIndent(4); ?>
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
