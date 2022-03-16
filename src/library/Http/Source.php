<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DigPHP\Framework\Framework;
use DigPHP\Psr16\LocalAdapter;
use DigPHP\Request\Request;
use DigPHP\Router\Router;
use DigPHP\Session\Session;
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
            $composer_file = Framework::getRoot() . '/app/' . $name . '/composer.json';
            if (file_exists($composer_file)) {
                $package = json_decode(file_get_contents($composer_file), true);
                $param['version'] = $package['version'];
            }

            $res = $server->query('/source', $param);
            if ($res['code']) {
                return $this->error($res['message'], $res['redirect_url'] ?? '', $res['code']);
            }
            if (null === $package = $cache->get('storesource')) {
                return $this->error('超时，请重新操作~');
            }
            $session->set('package', $package);
            return $this->success($res['message']);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
