<?php
/**
 * 557 HiNewsServer.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/3/29 下午9:41
 */

namespace App\Utilities;


class CurlHelper
{

    /**
     * 发送数据,并带着对应的 cookie
     * @param String $url 请求的地址
     * @param string $method
     * @param array $header 自定义的header数据
     * $header = array('x:y','language:zh','region:GZ');
     * @param array $content POST的数据
     * $content = array('name' => '11');
     * @param int $backHeader 返回数据是否返回header
     * 0不反回 1返回
     * @param string $cookie 携带的cookie
     * @return String
     */
    public static function curl($url, $method = 'get', $header = array(), $content = array(), $backHeader = 0, $cookie = '')
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);          //单位 秒，也可以使用
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36');
        if (!isset($header[0])) {//将索引数组转为键值数组
            foreach ($header as $hk => $hv) {
                unset($header[$hk]);
                $header[] = $hk . ':' . $hv;
            }
        }
        $header[] = 'Expect:';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if (count($content)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        if (!empty($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($ch, CURLOPT_HEADER, $backHeader); // 显示返回的Header区域内容
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
//            die($error);
            return $error;
        }
        curl_close($ch);
        return $response;

    }
}
