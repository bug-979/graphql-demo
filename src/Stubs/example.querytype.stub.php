<?php
namespace app\http\graph;

use \tomorrow\think\Support\GraphQLQuery;
use \tomorrow\think\Support\Types;
use \think\facade\Env;

class Query extends GraphQLQuery
{
    public function attrs()
    {
        return [
            'name' => 'Query',
            'desc' => '查询'
        ];
    }

    public function fields()
    {
        $queryConfig = [];

        //Query下所有文件
        $queryField = scandir(Env::get('root_path') . 'application/http/graph/Query');

        //去除 . ..
        $queryField = array_values(array_diff($queryField, ['.', '..']));

        //遍历Query类型
        foreach ($queryField as $key => $val) {

            //首字母小写(驼峰)
            $queryFieldMin[$key] = lcfirst(basename($val, '.php'));

            //生成数组
            $queryConfig[$queryFieldMin[$key]] = [
                'type' => Types::{$queryFieldMin[$key]}('query'),
                'desc' => Types::{$queryFieldMin[$key]}('query')->description,
                'resolve' => function () {
                    return [];
                }
            ];

            //fieldName拼接Query
            $queryConfig[$queryFieldMin[$key]]['type']->name = $queryConfig[$queryFieldMin[$key]]['type']->name . 'Query';
        }

        return $queryConfig;
    }
}
