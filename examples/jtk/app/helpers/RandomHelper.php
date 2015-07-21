<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace App\Helpers;

use Jivoo\Helpers\Helper;

class RandomHelper extends Helper {
  private $firstNames = array('Noah', 'Liam', 'Mason', 'Jacob', 'William', 
    'Ethan', 'Michael', 'Alexander', 'James', 'Daniel', 'Emma', 'Olivia', 
    'Sophia', 'Isabella', 'Ava', 'Mia', 'Emily', 'Abigail', 'Madison', 
    'Charlotte');

  private $lastNames = array('Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 
    'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor', 'Anderson', 'Thomas', 
    'Jackson', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 
    'Robinson');
  
  public function name() {
    return $this->firstName() . ' ' . $this->lastName();
  }
  
  public function firstName() {
    return $this->firstNames[rand(0, count($this->firstNames) - 1)];
  }
  
  public function lastName() {
    return $this->lastNames[rand(0, count($this->lastNames) - 1)];
  }
}