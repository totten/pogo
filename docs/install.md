# Requirements

* PHP 7.x / 5.6
* `composer`

# Install (PHAR, Unix)

* Navigate to the [Github: pogo: Releases](https://github.com/totten/pogo/releases)
* Download the latest PHAR
* Move it in your path (e.g. `sudo mv pogo-0.1.0.phar /usr/local/bin/pogo`)
* Mark it executable (`chmod +x /usr/local/bin/pogo`)

# Install (Git, Unix)

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
