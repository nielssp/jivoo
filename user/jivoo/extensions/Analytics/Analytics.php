<?php
class Analytics extends ExtensionModule {
  
  protected $modules = array('View');
  
  protected function init() {
    if (isset($this->config['id'])) {
      $this->view->blocks->append(
        'body-top',
        '<script type="text/javascript">' . "
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '" . $this->config['id'] . "', 'auto');
  ga('send', 'pageview');
</script>" . PHP_EOL
      );
    }
  }
}
