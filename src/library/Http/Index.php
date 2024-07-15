<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpomg\Admin\Http\Common;
use PHPOMG\Facade\Template;

class Index extends Common
{
    public function get()
    {
        return Template::render('index@ebcms/store');
    }
}
