# ui_devtools
Support tools to ease local frontend development using the built-in
php webserver.

The main goal of this repository is to allow user interface development without the need
for an actual OPNsense deployment, which eases the process of designing frontend modules.

Debugging (using xdebug) is supported, although keep in mind that this will slow down all requests as
the static pages are also delivered using the php interpreter.

Requirements
============

Make sure you have the same php (http://www.php.net/) and phalcon (https://phalconphp.com/) versions installed on the target to
which you would like to deploy the test server.

As of this writing OPNsense uses php *7.1.x* and Phalcon *3.3.x*.

In theory this approach should function on both unix like machines and Windows.

Setup
===========

Clone this repository and copy `config/config.local.php.sample` to
`config/config.local.php` then fill in the required parameters as noted in the
configuration sample.

Normally the only relevant configuration section is the `environment` section, which needs
absolute paths to both the OPNsense core files and the plugins you wish to expose.

For example, using the default build directories:

```
    'environment' => array(
        /* packages to include in setup */
        'packages'      => array(
            '/usr/plugins/security/tinc'
        ),
        /* location of OPNsense core package */
        'coreDir'        => '/usr/core',
    )
```

All working directories are pointed to the local directory where this
repository is checked out, to minimize the dependencies.


Startup
=======

Startup the local server, which listens to port 8000 on localhost
```
php run_server.php
```

Finally point your browser to http://localhost:8000/ and test your ui software.
