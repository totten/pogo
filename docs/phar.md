# Compile to PHAR

Suppose you've created a file `my-script.php` with some inline dependencies:

```php
<?php
#!require symfony/yaml: '~4.0'
use Symfony\Component\Yaml\Yaml;

echo YAML::dump(['good morning' => $argv[1] ?? 'world']);
```

As we saw in the [Execution](exec.md) page, you can run this script through `pogo`, and it will resolve the dependencies (`symfony/yaml`) automatically.

```
$ pogo my-script.php dave
Loading composer repositories with package information
Updating dependencies (including require-dev)
...
good morning: dave
```

But what if you want to run the script on another computer -- which may not have `pogo`, `composer`, or `symfony/yaml` installed? You could
generate a [PHAR file](https://www.php.net/manual/en/intro.phar.php). This is convenient for distributing PHP command-line tools.

The command `pogo --phar` will generate a PHAR executable:

```
$ pogo --phar my-script.php
Loading composer repositories with package information
Updating dependencies (including require-dev)
...
Generating my-script.phar from my-script.php

$ ./my-script.phar dave
good morning: dave
```

The generated file, `my-script.phar`, is a portable executable that combines `my-script.php` with its dependencies.
You can distribute this PHAR for use on other systems. Those systems will not need `pogo`, `composer`, or `symfony/yaml` pre-installed.

## phar.readonly

Many PHP environments have an option `phar.readonly` which prevents one from generating to PHAR.  If you get an error
about `phar.readonly`, then disable the options. Either:

* Update `php.ini` to set `phar.readonly=0`
* Revise the call to `pogo` and set the option at runtime:
  ```bash
  ## Example
  php -d phar.readonly=0 /usr/local/bin/pogo --phar my-script.php

  ## Formula
  php -d <INI_KEY_VALUE> <POGO_BINARY> --phar <MY_SCRIPT>
  ```

## Output file

The default output file simply swaps the file extension (`*.php` to `*.phar`). You may also choose an explicit name using `--out` (`-o`), e.g.

```bash
## Put the executable in a $HOME/bin
pogo --phar -o "$HOME/bin/my-script" my-script.php

## Put a versioned executable in a web folder
pogo --phar -o /var/www/release/my-script-$(date 'Ymd').phar my-script.php
```

> __TIP__: The `-o <filename>` option *must* come before the script filename.

## Limitations

* Once built, a PHAR is read-only. This means that:
    * Dependencies don't auto-update.
    * If PHP code tries to edit its own file/folder at runtime, then...  that...  won't work.  You'll need a clear
      separation between runtime-code and writable-data.  (For CLI scripts, consider picking a data folder based on CLI arguments or
      [XDG_*_HOME](https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html).)
* At runtime, files within the PHAR have special file-paths (eg `phar://<path-to-phar>/<path-within-phar>`). This tends to work fine
  if you access the files using the PHP constants and the PHP standard library (e.g. `fopen(__DIR__.'/default-data.txt', 'r')`).
  But it may require adaptation for other contexts.
