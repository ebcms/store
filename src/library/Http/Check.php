<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Php94\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHP94\Facade\App;
use PHP94\Help\Response;
use PHP94\Help\Request;
use Throwable;

class Check extends Common
{
    public function get()
    {
        try {
            if (App::isCore(Request::get('name'))) {
                return Response::error('核心项目，请通过composer更新');
            }
            $res = (new Server)->query('/check', [
                'name' => Request::get('name'),
            ]);
            if ($res['error']) {
                return Response::error($res['message'], $res['redirect_url'] ?? null, $res['data'] ?? null, $res['error'] ?? 1);
            } else {
                return Response::success($res['message'], $res['redirect_url'] ?? null, $res['data'] ?? null);
            }
        } catch (Throwable $th) {
            return Response::error($th->getMessage());
        }
    }
}
