<?php
/**
 * Class for rendering the backend overlay
 *
 * @package PeanutCMS
 */

/**
 * Backend class
 */
class Backend {

  var $page;

  var $pages;

  /**
   * Constructor
   */
  function Backend() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;

    // Set default settings
    if (!$PEANUT['configuration']->exists('backendTheme'))
      $PEANUT['configuration']->set('backendTheme', 'blue-peanut');
    
    // Get backend controls
    require_once(PATH . INC . 'helpers/backend-controls.php');

    if ($PEANUT['user']->loggedIn) {

      // Add default pages
      $this->addPage('notifications', tr('Notifications'), '', 'home', array(), null, 0, false);
      $this->addContent('notifications', array($this, 'notificationsPage'));
      
      $this->addPage('dashboard', tr('Dashboard'), tr('This is your dashboard.'), 'home', array(), null, 100);
      
      $this->addPage('menu', tr('Menu'), tr('Modify the main menu.'), 'note', array(), null, 98);
      $this->addContent('menu', new BackendPageTypes());
      
      $this->addPage('theme', tr('Theme'), tr('Select your theme.'), 'star', array(), null, 0);
      
      // Localization page
      $this->addPage('i18n', tr('Localization'), tr('Change language and time/date settings.'),
              'flag', array(array('save', tr('Save'), 'disk')), array($this, 'saveLocalization'), -8);
      // configurations: language, dateFormat, timeFormat, timeZone, 
      $this->addContent('i18n', new BackendLanguageSelect('localization-language',
              'language', tr('Language'), $PEANUT['configuration']->get('language')));
      $this->addContent('i18n', new BackendTimeZoneSelect('localization-timezone',
              'timezone', tr('Time zone'), $PEANUT['configuration']->get('timeZone')));
      /** @todo Language-specific date/time formats */
      $dateFormats = array(
          'F j, Y',
          'D jS M y',
          'd/m/Y',
          'Y/m/d'
      );
      $timeFormats = array(
          'g:i a',
          'g:i A',
          'H:i',
          'H:i:s'
      );
      $this->addContent('i18n', new BackendFormatSelect('localization-dateformat',
              'dateformat', tr('Date format'), $PEANUT['configuration']->get('dateFormat'),
              '', $dateFormats, array($PEANUT['i18n'], 'date')));
      $this->addContent('i18n', new BackendFormatSelect('localization-timeformat',
              'timeformat', tr('Time format'), $PEANUT['configuration']->get('timeFormat'),
              '', $timeFormats, array($PEANUT['i18n'], 'date')));
      
      // Frontend settings page
      $this->addPage('frontend', tr('Frontend'), tr('Configure the PeanutCMS frontend.'),
              'wrench', array(array('save', tr('Save'), 'disk')),
              array($this, 'saveFrontend'), -10);
      $this->addContent('frontend', new BackendTextInput('frontend-title', 'title', tr('Title'), $PEANUT['configuration']->get('title')));
      $this->addContent('frontend', new BackendTextInput('frontend-subtitle', 'subtitle', tr('Subtitle'), $PEANUT['configuration']->get('subtitle')));
      $permalinkFormat = implode('/', $PEANUT['configuration']->get('postPermalink'));
      $permalinkFormats = array(
          tr('Month') => '%year%/%month%/%name%',
          tr('Day') => '%year%/%month%/%day%/%name%',
          tr('Numeric') => 'blog/%id%');
      $this->addContent('frontend', new BackendFormatSelect('frontend-post-permalink', 'post-permalink',
              tr('Blog post permalink format'), $permalinkFormat, '', $permalinkFormats, array($this, 'getExampleFormat')));
      $this->addContent('frontend', new BackendRadioInput('frontend-commenting-default', 'commenting-default',
              tr('Allow comments'), $PEANUT['configuration']->get('commentingDefault'),
              '',
              array('off' => tr('Off'),
                  'on' => tr('On'))));
      $this->addContent('frontend', new BackendRadioInput('frontend-anonymous-commenting', 'anonymous-commenting',
              tr('Allow anonymous comments'), $PEANUT['configuration']->get('anonymousCommenting'),
              '',
              array('off' => tr('Off'),
                  'on' => tr('On'))));
      $this->addContent('frontend', new BackendRadioInput('frontend-comment-approval', 'comment-approval',
              tr('Manual comment approval'), $PEANUT['configuration']->get('commentApproval'),
              '',
              array('off' => tr('Off'),
                  'on' => tr('On'))));
      $this->addContent('frontend', new BackendRadioInput('frontend-comment-sorting', 'comment-sorting',
              tr('Comment sorting'), $PEANUT['configuration']->get('commentSorting'),
              '',
              array('asc' => tr('Ascending'),
                  'desc' => tr('Descending'))));
      $this->addContent('frontend', new BackendRadioInput('frontend-comment-display', 'comment-display',
              tr('Comment list'), $PEANUT['configuration']->get('commentDisplay'),
              '',
              array('flat' => tr('Flat'),
                  'thread' => tr('Threaded'))));
      $this->addContent('frontend', new BackendNumericSelect('frontend-comment-level-limit',
              'comment-level-limit', tr('Max thread level'), $PEANUT['configuration']->get('commentLevelLimit'), ''));
      $this->addContent('frontend', new BackendRadioInput('frontend-comment-child-sorting', 'comment-child-sorting',
              tr('Comment reply sorting'), $PEANUT['configuration']->get('commentChildSorting'),
              '',
              array('asc' => tr('Ascending'),
                  'desc' => tr('Descending'))));
      
      // Backend settings page
      $this->addPage('backend', tr('Backend'), tr('Configure the PeanutCMS backend.'),
              'gear', array(array('save', tr('Save'), 'disk')),
              array($this, 'saveBackend'), -12);
      $this->addContent('backend', new BackendPermalinkInput('backend-login-link', 'login-link',
              tr('Login link'), $PEANUT['configuration']->get('loginPermalink'), '',
              WEBPATH));
      $this->addContent('backend', new BackendRadioInput('backend-rewrite', 'rewrite',
              tr('HTTP Rewrite'), $PEANUT['configuration']->get('rewrite'),
              tr('Makes links shorter and prettier. Only works on Apache webservers.'),
              array('off' => tr('Off'),
                  'on' => tr('On'))));
      $this->addContent('backend', new BackendThemeSelect('backend-theme', 'theme',
              tr('Backend theme'), $PEANUT['configuration']->get('backendTheme')));
      $this->addContent('backend', new BackendTextInput('backend-username', 'username',
              tr('Username'), $PEANUT['configuration']->get('adminUsername')));
      $this->addContent('backend', new BackendPasswordInput('backend-password', 'password',
              tr('New password'), '', '', true));
      $this->addContent('backend', new BackendPasswordInput('backend-repeat-password', 'repeat-password',
              tr('Repeat new password')));
      $this->addContent('backend', new BackendPasswordInput('backend-old-password', 'old-password',
              tr('Old password'), '', tr('Required if you want to change your password.')));
      
      if ($PEANUT['actions']->has('rewrite-on', 'sessionget')) {
        $PEANUT['configuration']->set('rewrite', 'on');
        $PEANUT['errors']->notification('notice', tr('Your settings have been saved'), false);
        $PEANUT['http']->redirectPath(null, array('backend' => 'backend'), false);
      }

      // Add jQuery UI theme
      $theme = $PEANUT['configuration']->get('backendTheme');
      if (!file_exists(PATH . INC . 'css/' . $theme . '/jquery-ui-1.8.16.custom.css'))
        $theme = 'blue-peanut';
      $PEANUT['theme']->insertHtml('jquery-ui-theme', 'head-bottom', 'link', array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => WEBPATH . INC . 'css/' . $theme . '/jquery-ui-1.8.16.custom.css'), '', 12);

      // Add jQuery and jQuery UI
      $PEANUT['theme']->insertHTML('jquery', 'head-bottom', 'script', array('src' => WEBPATH . INC . 'js/jquery-1.6.2.min.js'), '', 10);
      $PEANUT['theme']->insertHTML('jquery-ui', 'head-bottom', 'script', array('src' => WEBPATH . INC . 'js/jquery-ui-1.8.16.custom.min.js'), '', 8);
//      if ($PEANUT['i18n']->languageCode != 'en' AND
//              file_exists(PATH . INC . 'js/i18n/jquery.ui.datepicker-' . $PEANUT['i18n']->languageCode . '.js'))
//        $PEANUT['theme']->insertHTML('jquery-ui', 'head-bottom', 'script', array('src' => WEBPATH . INC . 'js/i18n/jquery.ui.datepicker-' . $PEANUT['i18n']->languageCode . '.js'), '', 6);

      // Add backend stylesheet and script
      $PEANUT['theme']->insertHTML('globals', 'head-bottom', 'script', array(), 
              '
var WEBPATH = "' . WEBPATH . '";
var INC = "' . INC . '";
', 10);
      $PEANUT['theme']->insertHTML('tinymce', 'head-bottom', 'script', array('src' => WEBPATH . INC . 'js/tinymce/jquery.tinymce.js'), '', 8);
      $PEANUT['theme']->insertHtml('backend-style', 'head-bottom', 'link', array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'href' => WEBPATH . INC . 'css/backend.css'), '', -10);
      $PEANUT['theme']->insertHTML('backend-script', 'head-bottom', 'script', array('src' => WEBPATH . INC . 'js/backend.js'), '', -10);
      
      // Create the toolbar just before the theme is rendered
      $PEANUT['hooks']->attach('preRender', array($this, 'createToolbar'));

      // Create the administration-page just before the theme is rendered
      $PEANUT['hooks']->attach('preRender', array($this, 'createPage'));

    }
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }
  
  function saveLocalization() {
    global $PEANUT;
    $error = '';
    
    if ($error == '') {
      $PEANUT['configuration']->set('language', $_POST['language']);
      $PEANUT['configuration']->set('timeZone', $_POST['timezone']);
      if ($_POST['timeformat'] == 'custom')
        $PEANUT['configuration']->set('timeFormat', $_POST['timeformat_custom']);
      else
        $PEANUT['configuration']->set('timeFormat', $_POST['timeformat']);
      if ($_POST['dateformat'] == 'custom')
        $PEANUT['configuration']->set('dateFormat', $_POST['dateformat_custom']);
      else
        $PEANUT['configuration']->set('dateFormat', $_POST['dateformat']);
      $PEANUT['errors']->notification('notice', tr('Your settings have been saved'), false);
      $PEANUT['http']->redirectPath(null, array('backend' => 'i18n'), false);
    }
    else {
      $PEANUT['errors']->notification('error', $error, false);
    }
  }
  
  function saveBackend() {
    global $PEANUT;
    $error = '';
    
    if (!isset($_POST['username']) OR empty($_POST['username'])) {
      $error = tr('The username should not be empty');
    }
    else {
      $PEANUT['configuration']->set('adminUsername', $_POST['username']);
    }
    
    if (isset($_POST['theme']) AND is_dir(PATH . INC . 'css/' . $_POST['theme']))
      $PEANUT['configuration']->set('backendTheme', $_POST['theme']);
      
    
    if (isset($_POST['password']) AND !empty($_POST['password'])) {
      if (!isset($_POST['repeat-password']) OR empty($_POST['repeat-password']))
        $error = tr('The password should be repeated');
      else if (!isset($_POST['old-password']) OR empty($_POST['old-password']))
        $error = tr('The old password should not be empty');
      else if ($_POST['password'] != $_POST['repeat-password'])
        $error = tr('The two passwords are not identical');
      else if (sha1($_POST['old-password']) != $PEANUT['configuration']->get('adminPassword'))
        $error = tr('The old password is not correct');
      
      if ($error == '') {
        $PEANUT['configuration']->set('adminPassword', sha1($_POST['password']));
      }
    }
    
    if (!isset($_POST['login-link'])) {
      $error = tr('The login link should not be empty');
    }
    else {
      $_POST['login-link'] = strtolower(preg_replace('/[ \-]/', '-', preg_replace('/[^(a-zA-Z0-9 \-)]/', '', $_POST['login-link'])));
      if (empty($_POST['login-link']))
        $error = tr('The login link should not be empty');
      else
        $PEANUT['configuration']->set('loginPermalink', $_POST['login-link']);
    }
    
    
    $rewrite = $PEANUT['configuration']->get('rewrite');
    if ($_POST['rewrite'] == 'off' AND $rewrite != 'off')
      $PEANUT['configuration']->set('rewrite', 'off');
    
    if ($_POST['rewrite'] == 'on' AND $rewrite != 'on') {
      $htaccess = '
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
</IfModule>

ErrorDocument 404 ' . WEBPATH . 'index.php
';
      $fp = fopen(PATH . '.htaccess', 'w');
      if (!$fp) {
        $error = tr('The file %1 is not writable', WEBPATH . '.htaccess');
      }
      else {
        fwrite($fp, $htaccess);
        fclose($fp);
        $_SESSION[SESSION_PREFIX . 'action'] = 'rewrite-on';
        $PEANUT['http']->redirectPath(null, array('rewrite-on' => 'rewrite-on'), false, null, true);
      }
    }
    
    if ($error == '')
      $PEANUT['errors']->notification('notice', tr('Your settings have been saved'), false);
    else
      $PEANUT['errors']->notification('error', $error, false);
    $PEANUT['http']->redirectPath(null, array('backend' => 'backend'), false);
  }
  
  function saveFrontend() {
    global $PEANUT;
    $PEANUT['configuration']->set('title', $_POST['title']);
    $PEANUT['configuration']->set('subtitle', $_POST['subtitle']);
    $PEANUT['errors']->notification('notice', tr('Your settings have been saved'), false);
    if ($_POST['post-permalink'] == 'custom')
      $permalink = $_POST['post-permalink_custom'];
    else
      $permalink = $_POST['post-permalink'];
    $permalink = explode('/', $permalink);
    $permalink2 = array();
    foreach ($permalink as $part) {
      if (!empty($part))
        $permalink2[] = $part;
    }
    if (count($permalink2) > 0)
      $PEANUT['configuration']->set('postPermalink', $permalink2);

    if ($_POST['commenting-default'] == 'on')
      $PEANUT['configuration']->set('commentingDefault', 'on');
    else
      $PEANUT['configuration']->set('commentingDefault', 'off');

    if ($_POST['anonymous-commenting'] == 'on')
      $PEANUT['configuration']->set('anonymousCommenting', 'on');
    else
      $PEANUT['configuration']->set('anonymousCommenting', 'off');

    if ($_POST['comment-approval'] == 'on')
      $PEANUT['configuration']->set('commentApproval', 'on');
    else
      $PEANUT['configuration']->set('commentApproval', 'off');

    if ($_POST['comment-sorting'] == 'asc')
      $PEANUT['configuration']->set('commentSorting', 'asc');
    else
      $PEANUT['configuration']->set('commentSorting', 'desc');

    if ($_POST['comment-child-sorting'] == 'asc')
      $PEANUT['configuration']->set('commentChildSorting', 'asc');
    else
      $PEANUT['configuration']->set('commentChildSorting', 'desc');

    if ($_POST['comment-display'] == 'thread')
      $PEANUT['configuration']->set('commentDisplay', 'thread');
    else
      $PEANUT['configuration']->set('commentDisplay', 'flat');

    $PEANUT['configuration']->set('commentLevelLimit', $_POST['comment-level-limit']);

    $PEANUT['http']->redirectPath(null, array('backend' => 'frontend'), false);
  }
  
  function createToolbar() {
    global $PEANUT;
    // Create a container for the toolbar
    $PEANUT['theme']->insertHtml('backend-toolbar-container', 'body-bottom', 'div', array('id' => 'backend-toolbar-container'), '<div id="backend-toolbar-shadow"></div>');

    // Sort pages based on priority
    usort($this->pages, 'prioritySorter');

    $toolbarContent = '<div id="backend-global-notifications">';
    foreach ($PEANUT['errors']->getNotifications(array('error', 'warning', 'notice'), true, false) as $notification) {
        $toolbarContent .= '<a href="' . $PEANUT['http']->getLink(null, array('backend' => 'notifications')) . '" class="backend-global-';
        if ($notification['type'] == 'notice')
          $toolbarContent .= 'notice';
        else
          $toolbarContent .= 'error';
        $toolbarContent .= '">';
        $toolbarContent .= tr(ucfirst($notification['type']));
        $toolbarContent .= '</a>';
    }
    $toolbarContent .= '</div>';
    
    $toolbarContent .= '<a href="' . $PEANUT['actions']->add('logout') . '" rev="ui-icon-locked" class="backend-logout">' . tr('Log out') . '</a> ';
    foreach ($this->pages as $page) {
      if ($page['menu'] !== false) {
        $toolbarContent .= '<a href="' . $PEANUT['http']->getLink(null, array('backend' => $page['name'])) . '"';
        if (!empty($page['icon']))
          $toolbarContent .= ' rev="ui-icon-' . $page['icon'] . '"';
        $toolbarContent .= '>' . $page['title'] . '</a> ' . "\n";
      }
    }

    // Create the toolbar
    $PEANUT['theme']->insertHtml('backend-toolbar', 'backend-toolbar-container-top', 'div', array('id' => 'backend-toolbar', 'class' => 'ui-widget-header'), $toolbarContent);
  }

  /**
   *
   * @param string $name Page name
   * @param string $title Page title
   * @param string $description Page description
   * @param string $icon Page icon (an icon part of jQuery UI
   * @param array $buttons Button array containing arrays ala array(name, label, icon), first button is default
   * @param callback $submitFunction This function is called when the form is submitted
   * @param int $priority Page priority
   * @param boolean $menu If the page should be in the menu
   */
  function addPage($name, $title, $description = '', $icon = null, $buttons = array(), $submitFunction = null, $priority = 0, $menu = true) {
    $this->pages[$name] = array(
        'name' => $name,
        'title' => $title,
        'description' => $description,
        'icon' => $icon,
        'buttons' => $buttons,
        'function' => $submitFunction,
        'priority' => $priority,
        'menu' => $menu,
        'content' => array());
  }

  /**
   * Add content to a backend page
   *
   * @param type $page The page name to add content to
   * @param mixed $content Can be a string, a function that returns a string or an object with a render()-method that
   * returns a string
   */
  function addContent($page, $content) {
    if (is_string($content))
      $this->pages[$page]['content'][] = array('string', $content);
    else if (is_callable($content))
      $this->pages[$page]['content'][] = array('function', $content);
    else if (is_object($content) AND is_callable(array($content, 'render')))
      $this->pages[$page]['content'][] = array('object', $content);
  }

  function createPage() {
    global $PEANUT;
    if (isset($PEANUT['http']->params['backend'])) {
      foreach ($this->pages as $page) {
        if ($page['name'] == $PEANUT['http']->params['backend']) {
          $this->page = $page;
          break;
        }
      }
    }
    if (!isset($this->page))
      $display = 'display:none;';
    else
      $display = '';
    // Create the overlay background
    $PEANUT['theme']->insertHtml('backend-overlay', 'body-bottom', 'div', array(
        'id' => 'backend-overlay',
        'class' => 'ui-widget-overlay',
        'style' => $display), ' ');
	
    
    if ($PEANUT['actions']->has('backend-submit') AND is_callable($this->page['function']))
      call_user_func($this->page['function']);

    $pageContent = '
      
  <form action="' . $PEANUT['http']->getLink(null, $_GET) . '" method="post">
    <input type="hidden" name="action" value="backend-submit" />
    <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
      <span class="ui-dialog-title">' . $this->page['title'] . '</span>
      <a href="' . $PEANUT['http']->getLink() . '" id="backend-page-close">Close</a>
    </div>
    
    <div class="ui-dialog-content ui-widget-content">
    ';
    foreach ($PEANUT['errors']->getNotifications(array('error', 'warning', 'notice'), false, true) as $notification) {
      $pageContent .= '<div class="backend-notification ui-widget"><div class="ui-corner-all ';
      if ($notification['type'] == 'notice')
        $pageContent .= 'ui-state-highlight';
      else
        $pageContent .= 'ui-state-error';
      $pageContent .= '">
        <p>
          <span class="ui-icon ';
      if ($notification['type'] == 'notice')
        $pageContent .= 'ui-icon-info';
      else
        $pageContent .= 'ui-icon-alert';
      $pageContent .= '"></span> 
<strong>' . tr(ucfirst($notification['type'])) . '</strong> ' . $notification['message'];
      if (!empty($notification['readMore']))
        $pageContent .= ' <a href="' . $notification['readMore'] . '">(' . tr('Read more') . ')</a>';
      $pageContent .= '
        </p>
      </div>    
    </div>';
    }
    $pageContent .= '

      <p>' . $this->page['description'] . '</p>

      ';

    foreach ($this->page['content'] as $content) {
      if ($content[0] == 'string')
        $pageContent .= $content[1];
      else if ($content[0] == 'object')
        $pageContent .= call_user_func(array($content[1], 'render'));
      else
        $pageContent .= call_user_func($content[1]);
    }
   
    $pageContent .= '


    </div>
    <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
      <div class="ui-dialog-buttonset">
      ';
    foreach ($this->page['buttons'] as $button) {
      $pageContent .= '<button type="submit" name="' . $button[0] . '" value="' . $button[0] . '"';
      if (!empty($button[2]))
        $pageContent .= ' rev="ui-icon-' . $button[2] . '"';
      $pageContent .= '>' . $button[1] . '</button> ' . "\n";
    }
    $pageContent .= '
        <a href="' . $PEANUT['http']->getLink() . '" class="backend-button" rev="ui-icon-close">Cancel</a>
      </div>
    </div>
  </form>
';

    // Create the page
    $PEANUT['theme']->insertHtml('backend-page', 'body-bottom', 'div', array(
        'id' => 'backend-page',
        'class' => 'ui-dialog ui-widget ui-widget-content ui-corner-all',
        'style' => $display), $pageContent);
  }
  
  function notificationsPage() {
    global $PEANUT;
    $content = '';
    foreach ($PEANUT['errors']->getNotifications(array('error', 'warning', 'notice'), true, true) as $notification) {
      $content .= '<div class="backend-notification ui-widget"><div class="ui-corner-all ';
      if ($notification['type'] == 'notice')
        $content .= 'ui-state-highlight';
      else
        $content .= 'ui-state-error';
      $content .= '">
        <p>
          <span class="ui-icon ';
      if ($notification['type'] == 'notice')
        $content .= 'ui-icon-info';
      else
        $content .= 'ui-icon-alert';
      $content .= '"></span> 
<strong>' . tr(ucfirst($notification['type'])) . '</strong> ' . $notification['message'];
      if (!empty($notification['readMore']))
        $content .= ' <a href="' . $notification['readMore'] . '">(' . tr('Read more') . ')</a>';
      $content .= '
        </p>
      </div>    
    </div>';
    }
    return $content;
  }
  
  function getExampleFormat($postPermalink) {
    global $PEANUT;
    return $PEANUT['posts']->getExampleLink(tr('post-name'), $postPermalink);
  }

}
