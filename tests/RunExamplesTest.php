<?php
namespace Pogo;

use PHPUnit\Framework\TestCase;

class RunExamplesTest extends TestCase {

  public function getPrjDir($suffix = NULL) {
    $base = dirname(__DIR__);
    return $suffix ? $base . DIRECTORY_SEPARATOR . $suffix : $base;
  }

  public function getTestDir($suffix = NULL) {
    $base = __DIR__ . DIRECTORY_SEPARATOR . 'RunExamples';
    return $suffix ? $base . DIRECTORY_SEPARATOR . $suffix : $base;
  }

  public function getExamples() {
    $exs = [];

    $exs['pragmas-parse'] = [
      'pogo --parse example/pragmas.php',
      file_get_contents(self::getTestDir('parse-pragmas.out')),
    ];

    $exs['pragmas-explicit-cmd'] = [
      "pogo example/pragmas.php",
      "hello\n",
    ];

    $exs['pragmas-eval'] = [
      "pogo --run-mode=eval example/pragmas.php",
      "hello\n",
    ];

    // This is OK because "pragmas.php" does not use piped input.
    $exs['pragmas-dashb'] = [
      "pogo --run-mode=dash-b example/pragmas.php",
      "hello\n",
    ];

    $exs['tpl-parse'] = [
      'pogo --parse example/yaml-pipe-tpl.php',
      file_get_contents(self::getTestDir('parse-yaml-pipe-tpl.out')),
    ];

    $exs['tpl-implicit-cmd'] = [
      'echo \'{name: Alice, color: purple}\' | ./example/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    $exs['tpl-explicit-cmd'] = [
      'echo \'{name: Alice, color: purple}\' | pogo example/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    $exs['tpl-eval'] = [
      'echo \'{name: Alice, color: purple}\' | pogo --run-mode=eval example/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    // Using piped-input with dash-b requires buffering.
    $exs['tpl-dashb-buf'] = [
      sprintf('echo \'{name: Alice, color: purple}\' | pogo --run-mode=%s example/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
        escapeshellarg('{with:dash-b,buffer:1}')),
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    // Known bad: 'include' mode outputs shebangs. But otherwise it works.
    $exs['tpl-include'] = [
      'echo \'{name: Bob, color: green}\' | pogo --run-mode=include example/yaml-pipe-tpl.php foo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-shebang.out')),
    ];

    $exs['conflict-parse'] = [
      'pogo --parse example/conflict.php',
      file_get_contents(self::getTestDir('parse-conflict.out')),
    ];

    return $exs;
  }

  /**
   * @param $cmd
   * @param $expectOutput
   * @dataProvider getExamples
   */
  public function testExamples($cmd, $expectOutput) {
    // Tempting to rework this using Symfony command tester, because that
    // can make debugging easier, but pogo will always spawn subprocesses anyway,
    // so we won't actually get that benefit.
    $result = $this->runCmd($cmd);
    $this->assertEquals($expectOutput, $result['stdout']);
    $this->assertEquals(0, $result['exit']);
  }

  public function testConflict() {
    $result = $this->runCmd('pogo example/conflict.php');
    $this->assertRegExp(';The requested package php/version could not be found in any version;', $result['stderr']);
    $this->assertRegExp(';Composer failed to complete;', $result['stderr']);
    $this->assertNotEquals(0, $result['exit']);
    $this->assertEquals('', $result['stdout']);
  }

  protected function runCmd($cmd) {
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
