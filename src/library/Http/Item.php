<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Php94\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHP94\Facade\App;
use PHP94\Help\Response;
use PHP94\Help\Request;
use PHP94\Facade\Template;

class Item extends Common
{
    public function get()
    {
        $res = (new Server)->query('/detail', [
            'name' => Request::get('name'),
        ]);
        if ($res['error']) {
            return Response::error($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null, $res['error'] ?? 1);
        }
        return Template::render('item@ebcms/store', [
            'app' => $res['data'],
            'type' => App::isInstalled(Request::get('name')) ? 'update' : 'install',
        ]);
    }
}
