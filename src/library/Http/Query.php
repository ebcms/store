<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpapp\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHPAPP\Help\Response;
use PHPAPP\Help\Request;

class Query extends Common
{
    public function get()
    {
        $server = new Server;
        $res = $server->query('/' . Request::get('api'), (array) Request::get('params'));
        if (!$res['status']) {
            return Response::failure($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null);
        } else {
            return Response::success('获取成功', $res['redirect_url'] ?? '', $res['data'] ?? null);
        }
    }
}
