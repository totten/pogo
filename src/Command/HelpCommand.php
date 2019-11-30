<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class HelpCommand {

  public function run(PogoInput $input) {
    $cmd = basename($input->program);
    echo "Example: Run the given script\n";
    echo "  $cmd run [build-options] <script-file> [--] [script-options]\n";
    echo "\n";
    echo "Example: Create a folder with the script and all dependencies\n";
    echo "  $cmd create [build-options] <script-file>\n";
    echo "\n";
    //    echo "Example: Update dependencies in an existing project directory\n";
    //    echo "  $cmd update <script-file>\n";
    //    echo "\n";
    echo "Build Options:\n";
    echo "  -f        Force; recreate project, even if it appears current\n";
    echo "  -o <out>  Output directory\n";
    echo "\n";
    echo "\n";
    echo "Environment:\n";
    echo "  POGO_BASE   Default location for output folders\n";
    echo "              To store in-situ as a dot folder, use POGO_BASE=.\n";
    echo "              If omitted, defaults to ~/.cache/pogo or /tmp/pogo\n";
    echo "\n";
    return 0;
  }

}
