<?php
/**
 * Handling of errors, warnings etc.
 *
 * @package PeanutCMS
 */


/**
 * Errors class
 */
class Errors {

  /**
   * Countains warnings and notices. Only presented to user if
   * debugging is turned on.
   * @var array
   */
  private $errorLog;

  private $notifications;

  /**
   * PHP5-style constructor
   */
  public function __construct() {
    $this->errorLog = array();
    $this->notifications = array();
    set_error_handler(array($this, 'handleError'));
    set_exception_handler(array($this, 'handleException'));
  }

  /**
   * Custom error handler replacing default PHP error handler
   *
   * @param int $type Error level
   * @param string $message Error message
   * @param string $file Filename of file in which error occured
   * @param int $line Line number on which error occured
   * @return void
   */
  public function handleError($type, $message, $file, $line) {
    switch ($type) {
      case E_USER_ERROR:
      case E_ERROR:
        throw new PhpErrorException($type, $message, $file, $line);
        break;

      case E_USER_WARNING:
      case E_WARNING:
        $this->log('warning', $message, $file, $line, $type);
        break;

      case E_PARSE:
      case E_NOTICE:
      case E_USER_NOTICE:
      case E_STRICT:
      case E_DEPRECATED:
        $this->log('notice', $message, $file, $line, $type);
        break;

      default:
        throw new PhpErrorException($type, $message, $file, $line);
        break;

    }
  }

  public function handleException(Exception $exception) {
    if (!DEBUG) {
      $this->fatal(tr('Fatal error'), tr('An uncaught exception was thrown.'));
    }
    $file = $exception->getFile();
    $line = $exception->getLine();
    $message = $exception->getMessage();
    /* This should (!!) be a template/view instead..
     * Or should it? (What if the template is missing?) */
    $body = '<h2>' . $message . '</h2>';

    $body .= '<p>'
           . tr('An uncaught %1 was thrown in file %2 on line %3 that prevented further execution of this request.',
                '<strong>' . get_class($exception) . '</strong>',
                '<em>' . basename($file) . '</em>', '<strong>' . $line . '</strong>')
           . '</p><h2>'
           . tr('Where it happened')
           . '</h2><p><code>'
           . $file
           . '</code></p><h2>'
           . tr('Stack Trace')
           . '</h2><table class="trace"><thead><tr><th>'
           . tr('File')
           . '</th><th>'
           . tr('Line')
           . '</th><th>'
           . tr('Class')
           . '</th><th>'
           . tr('Function')
           . '</th><th>'
           . tr('Arguments')
           . '</th></tr></thead><tbody>';

    foreach ( $exception->getTrace() as $i => $trace ) {
      $body .= '<tr class="' . (($i % 2 == 0) ? 'even' : 'odd') . '">'
             . '<td>' . (isset($trace['file']) ? basename($trace['file']) : '') .'</td>'
             . '<td>' . (isset($trace['line']) ? $trace['line'] : '') .'</td>'
             . '<td>' . (isset($trace['class']) ? $trace['class'] : '') .'</td>'
             . '<td>' . (isset($trace['function']) ? $trace['function'] : '') .'</td>'
             . '<td>';
      if (isset($trace['args'])) {
        foreach($trace['args'] as $j => $arg) {
          $body .= ' <span title="' . var_export($arg, true) . '">' . gettype($arg) . '</span>'
                 . ($j < count($trace['args']) -1 ? ',' : '');
        }
      }
      else {
        $body .= 'NULL';
      }
      $body .= '</td></tr>';
    }
    $body .= '</tbody></table>';
    $this->exceptionLayout(tr('Uncaught exception'), $body);
  }

  /**
   * Create a notification for the user
   *
   * @param string $type Notification type, e.g. 'error', 'warning' or 'notice'
   * @param string $message Notification message
   * @param bool $global Is this notification global?
   * @param string $uid Unique identifier
   * @param string $readMore A link to a page where additional information about the error can be found (e.g. the manual)
   * @return void
   */
  public function notification($type, $message, $global = true, $uid = null, $readMore = '') {
    if (isset($_SESSION[SESSION_PREFIX . 'backend_notifications'])
        AND is_array($_SESSION[SESSION_PREFIX . 'backend_notifications'])) {
      $notifications = $_SESSION[SESSION_PREFIX . 'backend_notifications'];
    }
    else {
      $notifications = array();
    }
    if (!is_array($notifications[$type])) {
      $notifications[$type] = array();
    }
    if (isset($uid)) {
      $notifications[$type][$uid] = array(
      	'message' => $message,
      	'global' => $global,
      	'readMore' => $readMore
      );
    }
    else {
      $notifications[$type][] = array(
      	'message' => $message,
      	'global' => $global,
      	'readMore' => $readMore
      );
    }
    $_SESSION[SESSION_PREFIX . 'backend_notifications'] = $notifications;
  }

  /**
   *
   * @param array $types Notification types to get
   * @param boolean $global Return global notifications instead of in-page notifications
   * @param boolean $delete Remove notification after it has been returned
   * @return array An array of requested notifications
   */
  public function getNotifications($types, $global = false, $delete = true) {
    if (!isset($_SESSION[SESSION_PREFIX . 'backend_notifications'])
        OR !is_array($_SESSION[SESSION_PREFIX . 'backend_notifications'])) {
      return false;
    }
    $notifications = &$_SESSION[SESSION_PREFIX . 'backend_notifications'];
    $return = array();
    foreach ($types as $type) {
      if (is_array($notifications[$type])) {
        foreach ($notifications[$type] as $uid => $notification) {
          if ($global === $notification['global']) {
            $return[$uid] = $notification;
            $return[$uid]['type'] = $type;
            if ($delete) {
              unset($notifications[$type][$uid]);
            }
          }
        }
      }
    }
    return $return;
  }

  /**
   * Log a non-fatal error for debugging purposes
   *
   * Errors are saved in the errorLog-array for later use
   *
   * @param string $type Error type, e.g. 'error', 'warning' or 'notice'
   * @param string $message Error message
   * @param string $file Filename of file in which error occured
   * @param int $line Line number on which error occured
   * @param int $phpType PHP error level
   * @return void
   */
  public function log($type, $message, $file = null, $line = null, $phpType = null) {
    $this->errorLog[] = array(
      'type' => $type,
      'phpType' => $phpType,
      'message' => $message,
      'file' => $file,
      'line' => $line
    );
  }

  /**
   * Outputs an error page and kills PeanutCMS
   *
   * @param string $title Title of error
   * @param string $message Short message explaining error
   * @param string $more A longer HTML-formatted (should use paragraphs <p></p>) explanation of the error
   * @return void
   */
  public function fatal($title, $message, $more = NULL) {
    $body = '<h2>' . $message . '</h2>';

    if (!isset($more)) {
      $body .= '<p>'
             . tr('A fatal error has prevented further execution of this request.')
             . '</p>';
    }
    else {
      $body .= $more;
    }
    $body .= '<h2>' . tr('What now?') . '</h2>';

    $body .= '<p>'
           . tr('As a <strong>user</strong> you should contact the owner of this website and notify them of this error.')
           . '</p><p>'
           . tr('As a <strong>webmaster</strong> you should contact the developers of PeanutCMS and notify them of this error.')
           . '</p><p>'
           . tr('As a <strong>developer</strong> you should turn on debugging to get more information about this error.')
           . '</p>';

    $this->exceptionLayout($title, $body);
  }

  private function exceptionLayout($title, $body) {
    ob_start();
    echo '<!DOCTYPE html>
    <html>
      <head>
        <title>' . $title . '</title>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="' . WEBPATH . PUB . 'css/backend.css" type="text/css" />
        <link rel="stylesheet" href="' . WEBPATH . PUB . 'css/exception.css" type="text/css" />

      </head>
      <body>

        <div id="header">
          <div id="bar">
            <div class="right">PeanutCMS</div>
          </div>
          <div id="shadow"></div>
        </div>

        <div id="content">
          <div class="section">
            <div class="container">
              <div id="sad">
              	:-(
              </div>
              <h1>' . $title . '</h1>

              <div class="clearl"></div>

              ' . $body . '

            </div>
          </div>
        </div>

        <div class="footer" id="poweredby">
          Powered by <a href="#">PeanutCMS 0.1</a>
        </div>

        <div class="footer" id="links">
          <a href="#">About</a>
        </div>

      </body>
    </html>';
    $output = ob_get_clean();
    $length = strlen($output);
    // When used as error page the page has to be at least 512 bytes long for Chrome and IE to care about it.
    if ($length < 513) {
      for ($i = 0; $i < (513-$length); $i++) {
        $output .= ' ';
      }
    }
    echo $output;
    exit;
  }


  /* PROPERTIES BEGIN */

  /**
   * Array of readable property names
   * @var array
   */
  private $_getters = array('errorLog');
  /**
   * Array of writable property names
   * @var array
   */
  private $_setters = array();

  /**
   * Magic getter method
   *
   * @param string $property Property name
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property)) {
      return call_user_func(array($this, '_get_' . $property));
    }
    else if (in_array($property, $this->_setters)
             OR method_exists($this, '_set_' . $property)) {
      throw new PropertyWriteOnlyException(
        tr('Property "%1" is write-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }

  /**
   * Magic setter method
   *
   * @param string $property Property name
   * @param string $value New property value
   * @throws Exception
   */
  public function __set($property, $value) {
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property)) {
      call_user_func(array($this, '_set_' . $property), $value);
    }
    else if (in_array($property, $this->_getters)
             OR method_exists($this, '_get_' . $property)) {
      throw new PropertyReadOnlyException(
        tr('Property "%1" is read-only.', $property)
      );
    }
    else {
      throw new PropertyNotFoundException(
        tr('Property "%1" is not accessible.', $property)
      );
    }
  }
  /* PROPERTIES END */

}


/* Exceptions */


class PhpErrorException extends Exception {
  public function __construct($type, $message, $file, $line) {
    parent::__construct($message, $type);
    $this->file = $file;
    $this->line = $line;
  }
}
