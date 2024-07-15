<?php

declare(strict_types=1);

namespace App\Ebcms\Store\Http;

use App\Ebcms\Store\Help\Server;
use App\Phpomg\Admin\Http\Common;
use Composer\Autoload\ClassLoader;
use PHPOMG\Facade\Session;
use PHPOMG\Help\Request;
use PHPOMG\Help\Response;
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
            if (!$res['status']) {
                return Response::failure($res['message'], $res['redirect_url'] ?? '', $res['data'] ?? null);
            }

            $root = dirname((new ReflectionClass(ClassLoader::class))->getFileName(), 3);
            $file = $root . '/runtime/~storeapp.php';
            if (!file_exists($file)) {
                return Response::failure('数据丢失，请重试');
            }
            $content = file_get_contents($file);
            if ($content == false) {
                return Response::failure('数据丢失，请重试');
            }
            if (substr($content, 0, strlen('<?php die();?>')) != '<?php die();?>') {
                return Response::failure('数据丢失，请重试');
            }
            $data = json_decode(substr($content, strlen('<?php die();?>')), true);
            if (!isset($data['token'])) {
                return Response::failure('数据丢失，请重试');
            }
            if ($data['token'] != $token) {
                return Response::failure('数据丢失，请重试');
            }

            Session::set('appitem', $data);
            return Response::success($res['message']);
        } catch (Throwable $th) {
            return Response::failure($th->getMessage());
        }
    }
}
