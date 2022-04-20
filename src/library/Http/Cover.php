<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Traits\DirTrait;
use Composer\InstalledVersions;
use DigPHP\Session\Session;
use Exception;
use Throwable;
use ZipArchive;

class Cover extends Common
{
    use DirTrait;

    public function get(
        Session $session
    ) {
        try {
            $plugin = $session->get('plugin');
            $root_path = InstalledVersions::getRootPackage()['install_path'];
            $plulgin_dir = $root_path . '/plugin/' . $plugin['name'];
            $this->delDir($plulgin_dir);
            $this->unZip($plugin['tmpfile'], $plulgin_dir);
            return $this->success('文件更新成功!');
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    private function unZip($file, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            throw new Exception('Could not open archive');
        }
        if (true !== $zip->extractTo($destination)) {
            throw new Exception('Could not extractTo ' . $destination);
        }
        if (true !== $zip->close()) {
            throw new Exception('Could not close archive ' . $file);
        }
    }
}
