<?php
/**
 * 557 easyswoole.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/18 上午11:43
 */

namespace ESGeneration\Builder\Project;


class Curd
{

    protected $mysqlConfig = [];
    protected $tables      = [];

    function __construct()
    {
        \EasySwoole\EasySwoole\Core::getInstance()->initialize();
        $DBConfig = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL');
        $this->mysqlConfig = new \EasySwoole\Mysqli\Config($DBConfig);
        \EasySwoole\MysqliPool\Mysql::getInstance()->register('mysql', $this->mysqlConfig);
    }

    function checkTableName($db, $tableName)
    {
        $result = $db->rawQuery("show tables;");
        $tables = array_column($result, 'Tables_in_' . $this->mysqlConfig->getDatabase());
        return in_array($tableName, $tables);

    }

    /**
     * 生成Bean层
     * @param $tableName
     */
    function makeBean($tableName)
    {
        go(function () use ($tableName) {

            $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (!$this->checkTableName($db, $tableName)) {
                echo PHP_EOL . ">> 未检测到表" . $tableName . PHP_EOL  . PHP_EOL . '按Ctrl+C结束任务';
                return false;
//                exit;
            }
            echo PHP_EOL . "======>>> 检测到表 [$tableName] <<<======" . PHP_EOL;
            $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
            $tableColumns = $mysqlTable->getColumnList($tableName);
            $tableComment = $mysqlTable->getComment($tableName);

            echo PHP_EOL . ">> 生成 Bean" . PHP_EOL;
            $path = '';
            $beanConfig = new \AutomaticGeneration\Config\BeanConfig();
            $beanConfig->setBaseNamespace("App\\Bean" . $path);
            $beanConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $beanConfig->setTableName($tableName);
            $beanConfig->setTableComment($tableComment);
            $beanConfig->setTableColumns($tableColumns);
            $beanBuilder = new \AutomaticGeneration\BeanBuilder($beanConfig);
            $result = $beanBuilder->generateBean();
            echo "> 已处理：$result" . PHP_EOL  . PHP_EOL . '按Ctrl+C结束任务';
            return true;
//            exit();
        });
    }

    /**
     * 生成Model层
     * @param $tableName
     */
    public function makeModel($tableName, $path = '')
    {
        go(function () use ($tableName, $path) {

            $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (!$this->checkTableName($db, $tableName)) {
                echo PHP_EOL . ">> 未检测到表" . $tableName . PHP_EOL . PHP_EOL . '按Ctrl+C结束任务';
                return false;
//                exit;
            }
            echo PHP_EOL . "======>>> 检测到表 [$tableName] <<<======" . PHP_EOL;
            $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
            $tableColumns = $mysqlTable->getColumnList($tableName);
            $tableComment = $mysqlTable->getComment($tableName);


            echo PHP_EOL . PHP_EOL . ">> 生成 Model" . PHP_EOL;
            $modelConfig = new \ESGeneration\Builder\Config\ModelConfig();
            $modelConfig->setBaseNamespace("App\\Model" . $path);
            $modelConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $modelConfig->setExtendClass(\App\Model\BaseModel::class);
            $modelConfig->setTableName($tableName);
            $modelConfig->setTableComment($tableComment);
            $modelConfig->setTableColumns($tableColumns);
            $modelBuilder = new \ESGeneration\Builder\Component\ModelBuilder($modelConfig);
            $modelConfig->setBeanClass("App\\Bean\\" . $modelConfig->getRealTableName() . 'Bean');
            $modelBuilder = new \ESGeneration\Builder\Component\ModelBuilder($modelConfig);

            $result = $modelBuilder->generateModel();
            echo "> 已处理：$result" . PHP_EOL . PHP_EOL . '按Ctrl+C结束任务';
            return true;

//            exit();
        });
    }

    /**
     * 生成Model层
     * @param $tableName
     */
    public function makeController($tableName, $path = '')
    {
        go(function () use ($tableName, $path) {

            $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (!$this->checkTableName($db, $tableName)) {
                echo PHP_EOL . ">> 未检测到表" . $tableName . PHP_EOL . PHP_EOL . '按Ctrl+C结束任务';
                return false;
//                exit;
            }
            echo PHP_EOL . "======>>> 检测到表 [$tableName] <<<======" . PHP_EOL;
            $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
            $tableColumns = $mysqlTable->getColumnList($tableName);
            $tableComment = $mysqlTable->getComment($tableName);


            echo PHP_EOL . PHP_EOL . ">> 生成 Controller" . PHP_EOL;
            $controllerConfig = new \AutomaticGeneration\Config\ControllerConfig();
            $controllerConfig->setBaseNamespace("App\\HttpController" . $path);
            $controllerConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $controllerConfig->setTableName($tableName);
            $controllerConfig->setTableComment($tableComment);
            $controllerConfig->setTableColumns($tableColumns);


            $controllerConfig->setExtendClass("App\\HttpController" . $path . "\\Base");
            $controllerConfig->setMysqlPoolClass(\EasySwoole\MysqliPool\Mysql::class);
            $controllerConfig->setMysqlPoolName('mysql');
            $controllerBuilder = new \ESGeneration\Builder\Component\ControllerBuilder($controllerConfig);
            $controllerConfig->setModelClass("App\\Model\\" . $controllerBuilder->setRealTableName() . 'Model');
            $controllerConfig->setBeanClass("App\\Bean\\" . $controllerBuilder->setRealTableName() . 'Bean');
            $controllerBuilder = new \ESGeneration\Builder\Component\ControllerBuilder($controllerConfig);
            $result = $controllerBuilder->generateController();

            $routerFile = ES_ROOT . '/App/HttpController/Router.php';
            $router = file_get_contents($routerFile);
            $resoureRouter = '
            $r->addGroup(\'/' . $controllerBuilder->setRealTableName() . '\', function (RouteCollector $r) {        
                $r->get(\'/info\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/getAll\');
                $r->get(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/getOne\');
                $r->post(\'/info\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/add\');
                $r->put(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/update\');
                $r->delete(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/delete\');
            });
            ';

            file_put_contents($routerFile, str_replace('ROUTEAREA', 'ROUTEAREA' . $resoureRouter, $router));

            echo "> 已处理：$result" . PHP_EOL . PHP_EOL . '按Ctrl+C结束任务';
            return true;
//            exit();
        });
    }











    function makeCurd($tableName, $path = '')
    {
        go(function () use ($tableName) {

            $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (!$this->checkTableName($db, $tableName)) {
                echo PHP_EOL . ">> 未检测到表" . $tableName . PHP_EOL  ;
                return false;
//                exit;
            }
            echo PHP_EOL . "======>>> 检测到表 [$tableName] <<<======" . PHP_EOL;
            $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
            $tableColumns = $mysqlTable->getColumnList($tableName);
            $tableComment = $mysqlTable->getComment($tableName);

            echo PHP_EOL . ">> 生成 Bean" . PHP_EOL;
            $path = '';
            $beanConfig = new \AutomaticGeneration\Config\BeanConfig();
            $beanConfig->setBaseNamespace("App\\Bean" . $path);
            $beanConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $beanConfig->setTableName($tableName);
            $beanConfig->setTableComment($tableComment);
            $beanConfig->setTableColumns($tableColumns);
            $beanBuilder = new \AutomaticGeneration\BeanBuilder($beanConfig);
            $result = $beanBuilder->generateBean();
            echo "> 已处理：$result" . PHP_EOL  ;


            echo PHP_EOL . PHP_EOL . ">> 生成 Model" . PHP_EOL;
            $modelConfig = new \ESGeneration\Builder\Config\ModelConfig();
            $modelConfig->setBaseNamespace("App\\Model" . $path);
            $modelConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $modelConfig->setExtendClass(\App\Model\BaseModel::class);
            $modelConfig->setTableName($tableName);
            $modelConfig->setTableComment($tableComment);
            $modelConfig->setTableColumns($tableColumns);
            $modelConfig->setBeanClass($beanBuilder->getClassName());
            $modelBuilder = new \ESGeneration\Builder\Component\ModelBuilder($modelConfig);

            $result = $modelBuilder->generateModel();
            echo "> 已处理：$result" . PHP_EOL ;


            echo PHP_EOL . PHP_EOL . ">> 生成 Controller" . PHP_EOL;
            $controllerConfig = new \AutomaticGeneration\Config\ControllerConfig();
            $controllerConfig->setBaseNamespace("App\\HttpController" . $path);
            $controllerConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $controllerConfig->setTableName($tableName);
            $controllerConfig->setTableComment($tableComment);
            $controllerConfig->setTableColumns($tableColumns);
            $controllerConfig->setExtendClass("App\\HttpController" . $path . "\\Base");
            $controllerConfig->setMysqlPoolClass(\EasySwoole\MysqliPool\Mysql::class);
            $controllerConfig->setMysqlPoolName('mysql');
            $controllerConfig->setModelClass($modelBuilder->getClassName());
            $controllerConfig->setBeanClass($beanBuilder->getClassName());
            $controllerBuilder = new \ESGeneration\Builder\Component\ControllerBuilder($controllerConfig);
            $result = $controllerBuilder->generateController();

            $routerFile = ES_ROOT . '/App/HttpController/Router.php';
            $router = file_get_contents($routerFile);
            $resoureRouter = '
            $r->addGroup(\'/' . $controllerBuilder->setRealTableName() . '\', function (RouteCollector $r) {        
                $r->get(\'/info\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/getAll\');
                $r->get(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/getOne\');
                $r->post(\'/info\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/add\');
                $r->put(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/update\');
                $r->delete(\'/info/{id:\d+}\', \'' . $path . '/' . $controllerBuilder->setRealTableName() . '/delete\');
            });
            ';

            file_put_contents($routerFile, str_replace('ROUTEAREA', 'ROUTEAREA' . $resoureRouter, $router));

            echo "> 已处理：$result" . PHP_EOL . PHP_EOL . '按Ctrl+C结束任务';
            return true;
//            exit();
        });
    }


    protected function makeRouter($tableName, $path = '')
    {

    }

}
