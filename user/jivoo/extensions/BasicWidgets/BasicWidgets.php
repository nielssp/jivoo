<?php
class BasicWidgets extends ExtensionModule {
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
