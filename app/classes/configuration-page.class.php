<?php

class ConfigurationPage {

  private $backend;
  private $templates;

  public function __construct(Backend $backend) {
    $this->backend = $backend;
    $this->templates = $this->backend->getTemplates();
  }


  public function controller($path = array(), $parameters = array(), $contentType = 'html') {
    $templateData = array();
    
    $templateData['title'] = tr('Configuration');

    $this->templates->renderTemplate('backend/configuration.html', $templateData);
  }
}

interface IConfigurationOption {
  public function getTemplate();

  public function getTemplateData();

  public function save($parameters = array());
}

abstract class ConfigurationOption implements IConfigurationOption {

  public function __construct() {
  }
}
