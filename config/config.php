<?php
/**
 *    Copyright (C) 2018 Deciso B.V.
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

use Phalcon\Config\Config as PhalconConfig5;
use Phalcon\Config as PhalconConfig4;

if (!class_exists("PhalconConfig", false)) {
    if (class_exists("Phalcon\Config\Config", false)) {
        class ConfigWrapper extends PhalconConfig5
        {
        }
    } else {
        class ConfigWrapper extends PhalconConfig4
        {
        }
    }
    class PhalconConfig extends ConfigWrapper
    {
    }
}


$conf = include __DIR__ . "/config.local.php";

ini_set('session.save_path',dirname(realpath(__FILE__)) . '/../temp');
if (empty(session_save_path())) {
    // force sessions to use temp when unset
    ini_set('session.save_path', sys_get_temp_dir());
}

// register all packages
$appcnf = [
    "docroot" => [
        preg_replace('#/+#','/',"{$_SERVER['DOCUMENT_ROOT']}/")
    ],
    "application" => []
];
$packages = [preg_replace('#/+#','/',"{$conf->environment->coreDir}/")];
foreach ($conf->environment->packages as $package) {
    $packages[] = $package;
}

foreach ($packages as $package) {
    $packageDirs = array(
        "controllersDir" => preg_replace('#/+#','/',"{$package}/src/opnsense/mvc/app/controllers/"),
        "modelsDir" => preg_replace('#/+#','/',"{$package}/src/opnsense/mvc/app/models/"),
        "viewsDir" => preg_replace('#/+#','/',"{$package}/src/opnsense/mvc/app/views/"),
        "libraryDir" => preg_replace('#/+#','/',"{$package}/src/opnsense/mvc/app/library/"),
        "docroot" => preg_replace('#/+#','/',"{$package}/src/opnsense/www/"),
        "contrib" => array(
            preg_replace('#/+#','/',"{$package}/src/opnsense/contrib/"),
            preg_replace('#/+#','/',"{$package}/contrib/")
        )
    );

    foreach ($packageDirs as $packageDir => $loc) {
        $locations = is_array($loc) ? $loc : array($loc);
        foreach ($locations as $location) {
            if (is_dir($location)) {
                if (!isset($conf->application->$packageDir) || !in_array($location,
                        $conf->application->$packageDir->toArray())) {
                    // merge configuration
                    if (!isset($appcnf["application"][$packageDir])) {
                        $appcnf["application"][$packageDir] = [];
                    }
                    $appcnf["application"][$packageDir][] = $location;
                }
            }
        }
    }
}
$conf->merge(new PhalconConfig($appcnf));

return $conf;