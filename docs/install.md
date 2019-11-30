# Requirements

* PHP
* `composer`
* (Only tested in Linux/OS X)

# Download / Install

For the moment:

```bash
git clone https://github.com/totten/pogo
cd pogo
composer install
export PATH=$PWD/bin:$PATH
```

Optionally, instead of updating the `PATH`, you can use
[`box`](http://box-project.github.io/box2/) to create a PHAR and copy it to
your `bin` folder:

```bash
$ git clone https://github.com/totten/pogo && cd pogo && composer install
$ which box
/usr/local/bin/box
$ php -dphar.readonly=0 /usr/local/bin/box build
$ sudo cp bin/pogo.phar /usr/local/bin/pogo
```

