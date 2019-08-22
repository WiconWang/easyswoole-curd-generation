<?php
/**
 * 557 Service_Idm.
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/22 2:41 PM
 */

namespace ESGeneration\Builder\Mysqli;

use EasySwoole\Mysqli\DDLBuilder\Blueprints\TableBlueprint;

class alterTableBlueprint extends TableBlueprint
{

    public function __alterDDL()
    {
        // 表名称定义
        $tableName = "`{$this->table}`"; // 安全起见引号包裹

        // 表格字段定义
        $columnDefinitions = [];
        foreach ($this->columns as $name => $column) {
            $columnDefinitions[] = '  ' . (string)$column;
        }

        // 表格索引定义
        $indexDefinitions = [];
        foreach ($this->indexes as $name => $index) {
            $indexDefinitions[] = '  ' . (string)$index;
        }

        // 表格属性定义
        $tableOptions = array_filter(
            [
                $this->engine ? 'ENGINE = ' . strtoupper($this->engine) : null,
                $this->charset ? "DEFAULT COLLATE = '" . $this->charset . "'" : null,
                $this->comment ? "COMMENT = '" . addslashes($this->comment) . "'" : null
            ]
        );

        // 构建表格DDL
        $createDDL = implode(
                "\n",
                array_filter(
                    [
                        "ALTER TABLE {$tableName} (",
                        implode(",\n",
                            array_merge(
                                $columnDefinitions,
                                $indexDefinitions
                            )
                        ),
                        ')',
                        implode(" ", $tableOptions),
                    ]
                )
            ) . ';';

        return $createDDL;
    }

}