<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class ParseCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->file)) {
      throw new \Exception("[pogo dl] Missing required file name");
    }

    if (!file_exists($input->file)) {
      throw new \Exception("[pogo dl] Non-existent file: {$input->file}");
    }

    $scriptMetadata = \Pogo\ScriptMetadata::parse($input->file);
    $scriptMetadata = (array) $scriptMetadata;
    echo json_encode($scriptMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n";

    return 0;
  }

}
