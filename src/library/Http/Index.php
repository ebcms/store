<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpapp\Admin\Http\Common;
use PHPAPP\Facade\Template;

class Index extends Common
{
    public function get()
    {
        return Template::render('index@ebcms/store');
    }
}
