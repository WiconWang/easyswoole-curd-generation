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
    protected $tables = [];

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

    function makeBean($tableName)
    {
        go(function () use ($tableName) {

            $db = \EasySwoole\MysqliPool\Mysql::defer('mysql');
            if (!$this->checkTableName($db, $tableName)) {
                echo PHP_EOL . PHP_EOL . ">> 未检测到表" . $tableName . PHP_EOL;
                exit;
            }
            echo PHP_EOL . PHP_EOL . "======>>> 检测到表 [$tableName] <<<======" . PHP_EOL;
            $mysqlTable = new \AutomaticGeneration\MysqlTable($db, \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.database'));
            $tableColumns = $mysqlTable->getColumnList($tableName);
            $tableComment = $mysqlTable->getComment($tableName);

            echo PHP_EOL . PHP_EOL . ">> 生成 Bean" . PHP_EOL;
            $path = '';
            $beanConfig = new \AutomaticGeneration\Config\BeanConfig();
            $beanConfig->setBaseNamespace("App\\Bean" . $path);
            $beanConfig->setTablePre(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix'));
            $beanConfig->setTableName($tableName);
            $beanConfig->setTableComment($tableComment);
            $beanConfig->setTableColumns($tableColumns);
            $beanBuilder = new \AutomaticGeneration\BeanBuilder($beanConfig);
            $result = $beanBuilder->generateBean();
            echo "> 已处理：$result" . PHP_EOL;


            exit();
        });
    }


}
