<?php
namespace Pogo;

class Composer {

  /**
   * @var string
   *   The project folder =.
   */
  protected $pwd;

  protected $composerPath;

  /**
   * @var bool
   */
  protected $forceSkipXdebug = FALSE;

  /**
   * @param string $pwd
   * @return static
   */
  public static function create($pwd) {
    return new static($pwd);
  }

  /**
   * Composer constructor.
   * @param $pwd
   */
  public function __construct($pwd) {
    $this->pwd = $pwd;

    foreach (['composer', 'composer.phar'] as $cmd) {
      $composerPath = PathUtil::findCommand($cmd);
      if ($composerPath) {
        $this->composerPath = $composerPath;
        break;
      }
    }
    if (!$this->composerPath) {
      throw new \Exception("Failed to locate 'composer' or 'composer.phar' in PATH.");
    }

    // Letting composer run with xdebug can muck-up other CLI debugging processes, and
    // composer often just tries to relaunch without xdebug anyway.
    if (!getenv('COMPOSER_ALLOW_XDEBUG')) {
      $isXdebugActive = ini_get('xdebug.remote_enable') || ini_get('xdebug.remote_autostart') || ini_get('xdebug.profiler_enable');
      $this->forceSkipXdebug = $isXdebugActive && $this->isPhpPharFile($this->composerPath);
    }
  }

  /**
   * Execute a composer subcommand.
   * Assert success.
   * Pass through output to STDERR.
   *
   * @param $subcmd
   *   Ex: 'install --dev'
   */
  public function run($subcmd) {
    $composerBaseCmd = $this->forceSkipXdebug
      ? 'php -d xdebug.remote_autostart=0 -d xdebug.remote_enable=0 -d xdebug.profiler_enable=0 '
      : '';
    $composerBaseCmd .= escapeshellcmd($this->composerPath);

    $cmd = sprintf('cd %s && %s %s', escapeshellarg($this->pwd), $composerBaseCmd, $subcmd);
    $process = proc_open($cmd, [['pipe', 'r'], STDERR, STDERR], $pipes);
    if (is_resource($process)) {
      fclose($pipes[0]);
    }
    $result = proc_close($process);
    if ($result !== 0) {
      throw new \RuntimeException("Composer failed to complete.");
    }
  }

  /**
   * @param string $file
   * @return bool
   */
  protected function isPhpPharFile($file) {
    if (preg_match(';\.(phar|php)$;', $file)) {
      return TRUE;
    }
    else {
      $fh = fopen($file, 'r');
      $start = fgets($fh, 1023);
      fclose($fh);
      if (preg_match(';^#.*[/\\]php[\d]*$;', $start) || preg_match(';env php[\d]*$;', $start)) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }

}
