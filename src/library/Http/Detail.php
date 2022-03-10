<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use DigPHP\Template\Template;

class Detail extends Common
{
    public function get(
        Template $template
    ) {
        return $template->renderFromFile('detail@ebcms/store');
    }
}
