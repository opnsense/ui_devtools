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

function error_output($http_code, $e,  $user_message)
{
    $response = [];
    if (OPNsense\Core\Config::getInstance()->object()->system->deployment != 'development'){
        $response['errorMessage'] = $user_message;
    } else {
        $response['errorMessage'] = $e->getMessage();
        $response['errorTrace'] = $e->getTraceAsString();
    }
    if (method_exists($e, 'getTitle')) {
        $response['errorTitle'] = $e->getTitle();
    }
    header('HTTP', true, $http_code);
    header("Content-Type: application/json;charset=utf-8");
    echo htmlspecialchars(json_encode($response), ENT_NOQUOTES);
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

    $router = new OPNsense\Mvc\Router('/api/', 'Api');
    $response = $router->routeRequest($_SERVER['REQUEST_URI']);
    if (!$response->isSent()) {
        $response->send();
    }
} catch (\OPNsense\Base\UserException $e) {
    error_output(500, $e, $e->getMessage());
} catch (
    \OPNsense\Mvc\Exceptions\ClassNotFoundException |
    \OPNsense\Mvc\Exceptions\MethodNotFoundException |
    \OPNsense\Mvc\Exceptions\ParameterMismatchException |
    \OPNsense\Mvc\Exceptions\InvalidUriException $e
) {
    error_output(404, $e, gettext('Endpoint not found'));
} catch (\Error | \Exception $e) {
    error_output(500, $e, gettext('Unexpected error, check log for details'));
    error_log($e);
}


