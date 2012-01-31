<?php
/*
 * Class for working with static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Pages {

  var $page;

  /**
   * Constructor
   */
  function Pages() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    //Define templates
    $PEANUT['templates']->defineTemplate('page', array($this, 'getPath'), array($this, 'getTitle'));

    // Create indexes
    if (!$PEANUT['flatfiles']->indexExists('pages', 'name'))
      $PEANUT['flatfiles']->buildIndex('pages', 'name');


    // Set default settings
    
    // Backend related
    
    // List pages page
    $PEANUT['backend']->addPage('pages', tr('Pages'), tr('All static pages.'), 'folder-collapsed', array(), null, 96);
    $PEANUT['backend']->addContent('pages', new BackendDataTable('pages', 'name',
            array(
                'title' => array('label' => tr('Title')),
                'state' => array('label' => tr('Status'), 'width' => '100'),
                'date' => array('label' => tr('Date'), 'type' => 'date', 'width' => '150')),
            array(
                tr('Edit') => array('backend' => 'edit-page', 'p' => '%id%'),
                tr('Delete') => array('backend' => 'delete-page', 'p' => '%id%'))
            ));
    
    // New page page
    $PEANUT['backend']->addPage('new-page', tr('New page'), tr('Create a new static page.'), 'document', array(
        array('publish', tr('Publish'), 'document'),
        array('save', tr('Save draft'), 'disk'),
    ), array($this, 'submitPage'), 92);
    $PEANUT['backend']->addContent('new-page', new BackendTextInput('page-title', 'title', tr('Title'), '',
            tr('The title of your page.')));
    if (isset($_POST['title']))
      $permalink = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-\/)]/', '', $_POST['title'])));
    else
      $permalink = '';
    $PEANUT['backend']->addContent('new-page', new BackendPermalinkInput('page-name', 'name', 'Permalink', $permalink,
            tr('The absolute path to this page.'), array($this, 'getExampleLink'), 'page-title', true));
    $PEANUT['backend']->addContent('new-page', new BackendTinyMce('page-content', 'content', tr('Content')));
    
    // Edit page page
    if (isset($_GET['p']) AND ($page = $PEANUT['flatfiles']->getRow('pages', $_GET['p'])) !== false) {
      $PEANUT['backend']->addPage('edit-page', tr('Edit page'), tr('Edit a static page.'), 'document', array(
          array('save', tr('Save'), 'disk')
      ), array($this, 'editPage'), 90, false);
      $PEANUT['backend']->addContent('edit-page', new BackendTextInput('page-title', 'title', tr('Title'), $page['title'],
              tr('The title of your page.')));
      $PEANUT['backend']->addContent('edit-page', new BackendTextInput('page-permalink', '', tr('Permalink'),
              $PEANUT['http']->getLink($this->getPath('page', array('p' => $_GET['p']))),
              tr('The absolute path to this page.')));
      $PEANUT['backend']->addContent('edit-page', new BackendTinyMce('page-content', 'content', tr('Content'), $page['content']));
      if ($page['state'] == 'published')
        $state = 'published';
      else
        $state = 'unpublished';
      $PEANUT['backend']->addContent('edit-page', new BackendRadioInput('page-status', 'status', tr('Status'), $state,
              '', array('published' => tr('Published'), 'unpublished' => tr('Unpublished'))));
      
      $PEANUT['backend']->addPage('delete-page', tr('Delete page'), tr('Are you sure you want to delete "%1"?', $page['title']),
              '', array(
                  array('confirm', tr('Confirm'), 'check')
              ), array($this, 'deletePage'), 90, false);
    }

    // Detect
    $this->detect();
    
    $PEANUT['hooks']->attach('finalTemplate', array($this, 'isFinal'));
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  function detect() {
    global $PEANUT;
    $path = $PEANUT['http']->path;
    if (!is_array($path))
      return;
    $pageid = $PEANUT['flatfiles']->indexFind('pages', 'name', implode('/', $path));
    if ($pageid === false OR ($page = $PEANUT['flatfiles']->getRow('pages', $pageid)) === false)
      return;
    $this->page = $page;
    $this->page['content'] = $this->addPageActions($pageid) . $this->page['content'];
    $PEANUT['templates']->setTemplate('page', 5, array('p' => $pageid));
  }
  
  function isFinal() {
    global $PEANUT;
    if ($PEANUT['templates']->template['name'] == 'page') {
      if (!isset($this->page) AND isset($PEANUT['http']->params['p']) AND
              ($page = $PEANUT['flatfiles']->getRow('pages', $PEANUT['http']->params['p'])) !== false) {
        $this->page = $page;
        $this->page['content'] = $this->addPageActions($PEANUT['http']->params['p']) . $this->page['content'];
      }
    }
  }
  
  function deletePage() {
    global $PEANUT;
    $pageId = $_GET['p'];
    if (!$PEANUT['flatfiles']->removeRow('pages', $pageId)) {
      $PEANUT['errors']->notification('error', tr('The page could not be deleted'), false);
      return;
    }
    $PEANUT['errors']->notification('notice', tr('The page has been deleted'), false);
    $PEANUT['http']->redirectPath(null, array('backend' => 'pages'), false);
  }
  
  function editPage() {
    global $PEANUT;
    $error = '';
    
    if (!isset($_GET['p']) OR ($page = $PEANUT['flatfiles']->getRow('pages', $_GET['p'])) === false)
      $error = tr('The page was not found');
    
    if (empty($_POST['title']))
      $error = tr('The title should not be empty');
    else if (empty($_POST['content']))
      $error = tr('The content should not be empty');
    
    if ($error == '') {
      if ($_POST['status'] == 'published')
        $state = 'published';
      else
        $state = 'unpublished';
      $pageArray = array(
          'name' => $page['name'],
          'title' => $_POST['title'],
          'date' => $page['date'],
          'state' => $state,
          'content' => $_POST['content']
      );
      $PEANUT['flatfiles']->insertRow('pages', $_GET['p'], $pageArray);
      if ($state == 'unpublished')
        $PEANUT['errors']->notification('notice', tr('Your page has been saved'), false);
      else
        $PEANUT['errors']->notification('notice', tr('Your page has been published'), false);
      $PEANUT['http']->redirectPath(null, array('backend' => 'edit-page', 'p' => $_GET['p']), false);
    }
    else {
      $PEANUT['errors']->notification('error', $error, false);
    }
  }
  
  function submitPage() {
    global $PEANUT;
    $error = '';
    
    $name = null;
    if (isset($_POST['name'])) {
      $name = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-\/)]/', '', $_POST['name'])));
      if (empty($name))
        $name = null;
    }
    
    if (!isset($name))
      $name = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-\/)]/', '', $_POST['title'])));
    
    $pageid = $PEANUT['flatfiles']->indexFind('pages', 'name', $name);
    if ($pageid !== false AND ($post = $PEANUT['flatfiles']->getRow('pages', $pageid)) !== false)
      $error = tr('A page with that name already exists');
    
    if (empty($name))
      $error = tr('The name should not be empty');
    
    if (empty($_POST['title']))
      $error = tr('The title should not be empty');
    else if (empty($_POST['content']))
      $error = tr('The content should not be empty');
    
    if ($error == '') {
      if (isset($_POST['save']))
        $state = 'unpublished';
      else
        $state = 'published';
      $id = $this->createPage($_POST['title'], $_POST['content'], $state, $name);
      if ($state == 'unpublished')
        $PEANUT['errors']->notification('notice', tr('Your page has been saved'), false);
      else
        $PEANUT['errors']->notification('notice', tr('Your page has been published'), false);
      $PEANUT['http']->redirectPath(null, array('backend' => 'edit-page', 'p' => $id), false);
    }
    else {
      $PEANUT['errors']->notification('error', $error, false);
    }
  }

  function createPage($title, $content, $state = 'unpublished', $name = null) {
    global $PEANUT;
    $date = time();
    $id = $PEANUT['flatfiles']->incrementId('pages');
    if (!isset($name))
      $name = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-\/)]/', '', $title)));
    $page = array(
        'name' => $name,
        'title' => $title,
        'date' => $date,
        'state' => $state,
        'content' => $content
    );
    $PEANUT['flatfiles']->insertRow('pages', $id, $page);
    return $id;
  }

  function getPath($template, $parameters = array()) {
    global $PEANUT;
    switch ($template) {
      case 'page':
        if (!empty($parameters['p'])) {
          if (($page = $PEANUT['flatfiles']->getRow('pages', $parameters['p'])) !== false) {
            $path = explode('/', $page['name']);
            return $path;
          }
        }
        break;
      default:
        break;

    }
  }

  function getExampleLink($placeholder) {
    global $PEANUT;
    return $PEANUT['http']->getLink(array($placeholder));
  }

  function getTitle($template, $parameters = array()) {
    global $PEANUT;
    switch ($template) {
      case 'page':
        if (!empty($parameters['p'])) {
          if (($page = $PEANUT['flatfiles']->getRow('pages', $parameters['p'])) !== false) {
            return $page['title'];
          }
        }
        break;
      default:
        break;

    }
  }
  
  function addPageActions($id) {
    global $PEANUT;
    if ($PEANUT['user']->isLoggedIn()) {
      $buttons = '<span class="backend-buttonset">';
      $buttons .= '<a href="' . $PEANUT['http']->getLink(null, array('backend' => 'edit-page', 'p' => $id)) .
              '" class="backend-button" rev="ui-icon-pencil">' . tr('Edit') . '</a>';
      $buttons .= '<a href="' . $PEANUT['http']->getLink(null, array('backend' => 'delete-page', 'p' => $id)) .
              '" class="backend-button" rev="ui-icon-trash">' . tr('Delete') . '</a>';
      $buttons .= '</span>';
      return $buttons;
    }
    return '';
  }

}
