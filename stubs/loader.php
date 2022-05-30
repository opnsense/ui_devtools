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

use Phalcon\Autoload\Loader as PhalconLoader5;
use Phalcon\Loader as PhalconLoader4;

if (!class_exists("PhalconLoader", false)) {
    if (class_exists("Phalcon\Autoload\Loader", false)) {
        class LoaderWrapper extends PhalconLoader5 {}
    } else {
        class LoaderWrapper extends PhalconLoader4 {}
    }

    class PhalconLoader extends LoaderWrapper
    {

        public function __call($fName, $args) {
            if (method_exists($this, $fName)) {
                return $this->fName(...$args);
            } elseif ($fName == 'setDirectories') {
                /* Phalcon5 renamed registerDirs to setDirectories */
                return $this->registerDirs(...$args);
            }
        }

    }
}

$loader = new PhalconLoader();

$loaderDirs = array();
foreach (array("controllersDir", "modelsDir", "libraryDir") as $topic) {
    foreach ($config->application->$topic as $path) {
        $loaderDirs[] = $path;
    }
}

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->setDirectories($loaderDirs)->register();
