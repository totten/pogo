#!/usr/bin/env pogo
<?php

## This example just several different pragmas.  It  gives interesting
## output when running `pogo --parse pragmas.php`

#!require symfony/console: ~3.0
#!require {symfony/yaml: ~3.1, symfony/finder: ~3.2}
#!ini variables_order: ES
#!ini {upload_max_filesize: 1m, memory_limit: 1g}
#!ttl 1 month

//print_r(ini_get_all());
echo "hello\n";
