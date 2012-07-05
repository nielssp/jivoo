<?php

interface IEditor {
  public function configure(Configuration $config);
  
  public function getFormat();
}
