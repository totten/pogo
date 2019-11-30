<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class ParseCommand {

  use DownloadCommandTrait;

  public function run(PogoInput $input) {
    if (empty($input->script)) {
      throw new \Exception("[pogo dl] Missing required file name");
    }

    if (!file_exists($input->script)) {
      throw new \Exception("[pogo dl] Non-existent file: {$input->script}");
    }

    $scriptMetadata = \Pogo\ScriptMetadata::parse($input->script);
    $scriptMetadata = (array) $scriptMetadata;
    echo json_encode($scriptMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n";

    return 0;
  }

}
