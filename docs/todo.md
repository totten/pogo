# TODO

General ideas to consider/implement/discard before v1.0:

* Reconsider configuration heirarchy (i.e. general relationship among CLI options/notations (`--run-mode`/`--dl`), pragmas/notations (`#!run`/`#!download`), env-vars, and `~/.config`)
    * Why / Reasons / Examples
        * Linux and macOS don't pass shebang args the same way, so `/usr/bin/env pogo --foo' doesn't work on Linux. Allowing all CLI options to be accessible as pragmas would mitigate this limit.
            * (Reportedly, newer versions of coreutils may have an option `-S` that helps, but that version wasn't handy on a Xenial box, and it wouldn't be portable to macOS anyway. Better to have an LCD option that's portable.)
        * Maybe there's a way to trim the architecture and expose the same options in more media.
        * TTL option would probably be more useful in env-var and/or user dot-file than as inline pragma
        * Maybe assimilate more `php` CLI args into the config hierarchy
        * Maybe assimilate more `composer` options into the config heirarchy (prefer-source/prefer-dist/repositories/minimum-stability)
    * Proposed hierarchy: "Built-in default << Global dot-file << User dot-file << Env-var << Inline pragma << CLI arg"
    * Possible organizing principles/conceits
        * PHP DX-first - Primary goal should be that inline-pragmas are easy to read/write. All other notations can be compromised.
        * CLI-first - Primary goal should be consistency w/existing CLI cmds (ex: not `#!require {package: "ver"}` but `#!composer require foo:ver`)
        * YAML/JSON-first - Define a config tree, and all other options are mapped by convention into it
        * php.ini - This cfg mechanism already has universal buy-in; make everything else match it (ex: `pogo -d memory_limit=123 -d pogo.runner=isolate ... myscript.php`)
        * Stability-first - Whatever the previous version did, the next version should do. (*Generally valid, but in `v0.x`... it's probably ample to deprecate+remove over a couple `0.{x+1}` increments.*)
        * Bespoke - The notation for each directive in each medium should be considered separately.
* Add a mechanism to cleanup stale data from POGO_BASE.
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
* When updating deps, be thread-safe/multi-process-safe
* Enhancements for `pogo --phar`
    * If `pogo --phar` is called on a system with `phar.readonly=1`, then respawn subprocess with `phar.readonly=0`
    * Allow some pragmas/CLI options to fine-tune the PHAR (ex: include/exclude tests+docs from downstream packages)
* Add some kind of pseudo requirement for the pogo version, e.g.`#!require {php: ~7.0, pogo: ~0.1}`
* If there are no `require` pragmas, then don't run `composer`. (Use a placeholder for `vendor/autoload.php`?)
* Consider renaming `--dl=X`. `--lib-dir=X` or somesuch may adapt to cases where one wishes to handle download statically/upfront.
* Figure out a way to use `composer` to resolve dependency graph and download URLs *without* using its autoloader/file-structure. Instead, use a new autoloader and downloader to track packages in reusable folder (`~/.cache/pogo/symfony/yaml-3.3.3/`). (*Analogy: nix-style FHS instead of docker-style FHS*)
* Consider integration with multifile composer projects. (Scan `**.php` and update `composer.json`)
* Implement a composer plugin which supplements `composer.json:require` with info from scanned files (`{$psr0}/**.php`, `{$psr4}/**.php`)
* Windows...
