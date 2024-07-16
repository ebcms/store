<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Store\Help\Zip;
use App\Phpapp\Admin\Http\Common;
use Composer\Autoload\ClassLoader;
use PHPAPP\Facade\Session;
use PHPAPP\Help\Response;
use ReflectionClass;
use Throwable;
use ZipArchive;

class Backup extends Common
{
    public function get()
    {
        try {
            $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
            $appitem = Session::get('appitem');
            $appitem['backup_file'] = $root . '/backup/app/' . $appitem['name'] . '/' . date('YmdHis') . '.zip';
            if (!is_dir(dirname($appitem['backup_file']))) {
                if (!mkdir(dirname($appitem['backup_file']), 0755, true)) {
                    return Response::success('创建备份目录失败');
                }
            }
            $zip = new Zip;
            $zip->open($appitem['backup_file'], ZipArchive::CREATE);
            if (is_dir($root . '/app/' . $appitem['name'])) {
                $zip->addDirectory($root . '/app/' . $appitem['name'], $root . '/app/' . $appitem['name'] . '/');
            }
            $zip->close();

            Session::set('appitem', $appitem);
            return Response::success('备份成功！', $appitem);
        } catch (Throwable $th) {
            return Response::failure($th->getMessage());
        }
    }
}
