<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Traits\DirTrait;
use Composer\InstalledVersions;
use DigPHP\Session\Session;
use Throwable;

class Rollback extends Common
{
    use DirTrait;

    public function get(
        Session $session
    ) {
        try {
            $package = $session->get('package');
            $root_path = InstalledVersions::getRootPackage()['install_path'];
            $this->delDir($root_path . '/app/' . $package['name']);
            foreach ($package['backup_dirs'] as $dir) {
                if (is_file($root_path . $dir)) {
                    unlink($root_path . $dir);
                } elseif (is_dir($root_path . $dir)) {
                    $this->delDir($root_path . $dir);
                }
            }
            $this->copyDir($package['backup_path'], $root_path);
        } catch (Throwable $th) {
            return $this->error('还原失败：' . $th->getMessage());
        }
    }
}
