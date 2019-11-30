<?php

namespace Qp;

use ProcessHelper\ProcessHelper as PH;
use Symfony\Component\Process\Process;

class QpProject {

  /**
   * @var ScriptMetadata
   */
  public $scriptMetadata;

  /**
   * @var string
   */
  public $path;


  /**
   * Project constructor.
   * @param \Qp\ScriptMetadata $scriptMetadata
   */
  public function __construct(\Qp\ScriptMetadata $scriptMetadata, $path) {
    $this->scriptMetadata = $scriptMetadata;
    $this->path = $path;
  }

  public function createComposerJson() {
    return [
      'name' => 'local/qp',
      'require' => $this->scriptMetadata->require,
      'autoload' => [
        'files' => ['.qplib.php'],
      ],
      'extra' => [
        'qp.expires' => strtotime($this->scriptMetadata->ttl),
      ],
    ];
  }

  /**
   * @return bool
   *   'current': The build exists and is up to date
   *   'stale': The build exists but is old
   *   'empty': The build does not exist
   */
  public function getStatus() {
    if (!file_exists("{$this->path}/composer.json")) {
      return 'empty';
    }
    $composerJson = json_decode(file_get_contents("{$this->path}/composer.json"), 1);
    if (isset($composerJson['extra']['qp.expires']) && $composerJson['extra']['qp.expires'] < time()) {
      return 'stale';
    }

    $sortedReq = $this->scriptMetadata->require;
    ksort($sortedReq);
    ksort($composerJson['require']);
    if ($composerJson['require'] !== $sortedReq) {
      return 'stale';
    }

    return 'current';
  }

  public function buildHelpers() {
    $path = $this->path;
    if (empty($path)) {
      throw new \RuntimeException("Project does not have a path");
    }
    if (!file_exists($path)) {
      mkdir($path, 0777, TRUE);
    }
    foreach (['qplib'] as $helper) {
      file_put_contents("$path/.{$helper}.php", file_get_contents(dirname(__DIR__) . "/templates/{$helper}.php"));
    }
  }

  public function buildComposer() {
    $path = $this->path;
    file_put_contents("$path/composer.json", json_encode($this->createComposerJson()));
    if (file_exists("$path/composer.lock")) {
      unlink("$path/composer.lock");
    }
    PH::runOk(new Process('composer install', $path));
  }

}