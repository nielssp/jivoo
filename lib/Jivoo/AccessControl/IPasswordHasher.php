<?php
interface IPasswordHasher {
  public function hash($password);
  
  public function compare($password, $hash);
}