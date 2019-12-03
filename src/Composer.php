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
    $cmd = sprintf('cd %s && %s %s', escapeshellarg($this->pwd), escapeshellcmd($this->composerPath), $subcmd);
    $process = proc_open($cmd, [['pipe', 'r'], STDERR, STDERR], $pipes);
    if (is_resource($process)) {
      fclose($pipes[0]);
    }
    $result = proc_close($process);
    if ($result !== 0) {
      throw new \RuntimeException("Composer failed to complete.");
    }
  }

}
