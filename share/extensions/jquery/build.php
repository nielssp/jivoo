<?php
use Jivoo\Extensions\BuildScript;

$this->name = 'jquery';

$this->version = '1.11.3';

$this->sources = array(
  'http://code.jquery.com/jquery-' . $this->version . '.min.js'
);

$this->manifest = array(
  'library' => true,
  'website' => 'http://jquery.com',
  'category' => 'JavaScript',
  'resources' => array(
    'jquery.js' => array(
      'file' => 'jquery-' . $this->version . '.min.js',
      'cdn' => '//code.jquery.com/jquery-' . $this->version . '.min.js'
    )
  )
);

$this->install = function(BuildScript $build) {
  $build->installFile('jquery-' . $build->version . '.min.js');
};