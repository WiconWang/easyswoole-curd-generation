<?php
/**
 * 557 easyswoole.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/18 上午11:43
 */

namespace ESGeneration\Builder\Project;

use EasySwoole\Utility\File;

class InitFile
{
    /**
     * createBaseDirectory
     * @param $baseDirectory
     * @throws \Exception
     * @author Tioncico
     * Time: 19:49
     */
    protected $fileExt = 'ptmpl';

    protected function createBaseDirectory($baseDirectory)
    {
        File::createDirectory($baseDirectory);
    }

    /**
     * 路由配置
     * @return bool
     */
    public function createRoute()
    {
        $this->createBaseDirectory('App//HttpController');
        $this->createPHPDocument('/App/HttpController/Router', $this->getPHPDocument('/templates/Route'));
        return true;
    }

    /**
     * 接口文档配置
     * @return bool
     */
    public function createApiDoc()
    {
        $this->createBaseDirectory('App//HttpController');
        $this->createPHPDocument('/App/HttpController/Swagger', $this->getPHPDocument('/templates/Swagger'));
    }

    /**
     * 响应码和配置
     * @return bool
     */
    public function createConfig()
    {
        $this->createBaseDirectory('App//Config');
        $this->createPHPDocument('/App/Config/Constant', $this->getPHPDocument('/templates/ConfigConstant'));
        $this->createPHPDocument('/App/Config/ReturnCode', $this->getPHPDocument('/templates/ConfigReturnCode'));
        return true;
    }


    /**
     * 工具集
     * @return bool
     */
    public function createUtilities()
    {
        $this->createBaseDirectory('App//Utilities');
        $this->createPHPDocument('/App/Utilities/CurlHelper', $this->getPHPDocument('/templates/UtilitiesCurlHelper'));
        $this->createPHPDocument('/App/Utilities/PageHelper', $this->getPHPDocument('/templates/UtilitiesPageHelper'));
        $this->createPHPDocument('/App/Utilities/ResponseHelper', $this->getPHPDocument('/templates/UtilitiesResponseHelper'));
        return true;
    }

    /**
     * 全站异常处理
     * @return bool
     */
    public function createExceptions()
    {
        $this->createBaseDirectory('App//Exceptions');
        $this->createPHPDocument('/App/Exceptions/ExceptionHandler', $this->getPHPDocument('/templates/ExceptionHandler'));

        // 读取主配置文件并添加 Exception
        $this->saveEasySwooleEvent('initialize', '
        \EasySwoole\Component\Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER,[\App\Exceptions\ExceptionHandler::class,\'handle\']);
        ');
        return true;
    }

    /**
     *   Mysql 模块
     * @return bool
     */
    public function createMysql()
    {
        $this->saveEasySwooleEvent('mainServerCreate', '
        $mysqlConfig = new \EasySwoole\Mysqli\Config(Config::getInstance()->getConf(\'MYSQL\'));
        try {
            \EasySwoole\MysqliPool\Mysql::getInstance()->register(\'mysql\', $mysqlConfig);
        } catch (\EasySwoole\MysqliPool\MysqlPoolException $e) {
            echo "[Warn] --> mysql池注册失败\n";
        }
        ');

        $str = ',
    \'MYSQL\' => [
        //数据库配置
        \'host\'                 => \'mysql\',//数据库连接ip
        \'user\'                 => \'root\',//数据库用户名
        \'password\'             => \'password\',//数据库密码
        \'database\'             => \'project\',//数据库
        \'port\'                 => \'3306\',//端口
        \'prefix\'                 => \'\',//前缀
        \'timeout\'              => \'30\',//超时时间
        \'connect_timeout\'      => \'5\',//连接超时时间
        \'charset\'              => \'utf8mb4\',//字符编码
        \'strict_type\'          => false, //开启严格模式，返回的字段将自动转为数字类型
        \'fetch_mode\'           => false,//开启fetch模式, 可与pdo一样使用fetch/fetchAll逐行或获取全部结果集(4.0版本以上)
        \'alias\'                => \'\',//子查询别名
        \'isSubQuery\'           => false,//是否为子查询
        \'max_reconnect_times \' => \'3\',//最大重连次数
    ]';
        $this->saveDev($str);
        $this->saveDev($str, false);
        return true;

    }

    /**
     *  Redis 模块
     * @return bool
     */
    public function createRedis()
    {
        $this->saveEasySwooleEvent('mainServerCreate', '
        $configData = Config::getInstance()->getConf(\'REDIS\');
        $config = new \EasySwoole\RedisPool\Config($configData);
        $config->setOptions([\'serialize\'=>true]);
        try {
            $poolConf = \EasySwoole\RedisPool\Redis::getInstance()->register(\'redis\', $config);
            $poolConf->setMaxObjectNum($configData[\'maxObjectNum\']);
            $poolConf->setMinObjectNum($configData[\'minObjectNum\']);
        } catch (\EasySwoole\RedisPool\RedisPoolException $e) {
            echo "[Warn] --> Redis池注册失败\n";
        } catch (\EasySwoole\Component\Pool\Exception\PoolObjectNumError $e) {
            echo "[Warn] --> Redis参数设置失败\n";
        }
        ');


        $str = ',
    \'REDIS\' => [
        \'host\'          => \'redis\',
        \'port\'          => \'6379\',
        \'auth\'          => \'\',
        \'intervalCheckTime\'    => 30 * 1000,//定时验证对象是否可用以及保持最小连接的间隔时间
        \'maxIdleTime\'          => 15,//最大存活时间,超出则会每$intervalCheckTime/1000秒被释放
        \'maxObjectNum\'         => 20,//最大创建数量
        \'minObjectNum\'         => 5,//最小创建数量 最小创建数量不能大于等于最大创建
    ]';

        $this->saveDev($str);
        $this->saveDev($str, false);

        return true;

    }


    /**
     * 全站 JSON 影响
     * @return bool
     */
    public function createJson()
    {

        $this->saveEasySwooleEvent('onRequest', '
        $response->withHeader(\'Content-type\', \'application/json;charset=utf-8\');
        ');

        return true;

    }

    /**
     * 允许跨域模块
     * @return bool
     */
    public function createOrigin()
    {

        $this->saveEasySwooleEvent('onRequest', '
        // 是否允许全部IP
        $allow_all = false;
        // 是否允许特定IP
        $allow_origin = array(
            \'http://xxx.net.cn\',
        );
        $origin = $request->getHeader(\'origin\');
        if ($origin !== []) {
            $origin_user = $allow_all ? \'*\' : $origin[0];
            if ($allow_all || in_array($origin_user, $allow_origin)) {
                $response->withHeader(\'Access-Control-Allow-Origin\', $origin_user);
                $response->withHeader(\'Access-Control-Allow-Methods\', \'GET, POST, OPTIONS, DELETE, PUT, PATCH\');
                $response->withHeader(\'Access-Control-Allow-Credentials\', \'true\');
                $response->withHeader(\'Access-Control-Allow-Headers\', \'Content-Type, Authorization, X-Requested-With, token\');
                if ($request->getMethod() === \'OPTIONS\') {
                    $response->withStatus(\EasySwoole\Http\Message\Status::CODE_OK);
                    return false;
                }
            }
        }
        // 重写掉Server防止暴露服务器敏感信息
        $response->withHeader(\'Server\', \'WiconServer\');
        ');

        return true;

    }

    // Base文件追加内容
    /**
     * 全站 JSON 影响
     * @return bool
     */
    public function appendBase()
    {
        $content = file_get_contents(ES_ROOT . '/App/HttpController/Base.php');
        $content = str_replace('public function index()','
        use \App\Utilities\ResponseHelper;
        public function index()
        ',$content);

        file_put_contents(ES_ROOT . '/App/HttpController/Base.php',$content);

    }
    //


    // 取内容
    protected function getPHPDocument($filePath)
    {
        return file_get_contents(ESGENERATION_ROOT . $filePath . '.' . $this->fileExt);
    }


    // 生成文件
    protected function createPHPDocument($filePath, $fileContent)
    {
        $filePath = ES_ROOT . $filePath;
        if (file_exists($filePath . '.php')) {
            echo "(INIT)当前路径已经存在文件,是否覆盖?(y/n)\n";
            if (trim(fgets(STDIN)) == 'n') {
                echo "- 已跳过\n";
                return false;
            }
        }
        $result = file_put_contents($filePath . '.php', $fileContent);

        return $result == false ? $result : $filePath . '.php';
    }

    /**
     * 向 EasySwooleEvent 指定方法中添加段落
     * @param $searchAction
     * @param $append
     * @return bool|int
     */
    protected function saveEasySwooleEvent($searchAction, $append)
    {
        $content = file_get_contents(ES_ROOT . '/EasySwooleEvent.php');
        $parseArray = explode('function', $content);
        foreach ($parseArray as $k => $v) {
            if (strpos($v, $searchAction) !== false) {
                // 在第2个元素前追加内容
                $htmlArray = explode('{', $v);
                $htmlArray[1] = $append . $htmlArray[1];
                $parseArray[$k] = implode('{', $htmlArray);
            }
        }
        return file_put_contents(ES_ROOT . '/EasySwooleEvent.php', implode('function', $parseArray));
    }


    /**
     * 向 dev 指定方法中添加段落
     * @param $append
     * @param bool $dev
     * @return bool|int
     */
    protected function saveDev($append, $dev = true)
    {
        $file = $dev ? ES_ROOT . '/dev.php' : ES_ROOT . '/produce.php';
        $content = file_get_contents($file);
        $parseArray = array_reverse(explode(']', $content));
        // 在第一个元素后添加内容
        $parseArray[1] = $append . $parseArray[1];
        return file_put_contents($file, implode(']', array_reverse($parseArray)));
    }


}
