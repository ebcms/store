<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use Composer\InstalledVersions;
use DigPHP\Framework\Framework;
use DigPHP\Session\Session;
use Throwable;

class Install extends Common
{
    public function get(
        Session $session
    ) {
        try {
            $package = $session->get('package');
            $root_path = InstalledVersions::getRootPackage()['install_path'];

            $composer_file = $root_path . '/app/' . $package['name'] . '/composer.json';
            if (!is_file($composer_file)) {
                return $this->error('文件无效！');
            }
            $json = (array) json_decode(file_get_contents($composer_file), true);
            if (
                !isset($json['name']) ||
                $json['name'] != $package['name'] ||
                !isset($json['version']) ||
                $json['version'] != $package['version']
            ) {
                return $this->error('文件无效！');
            }

            $lock_file = $root_path . '/config/' . $package['name'] . '/install.lock';

            $class_name = str_replace(['-', '/'], ['', '\\'], ucwords('\\App\\' . $package['name'] . '\\App', '/\\-'));
            $action = is_file($lock_file) ? 'onUpdate' : 'onInstall';
            if (method_exists($class_name, $action)) {
                Framework::execute([$class_name, $action]);
            }

            if (is_file($package['extra']['tmpfile'])) {
                unlink($package['extra']['tmpfile']);
            }

            if (!is_dir(dirname($lock_file))) {
                mkdir(dirname($lock_file), 0755, true);
            }
            if (is_file(dirname($lock_file) . '/disabled.lock')) {
                unlink(dirname($lock_file) . '/disabled.lock');
            }
            file_put_contents($lock_file, $package['version']);

            $session->delete('package');

            return $this->success('安装成功!');
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
