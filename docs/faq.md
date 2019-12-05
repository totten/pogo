# FAQ

### The examples only show CLI apps. What about web apps?

I've only thought about CLI apps.

For web-apps, you may want to divide the world into:

* Traditional PHP environments -- nginx/apache plus php-fpm/php-cgi, etc
* Async PHP environments -- [amphp](https://amphp.org/amp/), [reactphp](https://reactphp.org/), etc

You may find it easier to use `pogo` for micro-services in async environments, as in [examples/httpd-react.php](https://github.com/totten/pogo/blob/master/examples/httpd-react.php)
(*trivially adapted from [reactphp/http:01-hello-world.php](https://github.com/reactphp/http/blob/v0.8.5/examples/01-hello-world.php)*).

### The examples are mostly single-page scripts/apps. Does it work with includes?

Yes... `include`, `require`, `include_once`, and `require_once` still work.

But no... dependencies are not determined transitively through included files. The
scanner currently has no specific support for multi-file projects.

Note: For the main script, the content of `__DIR__` will depend in on the runner. Use `pogo_script_dir()` if you need a consistent value across all runners.

### Why does it redownload dependencies after a week?

That's a security precaution - to make sure we don't run stale libraries.

There are a few things you can use to mitigate this:

1. When calling `pogo`, set the `--allow-stale` option.
2. Configure the ["ttl" pragma](/docs/pragmas.md) with a very long TTL.
3. Work on a patch to generate statically-linked PHARs (e.g. `pogo --compile --out=/usr/local/bin/my-script my-script.php`).

### How does it cleanup old (previously-downloaded) dependencies?

Welcome to v0! It doesn't clean them up.

You can just `rm -rf ~/.cache/pogo` periodically.  Or maybe use `find ... -delete` to delete things after a certain number of days.

We should add a command (e.g. `pogo --cleanup`) for that which respects the `ttl` pragma.

### Are my scripts locked into a funky format?

Nope. If a script gets long and you want to migrate it to a full-blown `composer`
package, then you can use `pogo` to export a `composer.json`, e.g.

```bash
## Generate a folder with 'composer.json' and 'vendor'
pogo --get --dl=$HOME/src/new-project /path/to/old-script.php

## Copy the script into the project folder
cp /path/to/my-script.php $HOME/src/new-project/new-script.php

## Add the autoloader - at the top of the file, insert `require_once __DIR__ . '/vendor/autoload.php';`
vi $HOME/src/new-project/new-script.php

## Setup git for the new project
git init
echo /vendor/ > .gitignore
git add .
git commit
```

Then refactor to taste.
