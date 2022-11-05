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

    $realScript = realpath($this->scriptMetadata->file);
    $this->createLinkOrCopy($realScript, "$path/script.php");

    file_put_contents("$path/run.sh", implode("\n", [
      '#!/usr/bin/env bash',
      'export POGO_SCRIPT=' . escapeshellarg($realScript),
      'export POGO_AUTOLOAD=' . escapeshellarg($this->getAutoloader()),
      'export POGO_STDIN=',
      sprintf('[ -e %s ] && RUN_SCRIPT=%s || RUN_SCRIPT="$POGO_SCRIPT"', escapeshellarg("$path/script.php"), escapeshellarg("$path/script.php")),
      sprintf('exec php %s "$RUN_SCRIPT" "$@"',
        \Pogo\Php::iniToArgv($this->scriptMetadata->ini + ['auto_prepend_file' => $this->getAutoloader()])
      ),
      '',
    ]));
    chmod("$path/run.sh", 0755);

    file_put_contents("$path/run.php", implode("\n", [
      '#!/usr/bin/env php',
      '<' . '?php',
      $this->scriptMetadata->ini ? ('/' . '/ WARNING: ini_set() may not work with all variables') : '',
      \Pogo\Php::iniToCode($this->scriptMetadata->ini),
      sprintf('$_SERVER["POGO_SCRIPT"] = $_ENV["POGO_SCRIPT"] = %s;', var_export($realScript, TRUE)),
      'putenv("POGO_SCRIPT=" . $_ENV["POGO_SCRIPT"]);',
      '',
      '$_SERVER["POGO_AUTOLOAD"] = $_ENV["POGO_AUTOLOAD"] =  __DIR__ . "/vendor/autoload.php";',
      'putenv("POGO_AUTOLOAD=" . $_ENV["POGO_AUTOLOAD"]);',
      '',
      'unset($_SERVER["POGO_STDIN"]);',
      'unset($_ENV["POGO_STDIN"]);',
      'putenv("POGO_STDIN");',
      '',
      'require_once __DIR__ . "/vendor/autoload.php";',
      'require_once file_exists(__DIR__ . "/script.php") ? __DIR__ . "/script.php" : $_ENV["POGO_SCRIPT"];',
      '',
    ]));
    chmod("$path/run.php", 0755);
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

  private function createLinkOrCopy($in, $out) {
    if (file_exists($out)) {
      unlink($out);
    }
    if (preg_match('/(linux|darwin)/i', php_uname())) {
      symlink($in, $out);
    }
    else {
      copy($in, $out);
    }
  }

}
