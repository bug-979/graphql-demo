<?php


namespace tomorrow\think\Tools;


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
     * @param $tableName
     * @return array
     * 生成Type
     */
    public static function fieldType($tableName)
    {
        $sql = "select column_name, column_comment,data_type from INFORMATION_SCHEMA.Columns where table_name='$tableName'";
        $data = Db::query($sql);
        if (empty($data)) {
            self::gqlErrors(''.$tableName.'数据表不存在','500');
        }
        $field = [];
        foreach ($data as $key => $val) {
            $field[$val['column_name']] = [
                'type' => self::analysis($val['data_type']),
                'desc' => '测试',
            ];
        }
        return $field;
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
}