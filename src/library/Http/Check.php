<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DiggPHP\Request\Request;
use DiggPHP\Framework\Framework;
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
            $json_file = Framework::getRoot() . '/plugin/' . $name . '/plugin.json';
            if (file_exists($json_file)) {
                $json = json_decode(file_get_contents($json_file), true);
                $param['version'] = $json['version'];
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
