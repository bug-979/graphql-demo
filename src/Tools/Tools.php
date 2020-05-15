<?php


namespace tomorrow\think\Tools;


use think\facade\Cache;
use think\Validate;
use tomorrow\think\Support\Types;
use think\Db;

class Tools
{
    /**
     * GraphQl 异常处理
     * @param string $message 返回内容
     * @param integer $code HTTP状态码
     */
    public static function gqlErrors($message, $code)
    {
        header('Content-type: application/json', true, 500);
        echo json_encode([
            'errors' => [
                'message' => $message,
                'code' => $code,
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }


    /**
     * @param string $tableName 表名
     * @return array
     * 生成Type
     */
    public static function fieldType($tableName)
    {
        //获取缓存
        $field = Cache::get($tableName);
        if ($field) {
            foreach ($field as $key => $val) {
                $field[$key]['type'] = Types::{strtolower($val['type']->name)}();
            }
            return $field;
        } else {
            self::gqlErrors('数据表不存在',500);
        }
    }

    /**
     * @param $type
     * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType
     * 处理数据库字段类型
     */
    protected static function analysis ($type) {
        if ($type === 'int' ) {
            return Types::int();
        }
        if ($type === 'varchar') {
            return Types::string();
        }
        if ($type === 'tinyint') {
            return Types::boolean();
        }
        if ($type === 'float') {
            return Types::float();
        }
        return Types::string();
    }

    /**
     * @param $tableName
     * @param $field
     * @return array
     * 添加Field字段
     */
    public static function addField ($tableName,$field) {
        $fieldArray = self::fieldType($tableName);
        return array_merge($fieldArray,$field);
    }

    /**
     * @param $columnName
     * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType
     */
    public static function analysisColumn ($columnName) {
        return self::analysis($columnName);
    }
}
