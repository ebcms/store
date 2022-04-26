<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Admin\Http\Common;
use Composer\InstalledVersions;
use DiggPHP\Session\Session;
use DiggPHP\Framework\Framework;
use Throwable;

class Install extends Common
{
    public function get(
        Session $session
    ) {
        try {
            $plugin = $session->get('plugin');
            $root_path = InstalledVersions::getRootPackage()['install_path'];

            $json_file = $root_path . '/plugin/' . $plugin['name'] . '/plugin.json';
            if (!is_file($json_file)) {
                return $this->error('文件无效！');
            }
            $json = (array) json_decode(file_get_contents($json_file), true);
            if (
                !isset($json['name']) ||
                $json['name'] != $plugin['name'] ||
                !isset($json['version']) ||
                $json['version'] != $plugin['version']
            ) {
                return $this->error('文件无效！');
            }

            $lock_file = $root_path . '/config/plugin/' . $plugin['name'] . '/install.lock';

            $class_name = str_replace(['-', '/'], ['', '\\'], ucwords('\\App\\' . $plugin['name'] . '\\App', '/\\-'));
            $action = is_file($lock_file) ? 'onUpdate' : 'onInstall';
            if (method_exists($class_name, $action)) {
                Framework::execute([$class_name, $action]);
            }

            if (is_file($plugin['tmpfile'])) {
                unlink($plugin['tmpfile']);
            }

            if (!is_dir(dirname($lock_file))) {
                mkdir(dirname($lock_file), 0755, true);
            }
            if (is_file(dirname($lock_file) . '/disabled.lock')) {
                unlink(dirname($lock_file) . '/disabled.lock');
            }
            file_put_contents($lock_file, $plugin['version']);

            $session->delete('plugin');

            return $this->success('安装成功!');
        } catch (Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
