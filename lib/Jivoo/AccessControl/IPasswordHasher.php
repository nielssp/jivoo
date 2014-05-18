<?php
interface IPasswordHasher {
  public function hash($password);
}