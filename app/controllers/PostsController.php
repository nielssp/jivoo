<?php

class PostsController extends ApplicationController {

  public $post;
  
  public function index() {
    $this->posts = Post::all(
      SelectQuery::create()
        ->orderByDescending('date')
        ->limit(5)
    );

    $this->render('list-posts.html');
  }

  public function view($post = NULL) {
    $templateData = array();
    
    if ($this->m->Configuration->get('posts.fancyPermalinks') == 'on') {
      $templateData['post'] = Post::find($this->post);
    }
    else {
      $templateData['post'] = Post::find($post);
    }
    
    if (!$this->m->Http->isCurrent($templateData['post']->getPath())) {
      $this->m->Http->redirectPath($templateData['post']->getPath());
    }
    
    $templateData['title'] = $templateData['post']->title;
    
    $templateData['comments'] = array();
    
    /**
     * Just testing...
     * @todo JSON interface/whatever...
     */
    if (isset($parameters['json'])) {
      header('Content-Type: application/json;charset=utf-8');
      echo $templateData['post']->json();
    }
    else {
      $this->m->Templates->renderTemplate('post.html', $templateData);
    }
  }
  
  public function create() {
    
  }
  
}
