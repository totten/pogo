# Pogo and Executables

When creating a PHP script, you can call `pogo`:

```bash
pogo my-script.php
```

Alternatively, you create an executable script:

```php
echo '#!/usr/bin/env pogo' > my-script
echo '<?php' >> my-script
echo 'echo "Hello world\n";' >> my-script
chmod +x my-script
```

Note the use of `--`. Options before the `--` will be handled by the `pogo`
pre-processor. Options after the `--` will be passed through to the target
script.
