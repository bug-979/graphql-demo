<?php

use \think\facade\Env;
use \tomorrow\think\Tools\Tools;

$typeConfig = [
    // 类型注册表
    'types' => [
        'gql' => [],
    ],
    // 入口类型
    'schema' => [
        'gql',
    ],
    // 中间件
    'middleware' => [],
    // 路由前缀
    'routePrefix' => 'api/'
];
//获取Mutation下所有文件
$mutation = scandir(Env::get('root_path') . 'application/http/graph/Mutation');

//获取Query下所有文件
$query = scandir(Env::get('root_path') . 'application/http/graph/Query');

//获取Type下所有文件
$type = scandir(Env::get('root_path') . 'application/http/graph/Type');


//去除 . ..
$mutation = array_values(array_diff($mutation, ['.', '..']));

$query = array_values(array_diff($query, ['.', '..']));

$type = array_values(array_diff($type, ['.', '..']));

//判断类型是否被定义
if (empty($mutation) && empty($query) && empty($type)) {
    Tools::gqlErrors('类型未定义', '500');
}

//判断类型是否被定义
if (!empty($mutation)) {
    //遍历出Mutation
    foreach ($mutation as $key => $val) {
        //去除扩展名
        $mutationMax[$key] = basename($val, '.php');

        //首字母小写(驼峰)
        $mutationMin[$key] = lcfirst($mutationMax[$key]);

        //生成Mutation入口
        $typeConfig['types']['gql']['mutation'] = '\app\http\graph\Mutation';

        //注册Mutation类型
        $typeConfig['types'][$mutationMin[$key]]['mutation'] = '\app\http\graph\Mutation\\' . $mutationMax[$key];
    }
}

if (!empty($query)) {
    //遍历出Query
    foreach ($query as $key => $val) {
        //去除扩展名
        $queryMax[$key] = basename($val, '.php');

        //首字母小写(驼峰)
        $queryMin[$key] = lcfirst($queryMax[$key]);

        //生成Query入口
        $typeConfig['types']['gql']['query'] = '\app\http\graph\Query';

        //注册Query类型
        $typeConfig['types'][$queryMin[$key]]['query'] = '\app\http\graph\Query\\' . $queryMax[$key];
    }
}

if (!empty($type)) {
    //遍历出Type
    foreach ($type as $key => $val) {
        //去除扩展名
        $typeMax[$key] = basename($val, '.php');
        //首字母小写(驼峰)
        $typeMin[$key] = lcfirst($typeMax[$key]);
        //注册Type类型
        $typeConfig['types'][$typeMin[$key]]['type'] = '\app\http\graph\Type\\' . $typeMax[$key];
    }
}

return $typeConfig;

