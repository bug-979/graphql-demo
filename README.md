# graphql-demo
构建GraphQL服务

## 安装

```bash
$ composer require bug-979/graphql-demo:dev-master
```
注意：由于ThinkPHP 5.1对比5.0有较大改变，所以目前只支持新版5.1。

## 使用

首先需要在`/application/command.php`中增加一个指令。

```php
<?php

return [
    'tomorrow\think\GraphQLCommand'
];
```

然后在项目根目录下使用如下命令初始化框架

```bash
$ php think graph init
```

运行该命令之后如果提示初始化成功，则可以在`/config/graph.php`看到生成出的配置文件，以及在`/application/http/graph`文件夹下生成出的实例项目。

在初始化完毕之后，你可以使用GraphQL的测试工具请求`http://localhost/api/gql`进行尝试。
