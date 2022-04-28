<?php

use App\Ebcms\Store\Model\Server;
use DiggPHP\Router\Router;
use DiggPHP\Framework\Framework;

return Framework::execute(function (
    Server $server,
    Router $router
): array {
    $res = [];

    if ($data = $server->query('/checks')) {
        if (!$data['code'] && $data['data']) {

            $bodys = [];
            foreach ($data['data'] as $name => $version) {
                $title = json_decode(file_get_contents(Framework::getRoot() . '/plugin/' . $name . '/plugin.json'), true)['title'];
                $bodys[] = '<a href="' . $router->build('/ebcms/store/item', ['name' => $name]) . '" class="mx-1 fw-bold">' . $title . '</a>';
            }

            $res[] = [
                'title' => '插件升级',
                'body' => '插件' . implode(',', $bodys) . '可升级~',
                'tags' => ['remind'],
            ];
        }
    }
    return $res;
});
