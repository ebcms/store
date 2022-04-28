<?php

use DiggPHP\Router\Router;
use DiggPHP\Framework\Framework;

return Framework::execute(function (
    Router $router
): array {
    $res = [];
    $res[] = [
        'title' => '应用商店',
        'url' => $router->build('/ebcms/store/index'),
    ];
    return $res;
});
