<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpapp\Admin\Http\Common;
use App\Ebcms\Store\Help\Server;
use PHPAPP\Facade\App;
use PHPAPP\Help\Response;
use PHPAPP\Help\Request;
use PHPAPP\Facade\Template;

class Item extends Common
{
    public function get()
    {
        $res = (new Server)->query('/detail', [
            'name' => Request::get('name'),
        ]);
        if (!$res['status']) {
            return Response::failure($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null);
        }
        return Template::render('item@ebcms/store', [
            'app' => $res['data'],
            'type' => App::isInstalled(Request::get('name')) ? 'update' : 'install',
        ]);
    }
}
