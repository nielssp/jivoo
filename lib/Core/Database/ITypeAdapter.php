<?php
/**
 * Convert types from database to schema and vice versa
 */
interface ITypeAdapter {
  public function fromSchemaType($type);
  
  public function toSchemaType($dbType);
  
  public function encode($type, $value);
  
  public function decode($type, $value);
}