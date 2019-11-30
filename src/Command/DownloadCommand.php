<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class DownloadCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->script)) {
      throw new \Exception("[get] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure

    if (!file_exists($input->script)) {
      throw new \Exception("[get] Non-existent file: {$input->script}");
    }

    $this->initProject($input, $input->script);

    return 0;
  }

}
