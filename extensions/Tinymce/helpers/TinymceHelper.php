<?php
class TinymceHelper {
  
  private $scriptUrl;
  private $styleUrl;
  
  public function __construct($scriptUrl, $styleUrl) {
    $this->scriptUrl = $scriptUrl;
    $this->styleUrl = $styleUrl;
  }
  
  public function getScriptUrl() {
    return $this->scriptUrl;
  }
  
  public function getStyleUrl() {
    return $this->styleUrl;
  }
}