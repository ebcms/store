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
            $plugin_json = Framework::getRoot() . '/plugin/' . $name . '/plugin.json';
            if (file_exists($plugin_json)) {
                $plugin = json_decode(file_get_contents($plugin_json), true);
                $param['version'] = $plugin['version'];
            }
            $res = $server->query('/check', $param);
            if (!$res['status']) {
                return $this->error($res['message']);
            }
            return $this->success($res['message']);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
