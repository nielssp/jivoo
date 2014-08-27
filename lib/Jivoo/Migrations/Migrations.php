<?php
// Module
// Name           : Migrations
// Description    : The Jivoo schema and data migration system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Databases

/**
 * Migration module
 * @package Jivoo\Databases
 */
class Migrations extends LoadableModule {

  protected $modules = array('Databases');
  
  protected function init() {
  }
}