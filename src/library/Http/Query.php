<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DiggPHP\Request\Request;

class Query extends Common
{
    public function get(
        Request $request,
        Server $server
    ) {
        $res = $server->query('/' . $request->get('api'), (array) $request->get('params'));
        if ($res['code']) {
            return $this->error($res['message'], $res['redirect_url'] ?? '', $res['code']);
        } else {
            return $this->success('获取成功', $res['data']);
        }
    }
}
