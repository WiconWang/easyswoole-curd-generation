<?php

namespace ESGeneration\Cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ESGeneration\Builder\Project\Curd;

class BeanCommand extends Command
{
    protected function configure()
    {
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('make:bean')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('生成 Bean')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('根据数据库表，生成 Bean文件...')
            // 配置一个参数
            ->addArgument('table_name', InputArgument::REQUIRED, '请输入表名！');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $tableName = $input->getArgument('table_name');
        $beanConfig = new Curd();
        $beanConfig->makeBean($tableName);
        return true;
    }
}