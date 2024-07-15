<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Help;

use ZipArchive;

class Zip extends ZipArchive
{
    public function addDirectory(string $dir, string $base): self
    {
        foreach (glob($dir . '/*') as $node) {
            if (is_dir($node)) {
                $this->addEmptyDir(substr($node, strlen($base)));
                $this->addDirectory($node, $base);
            } else {
                $this->addFile($node, substr($node, strlen($base)));
            }
        }
        return $this;
    }
}
