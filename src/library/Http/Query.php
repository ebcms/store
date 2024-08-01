<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Php94\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHP94\Help\Response;
use PHP94\Help\Request;

class Query extends Common
{
    public function get()
    {
        $server = new Server;
        $res = $server->query('/' . Request::get('api'), (array) Request::get('params'));
        if ($res['error']) {
            return Response::error($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null, $res['error'] ?? 1);
        } else {
            return Response::success('获取成功', $res['redirect_url'] ?? '', $res['data'] ?? null);
        }
    }
}
