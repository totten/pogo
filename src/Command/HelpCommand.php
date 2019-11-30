<?php
namespace Pogo\Command;

use Pogo\PogoInput;

class HelpCommand {

  public function run(PogoInput $input) {
    $cmd = basename($input->interpreter);
    echo "Example: Run a script\n";
    echo "  $cmd run [download-options] <script-file> [--] [script-options]\n";
    echo "\n";
    echo "Example: Download dependencies for a script\n";
    echo "  $cmd dl [download-options] <script-file>\n";
    echo "\n";
    echo "Example: Preview the metadata for a script\n";
    echo "  $cmd parse <script-file>\n";
    echo "\n";
    echo "Example: Update dependencies in an existing project directory\n";
    echo "  cd <out-dir>\n";
    echo "  $cmd up\n";
    echo "\n";
    //    echo "Example: Remove any expired builds from the base folder\n";
    //    echo "  $cmd clean\n";
    //    echo "\n";
    echo "Download Options:\n";
    echo "  -f        Force; recreate project, even if it appears current\n";
    echo "  -o=<out>  Output directory\n";
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
