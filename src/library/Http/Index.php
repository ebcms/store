<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Model\Server;
use DigPHP\Template\Template;

class Index extends Common
{
    public function get(
        Template $template,
        Server $server
    ) {
        return $template->renderFromFile('index@ebcms/store', [
            'installed' => $server->getInstalled()
        ]);
    }
}
