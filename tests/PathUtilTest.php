<?php
namespace Pogo;

use PHPUnit\Framework\TestCase;

class PathUtilTest extends TestCase {

  public function getDotExamples(): array {
    $exs = [
      ["/", "/unix/abs", "/unix/abs"],
      ["/", "unix/rel", "unix/rel"],
      ["/", "c:/win/abs", "c:/win/abs"],
      ["/", "win/rel", "win/rel"],
      ["/", "/c:/win/abs", "/c:/win/abs"],
      ["/", "/unix/foo/bar/..", "/unix/foo"],
      ["/", "/unix/foo/bar/../", "/unix/foo/"],
      ["/", "/unix/foo/bar/.", "/unix/foo/bar"],
      ["/", "/unix/foo/bar/./", "/unix/foo/bar/"],
      ["/", "/unix/foo/./bar", "/unix/foo/bar"],
      ["/", ".", "."],
      ["/", "../", "../"],
      ["/", "..", ".."],
      ["/", "./foo", "foo"],
      ["/", "../foo/bar", "../foo/bar"],
      ["/", ".foo", ".foo"],
    ];
    return $exs;
  }

  /**
   * @param string $sep
   * @param string $inputPath
   * @param string $expectPath
   * @dataProvider getDotExamples
   */
  public function testEvaluateDots($sep, $inputPath, $expectPath): void {
    $origSep = PathUtil::$SEP;
    PathUtil::$SEP = $sep;
    $this->assertEquals($expectPath, PathUtil::evaluateDots($inputPath));
    PathUtil::$SEP = $origSep;
  }

}
