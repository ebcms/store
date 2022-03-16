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
            $package = $session->get('package');
            $root_path = InstalledVersions::getRootPackage()['install_path'];
            $package_dir = $root_path . '/app/' . $package['name'];
            $this->delDir($package_dir);
            $this->unZip($package['extra']['tmpfile'], $package_dir);
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
