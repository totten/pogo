<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class DownloadCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->arguments)) {
      throw new \Exception("[pogo dl] Missing required file name");
    }

    if (count($input->arguments) > 1 && $input->getOption(['out', 'o'])) {
      throw new \Exception("[pogo dl] The --out=<path> can only be used with one script at a time.");
    }

    // TODO: realpath($target) but using getenv(PWD) or `pwd` to preserve symlink structure

    foreach ($input->arguments as $target) {
      if (!file_exists($target)) {
        throw new \Exception("[pogo dl] Non-existent file: $target");
      }
    }

    foreach ($input->arguments as $target) {
      $this->initProject($input, $target);
    }
    return 0;
  }

}
