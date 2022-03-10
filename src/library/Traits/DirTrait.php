<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Traits;

use Exception;

trait DirTrait
{

    final public function delDir($dir)
    {
        if (is_dir($dir)) {
            if (false === $dh = opendir($dir)) {
                throw new Exception('打开目录失败（' . $dir . '）');
            }
            while ($file = readdir($dh)) {
                if (false === $file) {
                    throw new Exception('读取目录失败（' . $dir . '）');
                }
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $fullpath = $dir . '/' . $file;
                if (!is_dir($fullpath)) {
                    if (false === unlink($fullpath)) {
                        throw new Exception('删除失败（' . $fullpath . '）');
                    }
                } else {
                    $this->delDir($fullpath);
                }
            }
            closedir($dh);
            if (false === rmdir($dir)) {
                throw new Exception('删除失败（' . $dir . '）');
            }
        }
    }

    final public function copyDir($source, $dest)
    {
        if (!file_exists($dest)) {
            if (false === mkdir($dest, 0755, true)) {
                throw new Exception('目录创建失败（' . $dest . '）');
            }
        };
        if (false === $handle = opendir($source)) {
            throw new Exception('目录打开失败（' . $source . '）');
        }
        while ($item = readdir($handle)) {
            if (false === $item) {
                throw new Exception('读取目录失败（' . $source . '）');
            }
            if ($item == '.' || $item == '..') {
                continue;
            };
            $_source = $source . '/' . $item;
            $_dest = $dest . '/' . $item;
            if (is_file($_source)) {
                if (false === copy($_source, $_dest)) {
                    throw new Exception('Copy失败（from:' . $_source . ' to:' . $_dest . '），请检查权限~');
                }
            };
            if (is_dir($_source)) {
                $this->copyDir($_source, $_dest);
            };
        }
        closedir($handle);
    }
}
