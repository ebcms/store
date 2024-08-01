<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Store\Help\Curl;
use App\Php94\Admin\Http\Common;
use PHP94\Help\Response;
use PHP94\Facade\Session;
use Throwable;

class Download extends Common
{
    public function get()
    {
        try {
            $appitem = Session::get('appitem');
            if (false === $content = Curl::get($appitem['source'])) {
                return Response::error('文件下载失败~');
            }

            if (md5($content) != $appitem['md5']) {
                return Response::error('文件校验失败！');
            }

            $tmpfile = tempnam(sys_get_temp_dir(), 'appinstall');
            if (false === file_put_contents($tmpfile, $content)) {
                return Response::error('文件(' . $tmpfile . ')写入失败，请检查权限~');
            }
            $appitem['tmpfile'] = $tmpfile;
            Session::set('appitem', $appitem);
            return Response::success('下载成功！', null, $appitem);
        } catch (Throwable $th) {
            return Response::error($th->getMessage());
        }
    }
}
