<?php
/**
 * 557 异常处理
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/15 6:29 PM
 */

namespace App\Exceptions;

use App\Config\ReturnCode;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;


class ExceptionHandler
{

    public static function handle(\Throwable $exception, Request $request, Response $response)
    {
        $code = $exception->getCode();
        if ($exception->getMessage() == 'swoole exit') $code = ReturnCode::ERROR;
        if (!$response->isEndResponse()) {
            $data = Array(
                "code"   => $code,
                "result" => array(
                    'file'   => $exception->getFile(),
                    'line'   => $exception->getLine(),
                    'error'  => $exception->getTrace(),
                ),
                "msg"    => $exception->getMessage()
            );
            $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $response->withHeader('Content-type', 'application/json;charset=utf-8');
            $response->withStatus(ReturnCode::getStatus($exception->getCode()));
            return true;
        } else {
            return false;
        }
    }
}