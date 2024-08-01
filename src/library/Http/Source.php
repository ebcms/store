<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Store\Help\Server;
use App\Php94\Admin\Http\Common;
use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use PHP94\Facade\App;
use PHP94\Facade\Session;
use PHP94\Help\Request;
use PHP94\Help\Response;
use ReflectionClass;
use Throwable;

class Source extends Common
{
    public function get()
    {
        try {
            $name = Request::get('name');
            $token = 'app_' . md5(uniqid() . rand(10000000, 99999999));
            $res = (new Server)->query('/source', [
                'token' => $token,
                'name' => $name,
            ]);
            if ($res['error']) {
                return Response::error($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null, $res['error'] ?? 1);
            }

            $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
            $file = $root . '/runtime/~storeapp.php';
            if (!file_exists($file)) {
                return Response::error('数据丢失，请重试');
            }
            $content = file_get_contents($file);
            if ($content == false) {
                return Response::error('数据丢失，请重试');
            }
            if (substr($content, 0, strlen('<?php die();?>')) != '<?php die();?>') {
                return Response::error('数据丢失，请重试');
            }
            $data = json_decode(substr($content, strlen('<?php die();?>')), true);
            if (!isset($data['token'])) {
                return Response::error('数据丢失，请重试');
            }
            if ($data['token'] != $token) {
                return Response::error('数据丢失，请重试');
            }

            if (App::has($name)) {
                if (App::isCore($name)) {
                    $data['oldversion'] = InstalledVersions::getVersion($name);
                } else {
                    $json_file = App::getDir($name) . '/composer.json';
                    if (file_exists($json_file)) {
                        $json = json_decode(file_get_contents($json_file), true);
                        $data['oldversion'] = $json['version'] ?? '';
                    }
                }
            }

            Session::set('appitem', $data);
            return Response::success($res['message']);
        } catch (Throwable $th) {
            return Response::error($th->getMessage());
        }
    }
}
