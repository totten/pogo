<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class DownloadCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->file)) {
      throw new \Exception("[pogo dl] Missing required file name");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure

    if (!file_exists($input->file)) {
      throw new \Exception("[pogo dl] Non-existent file: {$input->file}");
    }

    $this->initProject($input, $input->file);

    return 0;
  }

}
