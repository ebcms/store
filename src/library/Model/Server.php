<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Model;

use Composer\InstalledVersions;
use DiggPHP\Router\Router;
use DiggPHP\Framework\AppInterface;
use DiggPHP\Framework\Config;
use DiggPHP\Framework\Framework;
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
        $root = InstalledVersions::getRootPackage();
        $res = [];
        $res['name'] = $root['name'];
        $res['version'] = $root['pretty_version'];
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
        foreach (glob($install_path . '/plugin/*/src/library/App.php') as $file) {

            $name = substr($file, strlen($install_path . '/plugin/'), -strlen('/src/library/App.php'));

            if (file_exists($install_path . '/config/plugin/' . $name . '/disabled.lock')) {
                continue;
            }

            if (!file_exists($install_path . '/config/plugin/' . $name . '/install.lock')) {
                continue;
            }

            $class_name = str_replace(['-', '/'], ['', '\\'], ucwords('\\App\\Plugin\\' . $name . '\\App', '/\\-'));
            if (
                !class_exists($class_name)
                || !is_subclass_of($class_name, AppInterface::class)
            ) {
                continue;
            }

            $json_file = $install_path . '/plugin/' . $name . '/plugin.json';
            $json = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

            $res[$name] = $json['version'] ?? '0.0.0.0';
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
