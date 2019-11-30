<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class ParseCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->arguments)) {
      throw new \Exception("[pogo dl] Missing required file name");
    }

    foreach ($input->arguments as $target) {
      if (!file_exists($target)) {
        throw new \Exception("[pogo dl] Non-existent file: $target");
      }
    }

    foreach ($input->arguments as $target) {
      $scriptMetadata = \Pogo\ScriptMetadata::parse($target);
      $scriptMetadata = (array) $scriptMetadata;
      echo json_encode($scriptMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
      echo "\n";
    }
    return 0;
  }

}
