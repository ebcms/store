<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DigPHP\Framework\Framework;
use DigPHP\Request\Request;
use Throwable;

class Check extends Common
{
    public function get(
        Server $server,
        Request $request
    ) {
        try {
            $name = $request->get('name');
            $param = [
                'name' => $request->get('name'),
            ];
            $composer_file = Framework::getRoot() . '/app/' . $name . '/composer.json';
            if (file_exists($composer_file)) {
                $package = json_decode(file_get_contents($composer_file), true);
                $param['version'] = $package['version'];
            }
            $res = $server->query('/check', $param);
            if ($res['code']) {
                return $this->error($res['message'], $res['redirect_url'] ?? '', $res['code'], $res['data'] ?? null);
            }
            return $this->success($res['message'], $res['data'] ?? null);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
