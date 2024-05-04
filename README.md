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

As of this writing OPNsense uses php *8.2.x* and Phalcon *5.6.x*.

In theory this approach should function on both unix like machines and Windows.

Prepare machine (OSX)
======================
When using homebrew (https://brew.sh/) on OSX, the easiest option to get you started is by executing
the following commands:

```
brew install php@8.2
brew tap phalcon/extension https://github.com/opnsense/homebrew-tap
brew install phalcon
```


Setup
===========

Clone this repository and copy `config/config.local.php.sample` to
`config/config.local.php` then fill in the required parameters as noted in the
configuration sample.

Normally the only relevant configuration section is the `environment` section, which needs
absolute paths to both the OPNsense core files and the plugins you wish to expose.

For example, using the default build directories:

```
    $ui_core_dir = '/usr/core';
    require_once rtrim($ui_core_dir,'/') . '/src/opnsense/mvc/app/config/AppConfig.php';
    'environment' => [
        /* packages to include in setup */
        'packages'      => [
            '/usr/plugins/security/tinc'
        ],
        /* location of OPNsense core package */
        'coreDir'        => $ui_core_dir,
    ]
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


Using configd locally [*nix only]
========================================

To test drive template generation and command execution, it can be practical to have a configd instance running as well on your local machine.

In most cases paths will differ on a development machine, but being able to generate templates and execute commands may help the development process.

The documentation for configd itself can be found [here](https://docs.opnsense.org/development/backend.html)

First step is to copy all the files in our service directory to your development location (the example below assumes current working directory):
```
rsync -avz /<path_to_core>/src/opnsense/service/* configd
cd configd
mkdir tmp
```

Next edit *conf/configd.conf* and change pid and socket location to something writeable from the current user.

To control where the templates are generated and which config it will use, you can edit the template configuration and change the config and root attributes in all sections

In *conf/actions.d/actions_template.conf* change:

*  config -->  to our config to use
*  root --> where to write our template output

Finally let's spin up the configd process in a console (install missing python plugins using pip, when you are unable to start the process):
```
python configd.py console
```

Symlink the socket to the expected location (which OPNsense uses) so both our UI and command
line tools can reach it:
```
sudo ln -s /<install location>/configd/tmp/configd.socket /var/run/configd.socket
```


And check it's status in a new console, using:
```
python configd_ctl.py configd actions
```

Which should display a list of registered commands.

Finally make sure you disable **simulate_mode** in the **config.local.php** of your
local server (restart to apply changes).
