<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Phpapp\Admin\Http\Common;
use PHPAPP\Help\Response;
use Composer\Autoload\ClassLoader;
use PHPAPP\Facade\App;
use PHPAPP\Facade\Framework;
use PHPAPP\Facade\Session;
use ReflectionClass;
use Throwable;

class Install extends Common
{
    public function get()
    {
        try {
            $appitem = Session::get('appitem');
            $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);

            $packagefile = App::getDir($appitem['name']) . '/src/config/package.php';
            $actiontype = App::isInstalled($appitem['name']) ? 'update' : 'install';
            if ($fn = $this->getFn($packagefile, $actiontype)) {
                Framework::execute($fn);
            }

            if (is_file($appitem['tmpfile'])) {
                unlink($appitem['tmpfile']);
            }

            $lock_file = $root . '/config/' . $appitem['name'] . '/install.lock';
            if (!is_dir(dirname($lock_file))) {
                mkdir(dirname($lock_file), 0755, true);
            }
            if (is_file(dirname($lock_file) . '/disabled.lock')) {
                unlink(dirname($lock_file) . '/disabled.lock');
            }
            file_put_contents($lock_file, json_encode($appitem, JSON_UNESCAPED_UNICODE));

            Session::delete('appitem');

            return Response::success('安装成功!');
        } catch (Throwable $th) {
            return Response::failure($th->getMessage());
        }
    }

    private function getFn(string $file, string $fnname): ?callable
    {
        if (file_exists($file)) {
            $x = include $file;
            if (isset($x[$fnname])) {
                return $x[$fnname];
            }
        }
        return null;
    }
}
