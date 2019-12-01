# Pogo and Composer

Internally, `pogo` uses `composer` to download the dependencies and store them in a hidden folder.
Understanding this mechanism may be useful if you are integrating with other development tooling (IDEs, debuggers, packagers, etc).


By default, `pogo` will put dependencies in `$HOME/.cache/pogo/<digest>`, where `<digest>` is a computed value
that depends on your list of requirements. You may tune the defaults with the `POGO_BASE` variable, e.g.

```bash
## Store builds in a shared folder
POGO_BASE=/var/cache/pogo

## Store builds in a dot-folder, adjacent to the executed script.
POGO_BASE=.
```

For a specific script, you may optionally exercise fine-grained control over the dependency
downloads, as in any of these:

```bash
## Download dependencies to a specific folder - and run the script.
pogo --run -d=<dep-dir> <script-file>

## Download dependencies to a specific folder.
pogo --get <script-file> -d=<dep-dir>

## Update dependencies in a previously downloaded folder
## Equivalent to re-running "pogo dl <same-script> -d=<same-output>"
cd <dep-dir>
pogo --up
```

