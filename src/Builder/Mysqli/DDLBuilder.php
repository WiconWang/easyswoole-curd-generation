<?php
/**
 * 557 Service_Idm.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/22 2:39 PM
 */
namespace ESGeneration\Builder\Mysqli;


use EasySwoole\Mysqli\DDLBuilder\Blueprints\TableBlueprint;
use ESGeneration\Builder\Mysqli\alterTableBlueprint;

/**
 * DDL生成助手
 * Class DDLBuilder
 * @package EasySwoole\Mysqli\DDLBuilder
 */
class DDLBuilder
{
    /**
     * 生成建表语句
     * @param string $table 表名称
     * @param callable $callable 在闭包中描述创建过程
     * @return string 返回生成的DDL语句
     */
    static function table($table, callable $callable)
    {
        $blueprint = new TableBlueprint($table);
        $callable($blueprint);
        return $blueprint->__createDDL();
    }


    /**
     * 生成建表语句
     * @param string $table 表名称
     * @param callable $callable 在闭包中描述创建过程
     * @return string 返回生成的DDL语句
     */
    static function alter($table, callable $callable)
    {
        $blueprint = new alterTableBlueprint($table);
        $callable($blueprint);
        return $blueprint->__alterDDL();
    }
}