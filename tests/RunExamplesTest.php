<?php
namespace Pogo;

use PHPUnit\Framework\TestCase;

class RunExamplesTest extends TestCase {

  use CommandTestTrait;

  public function getTestDir($suffix = NULL): string {
    $base = __DIR__ . DIRECTORY_SEPARATOR . 'RunExamples';
    return $suffix ? $base . DIRECTORY_SEPARATOR . $suffix : $base;
  }

  public function getExamples(): array {
    $exs = [];

    $exs['pragmas-parse'] = [
      'pogo --parse examples/pragmas.php',
      file_get_contents(self::getTestDir('parse-pragmas.out')),
    ];

    $exs['pragmas-explicit-cmd'] = [
      "pogo examples/pragmas.php",
      "hello\n",
    ];

    $exs['pragmas-eval'] = [
      "pogo --run-mode=prepend examples/pragmas.php",
      "hello\n",
    ];

    $exs['pragmas-eval'] = [
      "pogo --run-mode=eval examples/pragmas.php",
      "hello\n",
    ];

    $exs['tpl-parse'] = [
      'pogo --parse examples/yaml-pipe-tpl.php',
      file_get_contents(self::getTestDir('parse-yaml-pipe-tpl.out')),
    ];

    $exs['tpl-implicit-cmd'] = [
      'echo \'{name: Alice, color: purple}\' | ./examples/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    $exs['tpl-explicit-cmd'] = [
      'echo \'{name: Alice, color: purple}\' | pogo examples/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
    ];

    $exs['tpl-phar'] = [
      'mkdir -p tmp; php -d phar.readonly=0 `which pogo` --phar -o tmp/example.phar examples/yaml-pipe-tpl.php && echo \'{name: Alice, color: purple}\' | ./tmp/example.phar -d dum -D dee --dum=deedeedoo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-phar-ok.out')),
    ];

    foreach (['eval', 'isolate', 'local'] as $runMode) {
      $exs["tpl-$runMode"] = [
        "echo '{name: Alice, color: purple}' | pogo --run-mode=$runMode examples/yaml-pipe-tpl.php -d dum -D dee --dum=deedeedoo",
        file_get_contents(self::getTestDir('yaml-pipe-tpl-ok.out')),
      ];
    }

    // Known bad: 'require' mode outputs shebangs. But otherwise it works.
    $exs['tpl-require'] = [
      'echo \'{name: Bob, color: green}\' | pogo --run-mode=require examples/yaml-pipe-tpl.php foo',
      file_get_contents(self::getTestDir('yaml-pipe-tpl-shebang.out')),
    ];

    $exs['conflict-parse'] = [
      'pogo --parse examples/conflict.php',
      file_get_contents(self::getTestDir('parse-conflict.out')),
    ];

    return $exs;
  }

  /**
   * @param $cmd
   * @param $expectOutput
   * @dataProvider getExamples
   */
  public function testExamples($cmd, $expectOutput): void {
    // Tempting to rework this using Symfony command tester, because that
    // can make debugging easier, but pogo will always spawn subprocesses anyway,
    // so we won't actually get that benefit.
    $result = $this->runCmd($cmd);
    $this->assertEquals($expectOutput, $result['stdout']);
    $this->assertEquals(0, $result['exit']);
  }

  public function testConflict(): void {
    $result = $this->runCmd('pogo examples/conflict.php');
    $this->assertRegExp(';The requested package php/version could not be found in any version;', $result['stderr']);
    $this->assertRegExp(';Composer failed to complete;', $result['stderr']);
    $this->assertNotEquals(0, $result['exit']);
    $this->assertEquals('', $result['stdout']);
  }

}
