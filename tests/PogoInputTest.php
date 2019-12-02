<?php
namespace Pogo;

use PHPUnit\Framework\TestCase;

class PogoInputTest extends TestCase {

  public function getExamples() {
    // POGO: pogo [--<action>] [action-options] [--] <script-file> [script-options]
    // SYMFONY: symfony <action> [action-options] -- <script-file> [script-options]
    //
    // The key principle: "<script-file>" is the dividing line between
    // action-options and script-options. We detect <script-file> as the first
    // non-dashed input, and enforce the split by injecting '--' before <script-file>.
    //
    // This implies that [action-options] must be strictly *options* (dash-prefix).
    $exs = [];

    // Default actions with basic inputs

    $exs[] = [
      'pogo SCRIPT',
      'symfony run -- SCRIPT',
    ];
    $exs[] = [
      'pogo',
      'symfony help',
    ];

    // Actions map to actions

    $exs[] = [
      'pogo --get',
      'symfony get',
    ];
    $exs[] = [
      'pogo --parse',
      'symfony parse',
    ];
    $exs[] = [
      'pogo --run',
      'symfony run',
    ];

    // Variations on help

    $exs[] = [
      'pogo -h',
      'symfony help -h',
    ];
    $exs[] = [
      'pogo --get -h',
      'symfony get -h',
    ];
    $exs[] = [
      'pogo --get --help',
      'symfony get --help',
    ];
    $exs[] = [
      'pogo --help --get',
      'symfony get --help',
    ];

    // Variations on --run. The SCRIPT is always the dividing line.

    $exs[] = [
      'pogo --dl=DIR SCRIPT wakka wakka --get --help yo',
      'symfony run --dl=DIR -- SCRIPT wakka wakka --get --help yo',
    ];
    $exs[] = [
      'pogo --run --dl=DIR SCRIPT wakka wakka --get --help yo',
      'symfony run --dl=DIR -- SCRIPT wakka wakka --get --help yo',
    ];
    $exs[] = [
      'pogo --dl=DIR --run -f SCRIPT wakka wakka --get --help yo',
      'symfony run --dl=DIR -f -- SCRIPT wakka wakka --get --help yo',
    ];
    $exs[] = [
      // This would be executing a file named 'get'.
      'pogo get',
      'symfony run -- get',
    ];

    // If someone doesn't like our dividing line, they can pass '--' explicitly.

    $exs[] = [
      'pogo --run -- foo --run bar',
      'symfony run -- foo --run bar',
    ];
    $exs[] = [
      'pogo SCRIPT --dl=FOO -ASDF -- foo --run bar',
      'symfony run SCRIPT --dl=FOO -ASDF -- foo --run bar',
    ];

    return $exs;
  }

  /**
   * @param string $inputPogo
   *   The command line, as formatted in pogo's shebang-friendly convention.
   * @param string $expectSymfony
   *   The command line, as formatted in Symfony's convention.
   * @dataProvider getExamples
   */
  public function testFilter($inputPogo, $expectSymfony) {
    // We could've written these tests as array-inputs (since all the classes
    // work with arrays canonically), but it's easier to grok if the examples
    // are written in string notation.
    $inputArgv = explode(' ', $inputPogo);
    $actualArgv = PogoInput::filter($inputArgv);
    $actualArgv[0] = preg_replace('/pogo/', 'symfony', $actualArgv[0]);
    $this->assertEquals($expectSymfony, implode(' ', $actualArgv));
  }

}
