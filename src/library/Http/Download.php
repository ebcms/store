<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Traits\DirTrait;
use DigPHP\Session\Session;
use Throwable;

class Download extends Common
{
    use DirTrait;

    public function get(
        Session $session
    ) {
        try {
            $package = $session->get('package');
            if (false === $content = file_get_contents($package['source'], false, stream_context_create([
                'http' => [
                    'method' => "GET",
                    'timeout' => 10,
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]))) {
                return $this->error('文件下载失败~');
            }

            if (md5($content) != $package['md5']) {
                return $this->error('文件校验失败！');
            }

            $tmpfile = tempnam(sys_get_temp_dir(), 'storeinstall');
            if (false == file_put_contents($tmpfile, $content)) {
                return $this->error('文件(' . $tmpfile . ')写入失败，请检查权限~');
            }
            $package['tmpfile'] = $tmpfile;
            $session->set('package', $package);
            return $this->success('下载成功！', $package);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
