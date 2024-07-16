<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Help;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Exception;
use PHPAPP\Facade\App;
use ReflectionClass;
use Throwable;

class Server
{

    private $api = 'https://www.ebcms.com/ebcms/store-server/api';

    public function setApi(string $api): self
    {
        $this->api = $api;
        return $this;
    }

    public function query(string $path, array $param = []): array
    {
        try {
            $response = $this->post($this->api . $path, array_merge($param, $this->getCommonParam()));
            $res = (array) json_decode($response, true);
            if (!isset($res['status'])) {
                return [
                    'status' => 0,
                    'message' => '错误：服务器无效响应！',
                ];
            }
            return $res;
        } catch (Throwable $th) {
            return [
                'status' => 0,
                'message' => '错误：' . $th->getMessage(),
            ];
        }
    }

    private function getCommonParam(): array
    {
        $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
        $composer_file = $root . '/composer.json';
        if (!file_exists($composer_file)) {
            throw new Exception('根目录下composer.json文件不存在，请确认是否通过官方下载');
        }
        if (false === $content = file_get_contents($composer_file)) {
            throw new Exception('composer.json文件损坏，请确认是否通过官方下载');
        }
        $json = json_decode($content, true);
        if (!is_array($json) || !isset($json['name']) || !isset($json['version'])) {
            throw new Exception('composer.json文件损坏，请确认是否通过官方下载');
        }
        $res = [];
        $res['project'] = $json['name'];
        $res['version'] = $json['version'];
        $res['site'] = $this->getSite();
        $res['site'] = 'http://43.155.74.117/index.php';
        $res['installed'] = $this->getInstalled();
        return $res;
    }

    private function getInstalled(): array
    {
        $installed = [];
        foreach (App::all() as $appname) {
            $tmp = [];
            $tmp['core'] = App::isCore($appname);
            if (App::isCore($appname)) {
                $tmp['version'] = InstalledVersions::getVersion($appname);
            } else {
                $json_file = App::getDir($appname) . '/composer.json';
                if (file_exists($json_file)) {
                    $json = json_decode(file_get_contents($json_file), true);
                    $tmp['version'] = $json['version'] ?? '';
                }
            }
            $installed[$appname] = $tmp;
        }
        return $installed;
    }

    private function getSite(): string
    {
        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $port = null;
        if (isset($_SERVER['HTTP_HOST'])) {
            $uri = 'http://' . $_SERVER['HTTP_HOST'];
            $parts = parse_url($uri);
            if (false !== $parts) {
                $host = isset($parts['host']) ? $parts['host'] : null;
                $port = isset($parts['port']) ? $parts['port'] : null;
            }
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'];
        }

        if (is_null($port) && isset($_SERVER['SERVER_PORT'])) {
            $port = $_SERVER['SERVER_PORT'];
        }

        $site_base = $scheme . '://' . $host . (in_array($port, [null, 80, 443]) ? '' : ':' . $port);
        if (strpos($_SERVER['REQUEST_URI'] ?? '', $_SERVER['SCRIPT_NAME']) === 0) {
            $site_path = $_SERVER['SCRIPT_NAME'];
        } else {
            $dir_script = dirname($_SERVER['SCRIPT_NAME']);
            $site_path = strlen($dir_script) > 1 ? $dir_script : '';
        }

        return $site_base . $site_path;
    }

    private function post($url, array $data)
    {
        $data = http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Length: ' . strlen($data),
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error);
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code >= 400) {
            $error = "HTTP error - $http_code";
            curl_close($ch);
            throw new Exception($error);
        }
        curl_close($ch);
        return $response;
    }
}
