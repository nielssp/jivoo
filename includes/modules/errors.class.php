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

  /**
   * PHP5-style constructor
   */
  function __construct() {
    $this->errorLog = array();
    $this->notifications = array();
    set_error_handler(array($this, 'handle'));
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
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
  function handle($type, $message, $file, $line) {
    switch ($type) {
      case E_USER_ERROR:
      case E_ERROR:
        $this->fatal(tr('Fatal error'), tr('%1 in %2 on line %3.', $message,
        '<code>' . str_replace(PATH, '', $file) . '</code>', '<strong>' . $line . '</strong>'));
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
        $this->fatal(tr('Unknown error of type %1', $type), tr('%1 in %2 on line %3.', $message,
        '<code>' . str_replace(PATH, '', $file) . '</code>', '<strong>' . $line . '</strong>'));
        break;
    }
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
  function notification($type, $message, $global = true, $uid = null, $readMore = '') {
    if (isset($_SESSION[SESSION_PREFIX . 'backend_notifications'])
        AND is_array($_SESSION[SESSION_PREFIX . 'backend_notifications']))
      $notifications = $_SESSION[SESSION_PREFIX . 'backend_notifications'];
    else
      $notifications = array();
    if (!is_array($notifications[$type]))
      $notifications[$type] = array();
    if (isset($uid))
      $notifications[$type][$uid] = array('message' => $message, 'global' => $global, 'readMore' => $readMore);
    else
      $notifications[$type][] = array('message' => $message, 'global' => $global, 'readMore' => $readMore);
    $_SESSION[SESSION_PREFIX . 'backend_notifications'] = $notifications;
  }
  
  /**
   *
   * @param array $types Notification types to get
   * @param boolean $global Return global notifications instead of in-page notifications
   * @param boolean $delete Remove notification after it has been returned
   * @return array An array of requested notifications
   */
  function getNotifications($types, $global = false, $delete = true) {
    if (!isset($_SESSION[SESSION_PREFIX . 'backend_notifications'])
          OR !is_array($_SESSION[SESSION_PREFIX . 'backend_notifications']))
      return false;
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
  function log($type, $message, $file = null, $line = null, $phpType = null) {
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
   * @param string $title Title of error page
   * @param string $content Content of error page
   * @return void
   */
  function fatal($title, $content) {
    ob_start();
    echo '<!DOCTYPE html>
<html>
<head>
<title>' . $title . '</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="' . WEBPATH . PUB . 'css/basic.css" type="text/css" />

</head>
<body>

<h1>PeanutCMS</h1>

<h2>' . $title . '</h2>

<p>' . $content . '</p>
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

}
