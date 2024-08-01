<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Php94\Admin\Http\Common;
use Composer\Autoload\ClassLoader;
use Exception;
use PHP94\Facade\Session;
use PHP94\Help\Response;
use ReflectionClass;
use Throwable;
use ZipArchive;

class Rollback extends Common
{

    public function get()
    {
        try {
            $appitem = Session::get('appitem');
            $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
            $appdir = $root . '/app/' . $appitem['name'];
            if (!$this->delDir($appdir)) {
                return Response::error('部分目录或文件无法删除，无法回滚');
            }
            if (!mkdir($appdir, 0755, true)) {
                return Response::error('无法创建应用目录，无法回滚');
            }
            $this->unZip($appitem['backup_file'], $appdir);
            return Response::success('回滚成功!');
        } catch (Throwable $th) {
            return Response::error('还原失败：' . $th->getMessage());
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

    private function delDir(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->delDir($path);
            } else {
                if (!unlink($path)) {
                    return false;
                }
            }
        }
        return rmdir($dir);
    }
}
