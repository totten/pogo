<?php
namespace Pogo;

use PHPUnit\Framework\TestCase;

/**
 * Class DownloadTest
 * @package Pogo
 *
 * Check the options to control dependency download directory.
 */
class DownloadTest extends TestCase {

  public function getTestDir($suffix = NULL): string {
    $base = __DIR__ . DIRECTORY_SEPARATOR . 'DownloadExamples';
    return $suffix ? $base . DIRECTORY_SEPARATOR . $suffix : $base;
  }

  use CommandTestTrait;

  public function tearDown(): void {
    parent::tearDown();
    $tmp = $this->getTestDir('tmp');
    if (file_exists($tmp)) {
      $this->runCmd('rm -rf ' . escapeshellarg($tmp));
    }
  }

  public function getRunModes(): array {
    return [
      ['local'],
      ['isolate'],
    ];
  }

  /**
   * @dataProvider getRunModes
   */
  public function testCliOption($runMode): void {
    $script = $this->getTestDir('dl-via-pragma.php');
    $depDirRel = 'tmp/dl-via-cli';
    $depDirAbs = $this->getTestDir($depDirRel);

    if (file_exists($depDirAbs)) {
      $this->runCmd('rm -rf ' . escapeshellarg($depDirAbs));
    }

    $this->assertFalse(file_exists($depDirAbs));
    $this->runCmd("pogo --run-mode=$runMode --dl=_SCRIPTDIR_/$depDirRel " . escapeshellarg($script));
    $this->assertTrue(file_exists($depDirAbs));
    $this->assertTrue(file_exists("$depDirAbs/vendor/autoload.php"));
  }

  /**
   * @dataProvider getRunModes
   */
  public function testPragmaOption($runMode): void {
    $script = $this->getTestDir('dl-via-pragma.php');
    $depDirAbs = $this->getTestDir('tmp/dl-via-pragma');

    if (file_exists($depDirAbs)) {
      $this->runCmd('rm -rf ' . escapeshellarg($depDirAbs));
    }

    $this->assertFalse(file_exists($depDirAbs));
    $this->runCmd("pogo --run-mode=$runMode " . escapeshellarg($script));
    $this->assertTrue(file_exists($depDirAbs));
    $this->assertTrue(file_exists("$depDirAbs/vendor/autoload.php"));
  }

}
