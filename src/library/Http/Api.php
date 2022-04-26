<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Traits\ResponseTrait;
use App\Ebcms\Admin\Traits\RestfulTrait;
use DiggPHP\Psr16\LocalAdapter;
use DiggPHP\Request\Request;

class Api
{
    use RestfulTrait;
    use ResponseTrait;

    public function post(
        Request $request,
        LocalAdapter $cache
    ) {
        if ($request->get('token') != $cache->get('storeapitoken')) {
            return $this->error('token校验失败！');
        }
        $cache->set('storesource', $request->post(), 10);
        return $this->success('success');
    }
}
