<?php
/**
 *    Copyright (C) 2018 Deciso B.V.
 *    Copyright (C) 2018 Fabian Franz
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
// construct "run server" command
$run_command = array();
$run_command[] = PHP_BINARY;

// enable xdebug when requested
if (in_array('-d', $argv)) {
    $run_command[] = "-d xdebug.remote_autostart=1";
    $run_command[] = "-d xdebug.remote_enable=1";
    $run_command[] = "-d xdebug.remote_host=localhost";
    $run_command[] = "-d xdebug.remote_port=9000";
    $run_command[] = "-d xdebug.remote_handler=dbgp";
}

$config = include __DIR__ . "/config/config.php";


// copy default config when there is no config found
if (!is_file("{$config->globals->config_path}/config.xml")) {
    copy("{$config->environment->coreDir}/src/etc/config.xml.sample", "{$config->globals->config_path}/config.xml");
}

// gather php include paths and add to run command
$include_paths = array();
foreach ($conf->application->contrib as $include) {
    if (is_dir($include)) {
        $include_paths[] = trim($include);
    }
}
if (PHP_OS == 'WINNT') {
    // include paths on windows differ
    $run_command[] = '-d include_path=".;' . implode(';', $include_paths) . '"';
} else {
    $run_command[] = '-d include_path=".:' . implode(':', $include_paths) . '"';
}

$run_command[] = "-d open_basedir=";

// listen to localhost
$run_command[] = "-S localhost:8000";

// set document root
$run_command[] = str_replace('//', '/', "-t {$config->environment->coreDir}/src/opnsense/www");

// .htaccess alternative routing
copy(__DIR__ . '/public/.htrouter.php', "{$config->environment->coreDir}/src/opnsense/www/.htrouter.php");
$run_command[] = ".htrouter.php";

// set our working directory in the php environment in which the server runs
putenv("DEV_WORKDIR=".__DIR__);

// show executed command
$cmd_action = implode(' ', $run_command) ;
echo "{$cmd_action}\n";

chdir("{$config->environment->coreDir}/src/opnsense/www");
exec($cmd_action);
