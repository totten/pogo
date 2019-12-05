# Pragmas

Pogo accepts instructions using a `#!foo` notation. The following are supported:

## require

* __Description__: Download a package via composer/packagist
* __Signature__: `#!require <yaml-key-value>`
* __Example__: `#!require symfony/yaml: ~3.0`
* __Example__: `#!require {symfony/yaml: ~3.0, symfony/finder: ~3.0}`
* __Comment__: The `<package>` and `<version>` notations match [composer's `require`](https://getcomposer.org/doc/04-schema.md#require).

## ini

* __Description__: Set the value of `php.ini` option *before* launching the script.
* __Signature__: `#!ini <yaml-key-value>`
* __Example__: `#!ini variables_order: EGPS`
* __Example__: `#!ini {max_upload_size: 1m, memory_limit: 1g}`

## ttl

* __Description__: The time-to-live determines the maximum time to retain previously downloaded dependencies.
    * The `<qty> <unit>` notation is a strict subset of [PHP `strtotime()`](php.net/strtotime).
    * The `<unit>` can be `sec`, `min`, `hour`, `day`, `week`, `month`, `year`.
* __Signature__: `#!ttl <qty> <unit>`
* __Example__: `#!ttl 7 days`
* __Comment__: Any change in dependencies will trigger a new download -- regardless of `ttl`. So what is `ttl` for?
  It allows scripts to remain reasonably secure (up-to-date) by default.  If you don't want this, you can always peg versions more precisely (`~3.0` vs `3.4.20`) or a set a long a `ttl` (`10 years`).

## run

* __Description__: After `pogo` updates the dependencies, it needs to call `php` and execute your script. Unfortunately, I have not found
  a great, one-size-fits-all technique for this delegation. The defaults should *execute correctly* in most scripting scenarios - but
  support for *nice backtraces* might be compromised if you use a `#!/usr/bin/env pogo...` header. In that case, you might want to
  try a different mode.
* __Signature__: `#!run <yaml-string|yaml-key-value>`
* __Example__: `#!run dash-b`
* __Example__: `#!run {with: dash-b, buffer: true}`
* __Options__:
    * `auto`: Let `pogo` pick a mechanism. Generally, this is `prepend`.
    * `prepend`: Loosely, this runs `php -d auto_prepend_file=$autoloader $your_script`. Better than all other existing runners.
    * `include`: Loosely, this runs `php -r 'require_once $autoloader; include $your_script;`. Almost as good as `prepend;
      however, if `$your_script` is a standalone program (`#!/usr/bin/env pogo`), then  it will erroneously output the first line.
    * `eval`: Loosely, this runs `php -r 'require_once $autoloader; eval(cleanup($your_script))'`. This fixes the
      erroneous output, but backtraces and debugging may not be as pleasant.
    * `dash-b`: Loosely, this runs `echo | php -B 'require_once $autoloader;' -F $your_script`. This avoids the
      erroneous output and gives decent backtraces, but it will not handle STDIN normally.
        * If want to use `dash-b` and you *know* that there will be piped input, then set `buffer:true`.
          The input will be available in an alternate location:
          ```php
          #!run {with: dash-b, buffer: true}
          $data = file_get_contents(pogo_stdin()));
          printf("Received input %s:\n%s\n", md5($data), $data);
          ```
    * `data`: This is very similar to `eval`, but it replaces `eval(...)` with `include 'data://text/...'`.
      At the moment, I don't think it has any real advantage over `eval`, but I've kept it as potential inspiration.

# YAML (Subset)

Most pragmas accept either strings or key-value pairs, e.g.

```php
#!foo somestring
#!bar {key1: value1, key2: value2}
```

To simplify implementation, `pogo` uses a YAML library to parse these. YAML is the most forgiving
of the widely-known formats, so it's reasonably easy to read+write.

In this context, we don't use the full YAML. There are no new-lines, multi-line content, indentation, 
anchors, or aliases.

Consequently, you should regard these inputs as a subset of YAML -- or, perhaps more precisely, consider
the inputs as JSON without the mandatory double-quotes. If the parsing library ever needs to be changed,
it will be checked against this subset - but may not be required to have full YAML compliance.
