<?php
/**
 *    Copyright (C) 2018-2022 Deciso B.V.
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
        foreach ((new OPNsense\Core\AppConfig())->application->docroot as $path) {
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
    foreach ((new OPNsense\Core\AppConfig())->application->docroot as $path) {
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

/**
 * return appended version string with a hash for proper caching for currently installed version
 * @param string $url to make cache-safe
 * @return string
 */
function view_cache_safe($url)
{
    return "{$url}?v=" . uniqid();
}

/**
 * return safe HTML encoded version of input string
 * @param string $text to make HTML safe
 * @return string
 */
function view_html_safe($text)
{
    /* gettext() embedded in JavaScript can cause syntax errors */
    return str_replace("\n", '&#10;', htmlspecialchars($text ?? '', ENT_QUOTES | ENT_HTML401));
}


try {
    $config = include __DIR__ . "/../config/config.php";
    include __DIR__ . "/loader.php";

    /**
     * local webserver might have moved Authorization header, move it back
     */
    if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Basic " .base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']);
    }

    $router = new OPNsense\Mvc\Router('/ui/');

    // always flush caches for local testing
    (new \OPNsense\Base\Menu\MenuSystem())->invalidateCache();
    (new \OPNsense\Core\ACL())->invalidateCache();
    $response = $router->routeRequest($_SERVER['REQUEST_URI'],[
        'controller' => 'indexController',
        'action' => 'indexAction'
    ]);
    if (!$response->isSent()) {
        $response->send();
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
