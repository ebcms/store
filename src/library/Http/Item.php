<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DiggPHP\Request\Request;
use DiggPHP\Template\Template;

class Item extends Common
{
    public function get(
        Request $request,
        Server $server,
        Template $template
    ) {
        $data = [];
        $res = $server->query('/detail', [
            'name' => $request->get('name'),
        ]);
        if ($res['code']) {
            return $this->error($res['message'], $res['redirect_url'] ?? '', $res['code']);
        }
        $data['plugin'] = $res['data'];
        $data['type'] = 'install';
        $installed = $server->getInstalled();
        if (isset($installed[$request->get('name')])) {
            $data['type'] = 'upgrade';
        } else {
            $data['type'] = 'install';
        }
        return $template->renderFromFile('item@ebcms/store', $data);
    }
}
