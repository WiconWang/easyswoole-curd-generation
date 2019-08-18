#!/usr/bin/env php
<?php

/**
 * 557 EasySwoole 命令行生成工具
 * @author WiconWang <WiconWang@gmail.com>
 * @copyright  2019/8/17 下午10:23
 */


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
new ESGeneration\Builder();

$application = new Application();

// 注册我们编写的命令 (commands)
$application->add(new ESGeneration\Cmd\InitCommand());
$application->add(new ESGeneration\Cmd\InitDbCommand());
$application->add(new ESGeneration\Cmd\ModelCommand());
$application->add(new ESGeneration\Cmd\ControllerCommand());
$application->add(new ESGeneration\Cmd\BeanCommand());
$application->add(new ESGeneration\Cmd\Databases\TableCommand());
$application->add(new ESGeneration\Cmd\Databases\TableCreateCommand());
$application->add(new ESGeneration\Cmd\Databases\TableAlterCommand());



$application->run();