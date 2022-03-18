<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Model;

use Composer\InstalledVersions;
use DigPHP\Framework\AppInterface;
use DigPHP\Framework\Config;
use DigPHP\Framework\Framework;
use DigPHP\Router\Router;
use Exception;
use Throwable;

class Server
{
    private $api;

    public function __construct(Config $config)
    {
        $this->api = $config->get('api.host@ebcms/store', 'https://www.ebcms.com/index.php/plugin/store/api');
    }

    public function query(string $path, array $param = []): array
    {
        try {
            $url = $this->api . $path . '?' . http_build_query($this->getCommonParam());
            $res = (array)json_decode($this->post($url, $param), true);
            if (!isset($res['code'])) {
                return [
                    'code' => 1,
                    'message' => '错误：服务器无效响应！',
                ];
            }
            if ($res['code']) {
                $res['message'] = '服务器消息：' . ($res['message'] ?? '');
            }
            return $res;
        } catch (Throwable $th) {
            return [
                'code' => 1,
                'message' => '错误：' . $th->getMessage(),
            ];
        }
    }

    private function getCommonParam(): array
    {
        $package = InstalledVersions::getRootPackage();
        $res = [];
        $res['name'] = $package['name'];
        $res['version'] = $package['pretty_version'];
        $res['site'] = Framework::execute(function (
            Router $router
        ): string {
            return $router->build('/');
        });
        $res['install'] = $this->getInstalled();
        return $res;
    }

    public function getInstalled(): array
    {
        $res = [];
        $install_path = InstalledVersions::getRootPackage()['install_path'];
        foreach (glob($install_path . '/app/*/*/src/library/App.php') as $file) {
            $app = substr($file, strlen($install_path . '/app/'), -strlen('/src/library/App.php'));

            if (file_exists($install_path . '/config/' . $app . '/disabled.lock')) {
                continue;
            }

            if (!file_exists($install_path . '/config/' . $app . '/install.lock')) {
                continue;
            }

            $composer_file = $install_path . '/app/' . $app . '/composer.json';
            if (!file_exists($composer_file)) {
                continue;
            }

            $json = (array)json_decode(file_get_contents($composer_file), true);
            if (!isset($json['version'])) {
                continue;
            }

            $app_file = $install_path . '/app/' . $app . '/src/library/App.php';
            if (!file_exists($app_file)) {
                continue;
            }
            require_once $app_file;

            $class_name = str_replace(['-', '/'], ['', '\\'], ucwords('\\App\\' . $app . '\\App', '/\\-'));
            if (
                !class_exists($class_name)
                || !is_subclass_of($class_name, AppInterface::class)
            ) {
                continue;
            }

            $res[$app] = $json['version'];
        }
        return $res;
    }

    private function get(string $url, $timeout = 5, $ssl_verify = false)
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
            ],
        ];
        if ($ssl_verify === false) {
            $options['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ];
        }
        $response = file_get_contents($url, false, stream_context_create($options));
        if (false === $response) {
            throw new Exception('post(' . $url . ') failure!');
        }
        return $response;
    }

    private function post(string $url, array $data = [], $timeout = 5, $ssl_verify = false)
    {
        $content = http_build_query($data);
        $options = [
            'http' => [
                'method' => 'POST',
                'timeout' => $timeout,
                'header' => "Accept: application/json\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . mb_strlen($content),
                'content' => $content,
            ],
        ];
        if ($ssl_verify === false) {
            $options['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ];
        }
        $response = file_get_contents($url, false, stream_context_create($options));
        if (false === $response) {
            throw new Exception('post(' . $url . ') failure!');
        }
        return $response;
    }
}
