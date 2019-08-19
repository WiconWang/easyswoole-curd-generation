<?php
/**
 * 557 Controller构建
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/18 上午11:43
 */

namespace ESGeneration\Builder\Component;

use AutomaticGeneration\Config\ControllerConfig;
use App\Config\ReturnCode;
//use App\Utilities\ResponseHelper;
use EasySwoole\MysqliPool\Mysql;
use EasySwoole\Utility\File;
use EasySwoole\Utility\Str;
use EasySwoole\Validate\Validate;
use http\Message\Body;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

/**
 * easyswoole 控制器快速构建器
 * Class ControllerBuilder
 * @package AutomaticGeneration
 */
class ControllerBuilder
{
    /**
     * @var $config BeanConfig;
     */
    protected $config;
    protected $validateList = [];

    /**
     * BeanBuilder constructor.
     * @param        $config
     * @throws \Exception
     */
    public function __construct(ControllerConfig $config)
    {
        $this->config = $config;
        $this->createBaseDirectory($config->getBaseDirectory());
    }

    /**
     * createBaseDirectory
     * @param $baseDirectory
     * @throws \Exception
     * @author Tioncico
     * Time: 19:49
     */
    protected function createBaseDirectory($baseDirectory)
    {
        File::createDirectory($baseDirectory);
    }

    /**
     * generateBean
     * @return bool|int
     * @author Tioncico
     * Time: 19:49
     */
    public function generateController()
    {
        $realTableName = $this->setRealTableName();
        $phpNamespace = new PhpNamespace($this->config->getBaseNamespace());
        $phpNamespace->addUse($this->config->getMysqlPoolClass());
        $phpNamespace->addUse($this->config->getModelClass());
        $phpNamespace->addUse($this->config->getBeanClass());
        $phpNamespace->addUse(ReturnCode::class);
//        $phpNamespace->addUse(ResponseHelper::class);
        $phpNamespace->addUse(Validate::class);
        $phpNamespace->addUse(Mysql::class);
        $phpNamespace->addUse($this->config->getExtendClass());
        $phpClass = $phpNamespace->addClass($realTableName);
//        $phpClass->addTrait(ResponseHelper::class);
        $phpClass->addExtend($this->config->getExtendClass());
        $phpClass->addComment("{$this->config->getTableComment()}");
        $phpClass->addComment("Class {$realTableName}");
        $phpClass->addComment('Create With Automatic Generator');
        $this->addAddDataMethod($phpClass);
        $this->addUpdateDataMethod($phpClass);
        $this->addGetOneDataMethod($phpClass);
        $this->addGetAllDataMethod($phpClass);
        $this->addDeleteDataMethod($phpClass);
        $this->addValidateMethod($phpClass);

        return $this->createPHPDocument($this->config->getBaseDirectory() . '/' . $realTableName, $phpNamespace, $this->config->getTableColumns());
    }


    function addValidateMethod(ClassType $phpClass)
    {
        $method = $phpClass->addMethod('getValidateRule');

        $method->addParameter("action")->setTypeHint('string')->setNullable();
        $method->setReturnType(Validate::class)->setReturnNullable();
        $methodBody = <<<Body
\$validate = null;
switch (\$action) {        
{$this->validateGenerationStr()}
}
return \$validate;
Body;
        $method->setBody($methodBody);
    }

    function validateGenerationStr()
    {
        $addColumnStr = '';
        $updateColumnStr = '';
        $getOneColumnStr = '';
        $getAllColumnStr = '';
        $deleteColumnStr = '';
        $updateColumnStr .= "        \$validate->addColumn('{$this->config->getPrimaryKey()}', 'id')->required();\n";
        $deleteColumnStr .= "        \$validate->addColumn('{$this->config->getPrimaryKey()}', 'id')->required();\n";
        $getOneColumnStr .= "        \$validate->addColumn('{$this->config->getPrimaryKey()}', 'id')->required();\n";
        $getAllColumnStr .= "        \$validate->addColumn('page', '页数')->optional();
        \$validate->addColumn('limit', 'limit')->optional();
        \$validate->addColumn('keyword', '关键词')->optional();";
        foreach ($this->config->getTableColumns() as $column) {
            if ($column['Key'] != 'PRI') {
                $addColumnStr .= "        \$validate->addColumn('{$column['Field']}', '{$column['Comment']}')";
                $updateColumnStr .= "        \$validate->addColumn('{$column['Field']}', '{$column['Comment']}')->optional();\n";
                if ($column['Null'] == 'NO') {
                    $addColumnStr .= "->required()";
                } else {
                    $addColumnStr .= "->optional()";
                }
                $addColumnStr .= ";\n";
            }
        }
        $body = '';
        $body .= <<<BODY
    case 'add':
        \$validate = new Validate();       
$addColumnStr
        break;
    case 'update':
        \$validate = new Validate();       
$updateColumnStr
        break;
    case 'getAll':
        \$validate = new Validate();       
$getAllColumnStr
        break;
    case 'getOne':
        \$validate = new Validate();       
$getOneColumnStr
        break;
    case 'delete':
        \$validate = new Validate();       
$deleteColumnStr
        break;
BODY;
        return $body;
    }


    function addAddDataMethod(ClassType $phpClass)
    {
        $addData = [];
        $method = $phpClass->addMethod('add');
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $this->config->getBaseNamespace());
//        var_dump($this->config->getBaseNamespace(),$apiUrl);die;
        //配置基础注释
        $method->addComment("@api {get|post} {$apiUrl}/{$this->setRealTableName()}/add");
        $method->addComment("@apiName add");
        $method->addComment("@apiGroup {$apiUrl}/{$this->setRealTableName()}");
        $method->addComment("@apiPermission {$this->config->getAuthName()}");
        $method->addComment("@apiDescription add新增数据");
        $this->config->getAuthSessionName() && ($method->addComment("@apiParam {String}  {$this->config->getAuthSessionName()} 权限验证token"));
        $mysqlPoolNameArr = (explode('\\', $this->config->getMysqlPoolClass()));
        $mysqlPoolName = end($mysqlPoolNameArr);
        $modelNameArr = (explode('\\', $this->config->getModelClass()));
        $modelName = end($modelNameArr);
        $beanNameArr = (explode('\\', $this->config->getBeanClass()));
        $beanName = end($beanNameArr);
        if (empty($this->config->getMysqlPoolName())) {
            $methodBody = "\$db = {$mysqlPoolName}::defer();\n";
        } else {
            $methodBody = "\$db = {$mysqlPoolName}::defer('{$this->config->getMysqlPoolName()}');\n";
        }
        $methodBody .= <<<Body
\$param = \$this->request()->getRequestParam();
\$model = new {$modelName}(\$db);
\$bean = new {$beanName}();

Body;
        foreach ($this->config->getTableColumns() as $column) {
            if ($column['Key'] != 'PRI') {
                $addData[] = $column['Field'];
                $columnType = $this->convertDbTypeToDocType($column['Type']);
                $setMethodName = "set" . Str::studly($column['Field']);
                if ($column['Null'] == 'NO') {
                    $method->addComment("@apiParam {{$columnType}} {$column['Field']} {$column['Comment']}");
                    $methodBody .= "\$bean->$setMethodName(\$param['{$column['Field']}']);\n";
                } else {
                    $method->addComment("@apiParam {{$columnType}} [{$column['Field']}] {$column['Comment']}");
                    $methodBody .= "\$bean->$setMethodName(\$param['{$column['Field']}'] ?? '');\n";
                }
            } else {
                $this->config->setPrimaryKey($column['Field']);
            }
        }
        $setPrimaryKeyMethodName = "set" . Str::studly($this->config->getPrimaryKey());
        $methodBody .= <<<Body
\$rs = \$model->add(\$bean);
if (\$rs) {
    \$bean->$setPrimaryKeyMethodName(\$db->getInsertId());
    
    \$this->responseJson(ReturnCode::SUCCESS, '', \$bean->toArray());
} else {
    \$this->responseJson(ReturnCode::ERROR, \$db->getLastError(), []);
}
Body;
        $method->setBody($methodBody);
        $method->addComment("@apiSuccess {Number} code");
        $method->addComment("@apiSuccess {Object[]} data");
        $method->addComment("@apiSuccess {String} msg");
        $method->addComment("@apiSuccessExample {json} Success-Response:");
        $method->addComment("HTTP/1.1 200 OK");
        $method->addComment("{\"code\":200,\"data\":{},\"msg\":\"success\"}");
    }

    function addUpdateDataMethod(ClassType $phpClass)
    {
        $addData = [];
        $method = $phpClass->addMethod('update');
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $this->config->getBaseNamespace());
        //配置基础注释
        $method->addComment("@api {get|post} {$apiUrl}/{$this->setRealTableName()}/update");
        $method->addComment("@apiName update");
        $method->addComment("@apiGroup {$apiUrl}/{$this->setRealTableName()}");
        $method->addComment("@apiPermission {$this->config->getAuthName()}");
        $method->addComment("@apiDescription update修改数据");
        $this->config->getAuthSessionName() && ($method->addComment("@apiParam {String}  {$this->config->getAuthSessionName()} 权限验证token"));
        $method->addComment("@apiParam {int} {$this->config->getPrimaryKey()} 主键id");
        $mysqlPoolNameArr = (explode('\\', $this->config->getMysqlPoolClass()));
        $mysqlPoolName = end($mysqlPoolNameArr);
        $modelNameArr = (explode('\\', $this->config->getModelClass()));
        $modelName = end($modelNameArr);
        $beanNameArr = (explode('\\', $this->config->getBeanClass()));
        $beanName = end($beanNameArr);
        if (empty($this->config->getMysqlPoolName())) {
            $methodBody = "\$db = {$mysqlPoolName}::defer();\n";
        } else {
            $methodBody = "\$db = {$mysqlPoolName}::defer('{$this->config->getMysqlPoolName()}');\n";
        }
        $methodBody .= <<<Body
\$param = \$this->request()->getRequestParam();
\$model = new {$modelName}(\$db);
\$bean = \$model->getOne(new {$beanName}(['{$this->config->getPrimaryKey()}' => \$param['{$this->config->getPrimaryKey()}']]));
if (empty(\$bean)) {
    \$this->responseJson(ReturnCode::RECORD_NOT_FOUND);
    return false;
}
\$updateBean = new {$beanName}();
\n
Body;
        foreach ($this->config->getTableColumns() as $column) {
            if ($column['Key'] != 'PRI') {
                $addData[] = $column['Field'];
                $columnType = $this->convertDbTypeToDocType($column['Type']);
                $setMethodName = "set" . Str::studly($column['Field']);
                $getMethodName = "get" . Str::studly($column['Field']);
                $method->addComment("@apiParam {{$columnType}} [{$column['Field']}] {$column['Comment']}");
                $methodBody .= "\$updateBean->$setMethodName(\$param['{$column['Field']}'] ?? \$bean->$getMethodName());\n";
            } else {
                $this->config->setPrimaryKey($column['Field']);
            }
        }
        $setPrimaryKeyMethodName = "set" . Str::studly($this->config->getPrimaryKey());
        $methodBody .= <<<Body
\$rs = \$model->update(\$bean, \$updateBean->toArray([], \$updateBean::FILTER_NOT_NULL));
if (\$rs) {
    \$this->responseJson(ReturnCode::SUCCESS, '', \$rs);
} else {
    \$this->responseJson(ReturnCode::ERROR, \$db->getLastError(), []);
}
Body;
        $method->setBody($methodBody);
        $method->addComment("@apiSuccess {Number} code");
        $method->addComment("@apiSuccess {Object[]} data");
        $method->addComment("@apiSuccess {String} msg");
        $method->addComment("@apiSuccessExample {json} Success-Response:");
        $method->addComment("HTTP/1.1 200 OK");
        $method->addComment("{\"code\":200,\"data\":{},\"msg\":\"success\"}");
    }

    function addGetOneDataMethod(ClassType $phpClass)
    {
        $method = $phpClass->addMethod('getOne');
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $this->config->getBaseNamespace());
        //配置基础注释
        $method->addComment("@api {get|post} {$apiUrl}/{$this->setRealTableName()}/getOne");
        $method->addComment("@apiName getOne");
        $method->addComment("@apiGroup {$apiUrl}/{$this->setRealTableName()}");
        $method->addComment("@apiPermission {$this->config->getAuthName()}");
        $method->addComment("@apiDescription 根据主键获取一条信息");
        $this->config->getAuthSessionName() && ($method->addComment("@apiParam {String}  {$this->config->getAuthSessionName()} 权限验证token"));
        $method->addComment("@apiParam {int} {$this->config->getPrimaryKey()} 主键id");
        $mysqlPoolNameArr = (explode('\\', $this->config->getMysqlPoolClass()));
        $mysqlPoolName = end($mysqlPoolNameArr);
        $modelNameArr = (explode('\\', $this->config->getModelClass()));
        $modelName = end($modelNameArr);
        $beanNameArr = (explode('\\', $this->config->getBeanClass()));
        $beanName = end($beanNameArr);
        if (empty($this->config->getMysqlPoolName())) {
            $methodBody = "\$db = {$mysqlPoolName}::defer();\n";
        } else {
            $methodBody = "\$db = {$mysqlPoolName}::defer('{$this->config->getMysqlPoolName()}');\n";
        }
        $methodBody .= <<<Body
\$param = \$this->request()->getRequestParam();
\$model = new {$modelName}(\$db);
\$bean = \$model->getOne(new {$beanName}(['{$this->config->getPrimaryKey()}' => \$param['{$this->config->getPrimaryKey()}']]));
if (\$bean) {
    \$this->responseJson(ReturnCode::SUCCESS, '', \$bean);
} else {
    \$this->responseJson(ReturnCode::RECORD_NOT_FOUND);
}
Body;
        $method->setBody($methodBody);
        $method->addComment("@apiSuccess {Number} code");
        $method->addComment("@apiSuccess {Object[]} data");
        $method->addComment("@apiSuccess {String} msg");
        $method->addComment("@apiSuccessExample {json} Success-Response:");
        $method->addComment("HTTP/1.1 200 OK");
        $method->addComment("{\"code\":200,\"data\":{},\"msg\":\"success\"}");
    }

    function addDeleteDataMethod(ClassType $phpClass)
    {
        $method = $phpClass->addMethod('delete');
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $this->config->getBaseNamespace());
        //配置基础注释
        $method->addComment("@api {get|post} {$apiUrl}/{$this->setRealTableName()}/delete");
        $method->addComment("@apiName delete");
        $method->addComment("@apiGroup {$apiUrl}/{$this->setRealTableName()}");
        $method->addComment("@apiPermission {$this->config->getAuthName()}");
        $method->addComment("@apiDescription 根据主键删除一条信息");
        $this->config->getAuthSessionName() && ($method->addComment("@apiParam {String}  {$this->config->getAuthSessionName()} 权限验证token"));
        $method->addComment("@apiParam {int} {$this->config->getPrimaryKey()} 主键id");
        $mysqlPoolNameArr = (explode('\\', $this->config->getMysqlPoolClass()));
        $mysqlPoolName = end($mysqlPoolNameArr);
        $modelNameArr = (explode('\\', $this->config->getModelClass()));
        $modelName = end($modelNameArr);
        $beanNameArr = (explode('\\', $this->config->getBeanClass()));
        $beanName = end($beanNameArr);
        if (empty($this->config->getMysqlPoolName())) {
            $methodBody = "\$db = {$mysqlPoolName}::defer();\n";
        } else {
            $methodBody = "\$db = {$mysqlPoolName}::defer('{$this->config->getMysqlPoolName()}');\n";
        }
        $methodBody .= <<<Body
\$param = \$this->request()->getRequestParam();
\$model = new {$modelName}(\$db);

\$bean = \$model->getOne(new $beanName(['{$this->config->getPrimaryKey()}' => \$param['{$this->config->getPrimaryKey()}']]));
if (empty(\$bean)) {
    \$this->responseJson(ReturnCode::RECORD_NOT_FOUND);
    return false;
}

\$rs = \$model->delete(new $beanName(['{$this->config->getPrimaryKey()}' => \$param['{$this->config->getPrimaryKey()}']]));
\$this->responseDefaultJson(\$rs);
Body;
        $method->setBody($methodBody);
        $method->addComment("@apiSuccess {Number} code");
        $method->addComment("@apiSuccess {Object[]} data");
        $method->addComment("@apiSuccess {String} msg");
        $method->addComment("@apiSuccessExample {json} Success-Response:");
        $method->addComment("HTTP/1.1 200 OK");
        $method->addComment("{\"code\":200,\"data\":{},\"msg\":\"success\"}");
    }

    function addGetAllDataMethod(ClassType $phpClass)
    {
        $method = $phpClass->addMethod('getAll');
        $apiUrl = str_replace(['App\\HttpController', '\\'], ['', '/'], $this->config->getBaseNamespace());
        //配置基础注释
        $method->addComment("@api {get|post} {$apiUrl}/{$this->setRealTableName()}/getAll");
        $method->addComment("@apiName getAll");
        $method->addComment("@apiGroup {$apiUrl}/{$this->setRealTableName()}");
        $method->addComment("@apiPermission {$this->config->getAuthName()}");
        $method->addComment("@apiDescription 获取一个列表");
        $this->config->getAuthSessionName() && ($method->addComment("@apiParam {String}  {$this->config->getAuthSessionName()} 权限验证token"));
        $method->addComment("@apiParam {String} [page=1]");
        $method->addComment("@apiParam {String} [limit=20]");
        $method->addComment("@apiParam {String} [keyword] 关键字,根据表的不同而不同");
        $mysqlPoolNameArr = (explode('\\', $this->config->getMysqlPoolClass()));
        $mysqlPoolName = end($mysqlPoolNameArr);
        $modelNameArr = (explode('\\', $this->config->getModelClass()));
        $modelName = end($modelNameArr);
        $beanNameArr = (explode('\\', $this->config->getBeanClass()));
        $beanName = end($beanNameArr);
        if (empty($this->config->getMysqlPoolName())) {
            $methodBody = "\$db = {$mysqlPoolName}::defer();\n";
        } else {
            $methodBody = "\$db = {$mysqlPoolName}::defer('{$this->config->getMysqlPoolName()}');\n";
        }
        $methodBody .= <<<Body
\$param = \$this->request()->getRequestParam();
\$page = (int)(\$param['page'] ?? 1);
\$limit = (int)(\$param['limit'] ?? 20);
unset(\$param['page'], \$param['limit']);
\$model = new {$modelName}(\$db);
\$data = \$model->getAll(\$page, \$param ?? null, \$limit);
\$this->responseJson(ReturnCode::SUCCESS, '', \$data);
Body;
        $method->setBody($methodBody);
        $method->addComment("@apiSuccess {Number} code");
        $method->addComment("@apiSuccess {Object[]} data");
        $method->addComment("@apiSuccess {String} msg");
        $method->addComment("@apiSuccessExample {json} Success-Response:");
        $method->addComment("HTTP/1.1 200 OK");
        $method->addComment("{\"code\":200,\"data\":{},\"msg\":\"success\"}");
    }


    /**
     * 处理表真实名称
     * setRealTableName
     * @return bool|mixed|string
     * @author tioncico
     * Time: 下午11:55
     */
    function setRealTableName()
    {
        if ($this->config->getRealTableName()) {
            return $this->config->getRealTableName();
        }
        //先去除前缀
        $tableName = substr($this->config->getTableName(), strlen($this->config->getTablePre()));
        //去除后缀
        foreach ($this->config->getIgnoreString() as $string) {
            $tableName = rtrim($tableName, $string);
        }
        //下划线转驼峰,并且首字母大写
        $tableName = ucfirst(Str::camel($tableName));
        $this->config->setRealTableName($tableName);
        return $tableName;
    }
    function getColumnDeaflutValue($column)
    {

    }

    /**
     * convertDbTypeToDocType
     * @param $fieldType
     * @return string
     * @author Tioncico
     * Time: 19:49
     */
    protected function convertDbTypeToDocType($fieldType)
    {
        $newFieldType = strtolower(strstr($fieldType, '(', true));
        if ($newFieldType == '') $newFieldType = strtolower($fieldType);
        if (in_array($newFieldType, ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'])) {
            $newFieldType = 'int';
        } elseif (in_array($newFieldType, ['float', 'double', 'real', 'decimal', 'numeric'])) {
            $newFieldType = 'float';
        } elseif (in_array($newFieldType, ['char', 'varchar', 'text'])) {
            $newFieldType = 'string';
        } else {
            $newFieldType = 'mixed';
        }
        return $newFieldType;
    }

    /**
     * createPHPDocument
     * @param $fileName
     * @param $fileContent
     * @param $tableColumns
     * @return bool|int
     * @author Tioncico
     * Time: 19:49
     */
    protected function createPHPDocument($fileName, $fileContent, $tableColumns)
    {
        if ($this->config->isConfirmWrite()) {
            if (file_exists($fileName . '.php')) {
                echo "(Controller)当前路径已经存在文件,是否覆盖?(y/n)\n";
                if (trim(fgets(STDIN)) == 'n') {
                    echo "已结束运行\n";
                    return false;
                }
            }
        }
        $content = "<?php\n\n{$fileContent}\n";
        $result = file_put_contents($fileName . '.php', $content);

        return $result == false ? $result : $fileName . '.php';
    }


}
