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
            $plugin = $session->get('plugin');
            $root_path = InstalledVersions::getRootPackage()['install_path'];
            $plugin_dir = $root_path . '/plugin/' . $plugin['name'];
            $this->delDir($plugin_dir);
            foreach ($plugin['backup_dirs'] as $dir) {
                if (is_file($root_path . $dir)) {
                    unlink($root_path . $dir);
                } elseif (is_dir($root_path . $dir)) {
                    $this->delDir($root_path . $dir);
                }
            }
            $this->copyDir($plugin['backup_path'], $root_path);
        } catch (Throwable $th) {
            return $this->error('还原失败：' . $th->getMessage());
        }
    }
}
