<?php


namespace tomorrow\think\Tools;


use GraphQL\Deferred;
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
    public static function gqlErrors($message, $code = 500)
    {
        header('Content-type: application/json', true, $code);
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
            self::gqlErrors('数据表' . $tableName . '不存在', 500);
        }
    }

    /**
     * @param $type
     * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType
     * 处理数据库字段类型
     */
    protected static function analysis($type)
    {
        if ($type === 'int') {
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
    public static function addField($tableName, $field)
    {
        $fieldArray = self::fieldType($tableName);
        return array_merge($fieldArray, $field);
    }

    /**
     * @param $columnName
     * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType
     */
    public static function analysisColumn($columnName)
    {
        return self::analysis($columnName);
    }

    /**
     * 关联定义
     * @param string $tableName 表名
     * @param string $foreignKey 关联外键
     * @param string $localKey 当前主键
     * @param array $data 传入参数
     * @return Deferred
     */
    public static function hasMany($tableName, $foreignKey, $localKey, $data)
    {
        if (is_array($data)) {
            if (array_key_exists($localKey, $data)) {
                $key = $data[$localKey];
                // 先把所有ID存起来然后用 in [] 查询多个id
                Buffer::set($key);
                return new Deferred(function () use ($tableName, $foreignKey, $key) {
                    // 执行 in [] 查询
                    Buffer::loadBuffered($tableName, $foreignKey);
                    // 获取对应的关联数据
                    return Buffer::get($foreignKey, $key, 'hasMany');
                });
            } else {
                self::gqlErrors('类的属性不存在:' . $localKey);
            }
        } else {
            self::gqlErrors('expecting arguments to be arrays');
        }
    }

    /**
     * @param string $tableName 表名
     * @param string $foreignKey 关联外键
     * @param string $localKey 当前主键
     * @param array $data 传入参数
     * @return Deferred
     */
    public static function hasOne($tableName, $foreignKey, $localKey, $data)
    {
        if (is_array($data)) {
            if (array_key_exists($localKey, $data)) {
                $key = $data[$localKey];
                // 先把所有ID存起来然后用 in [] 查询多个id
                Buffer::set($key);
                return new Deferred(function () use ($tableName, $foreignKey, $key) {
                    // 执行 in [] 查询
                    Buffer::loadBuffered($tableName, $foreignKey);
                    // 获取对应的关联数据
                    return Buffer::get($foreignKey, $key, 'hasOne');
                });
            } else {
                self::gqlErrors('类的属性不存在:' . $localKey);
            }
        } else {
            self::gqlErrors('expecting arguments to be arrays');
        }
    }
}
