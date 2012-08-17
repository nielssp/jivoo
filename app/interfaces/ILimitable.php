<?php
interface ILimitable {
  public function limit($limit);
  public function offset($offset);
}