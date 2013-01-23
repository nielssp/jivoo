<?php
// Extension
// Name         : Development version
// Dependencies : Templates

class InDev extends ExtensionBase {
  protected function init() {
    if (file_exists($this->p(null, '../.git'))) {
      if (PHP_OS == 'WINNT') {
        $revDate = exec(
          '"%PROGRAMFILES(X86)%/git/cmd/git" log -1 --format=%ci 2>&1');
      }
      else {
        $revDate = exec('git log -1 --format=%ci 2>&1');
      }
      $projectStart = strtotime('2012-01-31 16:39:33 +0100');
      $time = strtotime($revDate);
      $difference = $time - $projectStart;
      $build = floor($difference / (60 * 60));
      $this->m
        ->Templates
        ->insertHtml('build-number', 'body-bottom', 'div',
          array(
            'style' => 'position:fixed;bottom:30px;right:10px;font-family:Candara, sans-serif;font-size:12px;text-align:right;'
          ),
          'DEVELOPMENT VERSION<br/>VERSION ' . PEANUT_VERSION . '<br/>BUILD '
              . $build);
    }
  }
}
