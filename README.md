# EasySwoole PHP 代码生成器

## 功能
简化 EasySwoole 的配置工具，快速生成相关配置、文档、Controller、Bean、Model等内容。    
本项目部分功能需改写框架核心配置文件，且未经过严格测试，请不要在生产环境使用，以防出现故障。   
本人会尽量完善此项目功能，但不对任何使用中出现的故障负责。

## 使用前提
本框架主要包含两大部分功能：    
    1. 初始化框架，非常合适于基于 easyswoole 核心文件的空白框架的构建。    
    2. 研发过程中，通过表结构，快速生成 CURD 及 研发 文档 。

## 安装说明
1. 安装EasySwoole，若已安装请跳过此步骤：  
    参照 [官方文档](https://www.easyswoole.com/Cn/Introduction/install.html)  或 使用以下命令：
    ```
    composer require easyswoole/easyswoole=3.x
    php vendor/bin/easyswoole install
    ```

2. 安装主程序  
    目前本项目需要指定项目目录，请在 composer.json 中，添加以下代码，以便可以使用本项目
    ```
    "autoload": {
        "psr-4": {
            "App\\": "App/"
        }
    },
    ```  
    执行以下命令：
    ```
    composer require wiconwang/easyswoole-generation
    php vendor/bin/cmd install
    ```
执行后，会在根目录生成 `cmd` 文件。    
如未成功生成，请到 `vendor/wiconwang/easyswoole-generation` 下，将 `cmd` 文件复制到项目根目录以便后期使用。



## 使用说明
请在项目根目录下执行 `php cmd` 查看具体命令。
空白项目，推荐执行顺序：
``` 
php cmd init:framework
php cmd init:database
```

## 研发文档
系统会自动生成研发文档，路径为 `/Swagger/getJson`    
可自行下载 [`swagger-ui`](https://swagger.io/tools/swagger-ui/download/) 来加载此地址。   
返回码接口路径为 `/Swagger/getReturnCode` ，可对接并显示所有返回码。