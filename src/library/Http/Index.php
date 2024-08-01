<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Php94\Admin\Http\Common;
use PHP94\Facade\Template;

class Index extends Common
{
    public function get()
    {
        return Template::render('index@ebcms/store');
    }
}
