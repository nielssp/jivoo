<?php
use Jivoo\Vendor\BuildScript;
use Jivoo\Vendor\InstallException;

$majorVersion = 1;

$this->name = 'jquery/jquery';

$this->version = 'latest';

$this->sources = array(
  //'http://code.jquery.com/jquery-' . $this->version . '.min.js'
);

$this->prepare = function(BuildScript $build) use ($majorVersion) {
  $build->info(tr('Finding newest version...'));
  $versions = file_get_contents('http://code.jquery.com/jquery/');
  preg_match('/jQuery Core (' . $majorVersion . '\.[0-9]+\.[0-9]+)/', $versions, $matches);
  if (!isset($matches[1]))
    throw new InstallException(tr('Could not find jQuery version'));
  $build->version = $matches[1];
  $build->sources = array(
    'http://code.jquery.com/jquery-' . $build->version . '.min.js'
  );
};


$this->install = function(BuildScript $build) {
  $build->installFile('jquery-' . $build->version . '.min.js');

  $build->manifest = array(
    'library' => true,
    'website' => 'http://jquery.com',
    'category' => 'JavaScript',
    'resources' => array(
      'jquery.js' => array(
        'file' => 'jquery-' . $build->version . '.min.js',
        'cdn' => '//code.jquery.com/jquery-' . $build->version . '.min.js'
      )
    )
  );
};
