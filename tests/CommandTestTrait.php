<?php
namespace Pogo;

trait CommandTestTrait {

  abstract function markTestIncomplete();
  abstract function assertTrue($condition, $message = '');

  public function getPrjDir($suffix = NULL) {
    $base = dirname(__DIR__);
    return $suffix ? $base . DIRECTORY_SEPARATOR . $suffix : $base;
  }

  public function runCmd($cmd) {
    $env = empty($_ENV) ? getenv() : $_ENV;
    if (empty($env)) {
      $this->markTestIncomplete('Cannot execute test without knowledge of the environment');
    }
    $env['PATH'] = self::getPrjDir('bin') . PATH_SEPARATOR . $env['PATH'];

    $process = proc_open($cmd, [
      ['pipe', 'r'],
      ['pipe', 'w'],
      ['pipe', 'w'],
    ], $pipes, self::getPrjDir(), $env);
    $this->assertTrue(is_resource($process));
    fwrite($pipes[0], '');
    fclose($pipes[0]);

    // composer messaging may go t stderr, depending on whether script has run before.
    // but we're only checking correctness of program output on stdout.

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);

    return [
      'stdout' => $stdout,
      'stderr' => $stderr,
      'exit' => $exit,
    ];
  }

}