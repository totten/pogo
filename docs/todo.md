# TODO

* Linux and macOS don't pass shebang args the same say, so `/usr/bin/env pogo --foo' doesn't work on Linux.
    * Workaround: All CLI options should be accessible as pragmas
    * Reconsider the general relationship  between CLI options/notations (`--run-mode`/`--dl`) and pragmas/notations (`#!run`/`#!download`). Maybe there's a way to trim the architecture and expose the same options in both media.
    * Reportedly, newer versions of coreutils may have an option `-S` that helps, but that version wasn't handy on a Xenial box, and it wouldn't be portable anyway. Better to rework for LCD.
* Add a command to cleanup stale data from POGO_BASE.
* Add more test-cases, esp:
    * Update dependencies after editing a script (if the requirements changed)
    * Do NOT update dependencies after editing a script (if the requirements are unchanged)
    * Run a standalone download
    * Ini handling
    * With/without the --allow-stale option
* Add more verbosity options. Cleanup output.
* Make signature more like `php`
    * Accept code via stdin (if no files given)
    * Add `--ini`, `-d`, `-i`, `-v`, `-S`/`-t`, `-m`, `-n`
* Add command to compile/export a static binary in PHAR format (e.g. `pogo --compile --phar=my-script.phar my-script.php`)
* When updating deps, be thread-safe/multi-process-safe
* Add some kind of pseudo requirement for the pogo version, e.g.`#!require {php: ~7.0, pogo: ~0.1}`
* If there are no `require` pragmas, then don't run `composer`. (Use a placeholder for `vendor/autoload.php`?)
* Figure out a way to use `composer` to resolve dependency graph and download URLs *without* using its autoloader/file-structure. Instead, use a new autoloader and downloader to track packages in reusable folder (`~/.cache/pogo/symfony/yaml-3.3.3/`). (*Analogy: nix-style FHS instead of docker-style FHS*)
* Consider integration with multifile composer projects. (Scan `**.php` and update `composer.json`)
* Implement a composer plugin which supplements `composer.json:require` with info from scanned files (`{$psr0}/**.php`, `{$psr4}/**.php`)
* Windows...
