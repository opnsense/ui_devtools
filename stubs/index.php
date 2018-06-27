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
error_reporting(E_ALL);

/**
 * search for a themed filename or return distribution standard
 * @param string $url relative url
 * @param array $theme theme name
 * @return string
 */
function view_fetch_themed_filename($url, $theme)
{
    $search_pattern = array(
        "/themes/{$theme}/build/",
        "/"
    );
    foreach ($search_pattern as $pattern) {
        foreach (\Phalcon\DI\FactoryDefault::getDefault()->get('config')->application->docroot as $path) {
            $filename = "{$path}{$pattern}{$url}";
            if (file_exists($filename)) {
                return str_replace("//", "/", "/ui{$pattern}{$url}");
            }
        }
    }
    return $url; // not found, return source
}

/**
 * search for a themed filename or return distribution standard
 * @param string $url relative url
 * @param array $theme theme name
 * @return string
 */
function view_file_exists($filename)
{
    foreach (\Phalcon\DI\FactoryDefault::getDefault()->get('config')->application->docroot as $path) {
        // check registered document roots for existence of $filename
        $root_dir = "/usr/local/opnsense/www/";
        if (strpos($filename, $root_dir) === 0) {
            $check_filename = $path . substr($filename, strlen($root_dir));
            if (file_exists($check_filename)){
                return true;
            }
        }
    }
    return file_exists($filename);
}

try {
    /**
     * Read the configuration
     */
    $config = include __DIR__ . "/../config/config.php";

    /**
     * Read auto-loader
     */
    include __DIR__ . "/loader.php";

    /**
     * Read services
     */
    include $config->environment->coreDir . "/src/opnsense/mvc/app/config/services.php";

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    // always flush caches for local testing
    (new \OPNsense\Base\Menu\MenuSystem())->invalidateCache();
    (new \OPNsense\Core\ACL())->invalidateCache();
    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage();
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
