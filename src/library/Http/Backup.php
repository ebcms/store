<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\Store\Traits\DirTrait;
use Composer\InstalledVersions;
use DigPHP\Session\Session;
use Exception;
use Throwable;

class Backup extends Common
{
    use DirTrait;

    public function get(
        Session $session
    ) {
        try {
            $root_path = InstalledVersions::getRootPackage()['install_path'];
            $package = $session->get('package');
            $package['extra']['backup_path'] = $root_path . '/backup/' . date('YmdHis');
            $package['extra']['backup_dirs'] = [
                'app/', // todo
            ];
            $this->backup($package['extra']['backup_dirs'], $root_path, $package['extra']['backup_path']);
            $session->set('package', $package);
            return $this->success('备份成功！', $package);
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    private function backup(array $items, string $path, string $target)
    {
        foreach ($items as $item) {
            if (is_file($path . '/' . $item)) {
                if (!is_dir(dirname($target . '/' . $item))) {
                    if (false === mkdir(dirname($target . '/' . $item), 0755, true)) {
                        throw new Exception('创建目录（' . dirname($target . '/' . $item) . '）失败，请检查权限~');
                    }
                }
                if (false === copy($path . '/' . $item, $target . '/' . $item)) {
                    throw new Exception('Copy失败（from:' . $path . '/' . $item . ' to:' . $target . '/' . $item . '），请检查权限~');
                }
            } else {
                $this->copyDir($path . '/' . $item, $target . '/' . $item);
            }
        }
    }
}
