<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpapp\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHPAPP\Facade\App;
use PHPAPP\Help\Response;
use PHPAPP\Help\Request;
use Throwable;

class Check extends Common
{
    public function get()
    {
        try {
            if (App::isCore(Request::get('name'))) {
                return Response::failure('核心项目，请通过composer更新');
            }
            $res = (new Server)->query('/check', [
                'name' => Request::get('name'),
            ]);
            if (!$res['status']) {
                return Response::failure($res['message'], $res['redirect_url'] ?? null, $res['data'] ?? null);
            } else {
                return Response::success($res['message'], $res['redirect_url'] ?? null, $res['data'] ?? null);
            }
        } catch (Throwable $th) {
            return Response::failure($th->getMessage());
        }
    }
}
