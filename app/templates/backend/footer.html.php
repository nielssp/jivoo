<?php $this->setIndent(4); ?>

    </div>


<?php if (isset($aboutLink)): ?>
    <div class="footer" id="poweredby">
      Powered by
      <a href="<?php echo $aboutLink; ?>">
        PeanutCMS
<?php $version ?>
      </a>
    </div>
<?php endif; ?>

    <div class="footer" id="links">
      <a href="#">Help</a>
    </div>

<?php $this->output('body-bottom'); ?>

  </body>
</html>
