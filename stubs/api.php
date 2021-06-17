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
    include $config->environment->coreDir . "/src/opnsense/mvc/app/config/services_api.php";

    /**
     * local webserver might have moved Authorization header, move it back
     */
    if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = "Basic " .base64_encode($_SERVER['PHP_AUTH_USER'].":".$_SERVER['PHP_AUTH_PW']);
    }

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);
    echo $application->handle($_SERVER['REQUEST_URI'])->getContent();
} catch (\Exception $e) {
    $response = array();
    $response['errorMessage'] = $e->getMessage();
    if (method_exists($e, 'getTitle')) {
        $response['errorTitle'] = $e->getTitle();
    } else {
        $response['errorTitle'] = gettext("An API exception occured");
    }
    header('HTTP', true, 500);
    header("Content-Type: application/json;charset=utf-8");
    echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
    error_log($e);
}
