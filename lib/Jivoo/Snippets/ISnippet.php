<?php
/**
 * Snippet interface.
 * @package Jivoo\Snippets
 */
interface ISnippet {
  /**
   * Execute snippet logic and produce response.
   * @return Response|string A response object or content.
   */
  public function render();
}