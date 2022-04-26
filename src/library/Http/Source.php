<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DiggPHP\Psr16\LocalAdapter;
use DiggPHP\Request\Request;
use DiggPHP\Router\Router;
use DiggPHP\Session\Session;
use DiggPHP\Framework\Framework;
use Throwable;

class Source extends Common
{
    public function get(
        Request $request,
        Server $server,
        Router $router,
        LocalAdapter $cache,
        Session $session
    ) {
        try {
            $token = md5(uniqid());
            $cache->set('storeapitoken', $token, 30);
            $name = $request->get('name');
            $param = [
                'api' => $router->build('/ebcms/store/api', [
                    'token' => $token
                ]),
                'name' => $request->get('name'),
            ];
            $json_file = Framework::getRoot() . '/plugin/' . $name . '/plugin.json';
            if (file_exists($json_file)) {
                $json = json_decode(file_get_contents($json_file), true);
                $param['version'] = $json['version'];
            }

            $res = $server->query('/source', $param);
            if ($res['code']) {
                return $this->error($res['message'], $res['redirect_url'] ?? '', $res['code']);
            }
            if (null === $plugin = $cache->get('storesource')) {
                return $this->error('超时，请重新操作~');
            }
            $session->set('plugin', $plugin);
            return $this->success($res['message']);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
