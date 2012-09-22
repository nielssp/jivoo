<?php
// Module
// Name           : Errors
// Version        : 0.2.0
// Description    : The PeanutCMS error system
// Author         : PeanutCMS

/**
 * Errors module
 * @package PeanutCMS
 * @subpackage Modules
 */
class Errors extends ModuleBase {

  private $errorLog = array();

  protected function init() {
    set_error_handler(array($this, 'handleError'));
    set_exception_handler(array($this, 'handleException'));
  }

  /**
  * Custom error handler replacing default PHP error handler
  *
  * @param int $type Error level
  * @param string $message Error message
  * @param string $file Filename of file in which error occured
  * @param int $line Line number on which the error occured
  */
  public function handleError($type, $message, $file, $line) {
    switch ($type) {
      case E_USER_ERROR:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_ERROR:
      case E_RECOVERABLE_ERROR:
        throw new ErrorException($message, 0, $type, $file, $line);
        break;

      case E_USER_WARNING:
      case E_USER_DEPRECATED:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_WARNING:
      case E_DEPRECATED:
        $this->log('warning', $message, $file, $line, $type);
        break;

      case E_USER_NOTICE:
        $backtrace = debug_backtrace();
        $file = $backtrace[2]['file'];
        $line = $backtrace[2]['line'];
      case E_PARSE:
      case E_NOTICE:
      case E_STRICT:
        $this->log('notice', $message, $file, $line, $type);
        break;

      default:
        throw new ErrorException($message, 0, $type, $file, $line);
      break;

    }
  }

  /**
   * Get a log of errors
   * @return array Errors
   */
  public function getErrorLog() {
    return $this->errorLog;
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
    if (LOG_ERRORS) {
      $this->logToFile($message . ' in ' . $file . ' on line ' . $line);
    }
  }

  /**
   * Create an error report file from an Exception
   * @param Exception $exception Exception to report
   */
  public function createErrorReport(Exception $exception) {
    if (!is_writable(p(LOG))) {
    }
    else {
      $file = fopen(p(LOG . 'report-' . time() . '.html'), 'w');
      if ($file) {
        fwrite($file, $this->handleException($exception, true));
        fclose($file);
      }
    }
  }

  /**
   * Log an error to the error log
   * @param string $message Error message
   */
  public function logToFile($message) {
    if (!is_writable(p(LOG . 'error.log'))) {
      new GlobalWarning(tr('Unable to write to log file'));
    }
    else {
      $file = fopen(p(LOG . 'error.log'), 'a');
      if ($file) {
        fwrite($file, tdate('c') . ' ' . $message . PHP_EOL);
        fclose($file);
      }
    }
  }

  /**
   * Uncaught Exception handler
   * @param Exception $exception Exception
   */
  public function handleException(Exception $exception) {
    if (!DEBUG) {
      self::fatal(tr('Fatal error'), tr('A fatal error has occured'));
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
          $body .= ' <span title="';
          //var_export($arg, true)
//           if (!is_object($arg) AND !is_array($arg)) {
//             ob_start();
//             var_dump($arg);
//             $body .= ob_get_clean();
//           }
          if (is_scalar($arg)) {
            $body .= h($arg);
          }
          else if (is_object($arg)) {
            $body .= get_class($arg);
          }
          else if (is_array($arg)) {
            $body .= count($arg);
          }
          $body .= '">' . gettype($arg) . '</span>'
                . ($j < count($trace['args']) -1 ? ',' : '');
        }
      }
      else {
        $body .= 'null';
      }
      $body .= '</td></tr>';
    }
    $body .= '</tbody></table>';
    self::exceptionLayout(tr('Uncaught exception'), $body);
  }

  /**
  * Outputs an error page and kills PeanutCMS
  *
  * @param string $title Title of error
  * @param string $message Short message explaining error
  * @param string $more A longer HTML-formatted (should use paragraphs <p></p>) explanation of the error
  */
  public static function fatal($title, $message, $more = null) {
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

    self::exceptionLayout($title, $body);
  }

  /**
   * Creates the exception view
   * @param string $title Title of page
   * @param string $body COntent of page
   */
  private static function exceptionLayout($title, $body) {
    ob_start();
    echo '<!DOCTYPE html>
      <html>
        <head>
          <title>' . $title . '</title>

          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <link rel="stylesheet" href="' . w(PUB . 'css/backend.css') . '" type="text/css" />
          <link rel="stylesheet" href="' . w(PUB . 'css/exception.css') . '" type="text/css" />

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
}
