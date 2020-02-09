<?php
namespace app\http\graph;

use \tomorrow\think\Support\GraphQLMutation;
use \tomorrow\think\Support\Types;
use \think\facade\Env;

class Mutation extends GraphQLMutation
{
    public function attrs()
    {
        return [
            'name' => 'Mutation',
            'desc' => '变更',
        ];
    }

    public function fields()
    {
        $mutationConfig = [];

        //Mutation下所有文件
        $mutationField = scandir(Env::get('root_path') . 'application/http/graph/Mutation');

        //去除 . ..
        $mutationField = array_values(array_diff($mutationField, ['.', '..']));

        //遍历Mutation类型
        foreach ($mutationField as $key => $val) {

            //首字母小写(驼峰)
            $mutationFieldMin[$key] = lcfirst(basename($val, '.php'));

            //生成数组
            $mutationConfig[$mutationFieldMin[$key]] = [
                'type' => Types::{$mutationFieldMin[$key]}('mutation'),
                'desc' => Types::{$mutationFieldMin[$key]}('mutation')->description,
                'resolve' => function () {
                    return [];
                }
            ];

            //fieldName拼接Mutation
            $mutationConfig[$mutationFieldMin[$key]]['type']->name = $mutationConfig[$mutationFieldMin[$key]]['type']->name . 'Mutation';
        }

        return $mutationConfig;
    }
}
