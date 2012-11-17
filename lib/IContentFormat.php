<?php

interface IContentFormat {
  public function toHtml($text);
  
  public function fromHtml($html);
}
