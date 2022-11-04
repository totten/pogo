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

  public function getCliExamples(): array {
    [$phpMajor, $phpMinor] = explode('.', PHP_VERSION);

    return [
      ['{SCRIPT_DIR}/tmp/my-{SCRIPT_NAME}-php-{PHP_XY}', 'tmp/my-dl-via-pragma-php-' . $phpMajor . '.' . $phpMinor, 'local'],
      ['{SCRIPT_DIR}/tmp/my-{SCRIPT_NAME}-php-{PHP_XY}', 'tmp/my-dl-via-pragma-php-' . $phpMajor . '.' . $phpMinor, 'isolate'],
      // Deprecated: Underscore var
      ['_SCRIPTDIR_/tmp/dl-via-cli-legacy', 'tmp/dl-via-cli-legacy', 'local'],
      ['_SCRIPTDIR_/tmp/dl-via-cli-legacy', 'tmp/dl-via-cli-legacy', 'isolate'],
    ];
  }

  /**
   * @dataProvider getCliExamples
   */
  public function testCliOption($depDirExpr, $depDirRel, $runMode): void {
    $script = $this->getTestDir('dl-via-pragma.php');
    $depDirAbs = $this->getTestDir($depDirRel);

    if (file_exists($depDirAbs)) {
      $this->runCmd('rm -rf ' . escapeshellarg($depDirAbs));
    }

    $this->assertFalse(file_exists($depDirAbs), "Folder $depDirAbs should not exist");
    $this->runCmd("pogo --run-mode=$runMode --dl=$depDirExpr " . escapeshellarg($script));
    $this->assertTrue(file_exists($depDirAbs), "Folder $depDirAbs should exist");
    $this->assertTrue(file_exists("$depDirAbs/vendor/autoload.php"), "File $depDirAbs/vendor/autoload.php should exist");
  }

  public function testEnvVar() {
    $_SERVER['SOMEDIR'] = $_ENV['SOMEDIR'] = $this->getTestDir('tmp/foo');
    putenv('SOMEDIR=' . $_ENV['SOMEDIR']);
    try {
      $this->testCliOption('{ENV[SOMEDIR]}/bar', 'tmp/foo/bar', 'local');
    } finally {
      unset($_SERVER['SOMEDIR']);
      unset($_ENV['SOMEDIR']);
      putenv('SOMEDIR');
    }
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
