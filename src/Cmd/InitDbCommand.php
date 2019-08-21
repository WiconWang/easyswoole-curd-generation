<?php

namespace ESGeneration\Cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ESGeneration\Builder\Project\InitFile;

//use ESGeneration\Builder;

class InitDbCommand extends Command
{


//    /**
//     * InitCommand constructor.
//     */
//    function __construct()
//    {
//        new Builder();
//    }
//


    protected function configure()
    {
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('init:database')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('生成项目数据库配置 - 仅建议在项目初始时使用')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('生成项目数据库配置，此命令依赖替换 EasySwooleEvent.php \ dev.php \ produce.php， 如果涉及文件结构有改动，有可能失败...')
            // 配置一个参数
//            ->addArgument('name', InputArgument::REQUIRED, 'what\'s model you want to create ?')
            // 配置一个可选参数
//            ->addArgument('action', InputArgument::OPTIONAL, '生成行为')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $beanConfig = new InitFile();
        $output->writeln('!! 数据库模块涉及多处系统文件，有失败可能！');
        $output->writeln('!! 如要删除请移除 EasySwooleEvent\dev\produce 中相关代码');
        $output->writeln("!! 是否执行本操作? (y/n) 默认 y \n");
        if (trim(fgets(STDIN)) == 'n') {
            echo "- 已终止\n";
            return false;
        }

        $beanConfig->createMysql();
        $output->writeln('数据库模块 生成成功！请到 dev.php 和 produce.php 中配置连接');

        $beanConfig->createRedis();
        $output->writeln('缓存模块 生成成功！请到 dev.php 和 produce.php 中配置连接');


        $beanConfig->createJson();
        $output->writeln('全站JSON响应模块 生成成功！');

        $beanConfig->createOrigin();
        $output->writeln('跨域模块 生成成功！请在 EasySwooleEvent.php 中配置');


    }
}