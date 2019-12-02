# Execution

There are two ways to run a PHP script with `pogo`. These are very similar to how one invokes `php`.

## Direct invocation

Suppose you've created a file `my-script.php`:

```php
<?php
echo "Hello world.\n";
```

To execute this with standard PHP, you would say:

```bash
php my-script.php
```

To execute this with `pogo`, you would say:

```bash
pogo my-script.php
```

## Executable files (indirect invocation)

Suppose you are creating an executable program `/usr/local/bin/my-script`.

The first line needs an instruction on how to execute the script.  For
standard PHP, the file `my-script` might be:

```php
#!/usr/bin/env php
<?php
echo "Hello world.\n";
```

To use pogo, simply change the first line:

```php
#!/usr/bin/env pogo
<?php
echo "Hello world.\n";
```

In either case, mark the file as executeable and run it:

```bash
chmod +x /usr/local/bin/my-script
my-script
```

## Interpreter options

Whether you call `pogo` directly or indirectly, it accepts a few options, such as:

* `--dl=<DIR>` - Explicitly store dependencies in the given directory
* `-f` - Forcibly download fresh dependencies, even the dependencies are currently available

For example, if you wanted to inspect or debug the dependencies, you might explicitly call:

```bash
pogo -f --dl=/tmp/depdebug my-script.php`
```

Similarly, suppose you have an executable in `/home/me/bin/my-script` and you want
to ensure that it places dependencies in `/home/me/src/my-script.dbg`. Set the
first line accordingly:

```bash
#!/usr/bin/env pogo --dl=/home/me/src/my-script.dbg
<?php
echo "Hello world\n";
```

For more details about `pogo` command-line options, run `pogo -h`.

## Script options

Additionally, scripts may receive command-line options. These work in the
same way that PHP normally handles them -- with the global `$argv` variable.
For example, suppose we have a script named `greeter`:

```php
#!/usr/bin/env pogo
<?php
printf("Hello %s!\n", $argv[1]);
```

You can pass arguments in either style:

```php
$ pogo greeter Alice
Hello Alice!
```

or:

```php
$ ./greeter Bob
Hello Bob!
```

Of course, if you accept a lot of options, then you'll want an easier way to parse `$argv`.  Fortunately, you can get a PHP
library for that!  One nice library is [mnapoli/silly](https://github.com/mnapoli/silly); you can just copy an example and
include the pragma:

```php
#!require mnapoli/silly: ~1.7
```

(Full example: [example/silly.php](//github.com/totten/pogo/blob/master/example/silly.php))
