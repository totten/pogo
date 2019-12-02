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
pogo --run -D=<dep-dir> <script-file>

## Download dependencies to a specific folder.
pogo --get -D=<dep-dir> <script-file>

## Update dependencies in a previously downloaded folder
## Equivalent to re-running "pogo --get -D=<same-dep-dir> <same-script-file>"
cd <dep-dir>
pogo --up
```
