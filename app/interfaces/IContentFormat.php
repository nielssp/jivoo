<?php

interface IContentFormat {
  public function configure(Configuration $config);
  
  public function toHtml($text);
  
  public function fromHtml($html);
}