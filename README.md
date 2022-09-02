PHP Nmap
====

A PHP wrapper for [Nmap](http://nmap.org/), a free security scanner for network exploration. Forked from the original script by William Durrand (https://github.com/willdurand/nmap); updated to work with the latest version of Symfony and will be expanded to take advantage of all Nmap features in time.

Usage
-----
use Nmap\Nmap;

```php
$hosts = Nmap::create()->scan([ 'scanme.nmap.org' ]);

$ports = $hosts->getOpenPorts();
```

You can specify the ports you want to scan:

``` php
$nmap = new Nmap();

$nmap->scan([ 'scanme.nmap.org' ], [ 21, 22, 80 ]);
```

**OS detection** and **Service Info** are disabled by default, if you want to
enable them, use the `enableOsDetection()` and/or `enableServiceInfo()` methods:

``` php
$nmap
    ->enableOsDetection()
    ->scan([ 'scanme.nmap.org' ]);

$nmap
    ->enableServiceInfo()
    ->scan([ 'scanme.nmap.org' ]);

// Fluent interface!
$nmap
    ->enableOsDetection()
    ->enableServiceInfo()
    ->scan([ 'scanme.nmap.org' ]);
```

Turn on the **verbose mode** by using the `enableVerbose()` method:

``` php
$nmap
    ->enableVerbose()
    ->scan([ 'scanme.nmap.org' ]);
```

For some reasons, you might want to disable port scan, that is why **nmap**
provides a `disablePortScan()` method:

``` php
$nmap
    ->disablePortScan()
    ->scan([ 'scanme.nmap.org' ]);
```

You can also disable the reverse DNS resolution with `disableReverseDNS()`:

``` php
$nmap
    ->disableReverseDNS()
    ->scan([ 'scanme.nmap.org' ]);
```

You can define the process timeout (default to 60 seconds) with `setTimeout()`:

``` php
$nmap
    ->setTimeout(120)
    ->scan([ 'scanme.nmap.org' ]);
```

Installation
------------

The recommended way to install nmap is through
[Composer](http://getcomposer.org/):

```json
{
    "require": {
        "cultrix/nmap": "@master"
    }
}
```

Or:

`composer require cultrix/nmap`

License
-------

nmap is released under the MIT License. See the bundled LICENSE file for
details.
