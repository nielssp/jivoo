<?php
// Extension
// Name         : Markdown format and editor
// Category     : Content
// Website      : http://parsedown.org
// Version      : 1.1.0
// Dependencies : Content

class Markdown extends ExtensionBase {
  protected function init() {
    $this->load('MarkdownFormat');
    $this->load('MarkdownEditor');
    $this->m->Content->addFormat(new MarkdownFormat());
    $this->m->Content->addEditor(new MarkdownEditor());
  }
}
