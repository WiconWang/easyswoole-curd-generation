<?php

namespace ESGeneration\Cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ESGeneration\Builder\Project\InitFile;

class InitCommand extends Command
{


    protected function configure()
    {
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('init:framework')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('生成项目结构 - 仅建议在项目初始时使用')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('生成项目基本架构，初始化项目结构...')
            // 配置一个参数
//            ->addArgument('name', InputArgument::REQUIRED, 'what\'s model you want to create ?')
            // 配置一个可选参数
//            ->addArgument('action', InputArgument::OPTIONAL, '生成行为')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $beanConfig = new InitFile();

        $output->writeln('!! 注意本操作会覆盖已有项目文件，仅用于空白项目进行初始架构！');
        $output->writeln("!! 是否执行本操作? (y/n) 默认 y \n");
        if (trim(fgets(STDIN)) == 'n') {
            echo "- 已终止\n";
            return false;
        }

        // 生成路由
        $beanConfig->createRoute();
        $output->writeln('路由模块生成成功！如要删除请移除 Route.php');

        // 生成接口文档
        $beanConfig->createApiDoc();
        $output->writeln('接口文档模块生成成功！在 Swagger.php 中配置域名后可用swagger客户端访问接口文档，删除请移除此文档');


        // 生成工具
        $beanConfig->createUtilities();
        $output->writeln('工具模块路由生成成功！如要删除请移除 Utilities');

        // 生成配置文件
        $beanConfig->createConfig();
        $output->writeln('配置模块路由生成成功！如要删除请移除 Config');

        // 生成异常
        $beanConfig->createExceptions();
        $output->writeln('异常模块生成成功！如要删除请移除 Exception 文件夹和 EasySwooleEvent.php 中相关代码');

        // 生成核心base
//        $init = new \AutomaticGeneration\Init();
//        $init->initBaseModel();
//        $init->initBaseController();
//        $beanConfig->appendBase();
        $beanConfig->createBase();
        $output->writeln('Base相关文件生成成功！如要删除请移除 Model和HttpController下的 base.php');
    }
}