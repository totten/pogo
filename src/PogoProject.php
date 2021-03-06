<?php

namespace Pogo;

class PogoProject {

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
   * @param \Pogo\ScriptMetadata $scriptMetadata
   * @param string $path
   *   The location to store this project in.
   */
  public function __construct(\Pogo\ScriptMetadata $scriptMetadata, $path) {
    $this->scriptMetadata = $scriptMetadata;
    $this->path = $path;
  }

  public function createComposerJson() {
    $composerJson = [
      'name' => 'pogo/' . preg_replace(';[^a-zA-Z0-9_\-];', '', basename($this->scriptMetadata->file)),
      'autoload' => [
        'files' => ['.pogolib.php'],
      ],
      'extra' => [
        'pogo' => [
          'expires' => strtotime($this->scriptMetadata->ttl),
          'script' => realpath($this->scriptMetadata->file),
        ],
      ],
    ];
    if ($this->scriptMetadata->require) {
      $composerJson['require'] = $this->scriptMetadata->require;
    }
    return $composerJson;
  }

  /**
   * @return string
   *   'current': The build exists and is up to date
   *   'stale': The build exists but is old
   *   'empty': The build does not exist
   */
  public function getStatus() {
    if (!file_exists("{$this->path}/composer.json")) {
      return 'empty';
    }
    if (!file_exists($this->getAutoloader())) {
      return 'broken';
    }
    $composerJson = json_decode(file_get_contents("{$this->path}/composer.json"), 1);
    if (isset($composerJson['extra']['pogo']['expires']) && $composerJson['extra']['pogo']['expires'] < time()) {
      return 'stale';
    }
    if (isset($composerJson['extra']['pogo']['script']) && realpath($composerJson['extra']['pogo']['script']) !== realpath($this->scriptMetadata->file)) {
      return 'stale';
    }
    if (!isset($composerJson['require'])) {
      $composerJson['require'] = [];
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
    foreach (['pogolib'] as $helper) {
      file_put_contents("$path/.{$helper}.php", file_get_contents(dirname(__DIR__) . "/templates/{$helper}.php"));
    }
  }

  public function buildComposer() {
    $path = $this->path;
    file_put_contents("$path/composer.json", json_encode($this->createComposerJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    if (file_exists("$path/composer.lock")) {
      unlink("$path/composer.lock");
    }

    Composer::create($path)->run('install -n --prefer-dist');
  }

  /**
   * @return string
   *   The path to the autoloader for this project.
   */
  public function getAutoloader() {
    return $this->path . '/vendor/autoload.php';
  }

}
