<?php
// Extension
// Name         : Basic widgets
// Category     : Widgets
// Website      : http://apakoh.dk
// Version      : 1.0
// Dependencies : Templates Routing Widgets

class BasicWidgets extends ExtensionBase {
  protected function init() {
    $this->m->Widgets->register(new RecentPostsWidget(
      $this->app,
      $this->p('templates/recent-posts-widget.html.php')
    ));

    $this->m->Widgets->register(new RecentCommentsWidget(
      $this->app,
      $this->p('templates/recent-comments-widget.html.php')
    ));
  }
}
