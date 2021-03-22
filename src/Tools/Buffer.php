<?php


namespace tomorrow\think\Tools;


use think\Db;

class Buffer
{
    /**
     * 缓存数据
     * @var array
     */
    private static $bufferData = [];
    /**
     * 缓存主键ID
     * @var array
     */
    private static $bufferID = [];

    /**
     * 每个关联累计调用次数
     * @var array
     */
    private static $num = [];

    /**
     * 储存主键ID
     * @param string $localKey 当前主键
     */
    public static function set($tableName, $localKey)
    {
        // 储存主键ID
//        array_push(self::$bufferID[$tableName], $localKey);
        self::$bufferID[$tableName][] = $localKey;
        self::$num[$tableName] = 0;
    }

    /**
     * @param string $foreignKey 关联外键
     * @param string $localKey 当前主键
     * @param string $relation 关联关系
     * @return array
     */
    public static function get($tableName, $foreignKey, $localKey, $relation)
    {
        // 累计调用次数
        self::$num[$tableName]++;
        // hasMany 多维数组
        $data = [];
        // hasOne 一维数组
        $detail = [];
        if ($relation === 'hasMany') {
            foreach (self::$bufferData[$tableName] as $key => $val) {
                if ($val[$foreignKey] === $localKey) {
                    $data[$tableName][] = $val;
                    //unset(self::$bufferData['cache'][$key]);// 清除已被返回的数组
                }
            }
            if (empty($data[$tableName])) {
                return null;
            }
            if (count(self::$bufferID[$tableName]) === self::$num[$tableName]) {
                self::$num[$tableName] = 0;
                self::$bufferID[$tableName] = [];
                self::$bufferData[$tableName] = [];
            }
            return $data[$tableName];
        } else {
            foreach (self::$bufferData[$tableName] as $key => $val) {
                if ($val[$foreignKey] === $localKey) {
                    $detail[$tableName] = $val;
                }
            }
            if (empty($detail[$tableName])) {
                return null;
            }
            if (count(self::$bufferID[$tableName]) === self::$num[$tableName]) {
                self::$num[$tableName] = 0;
                self::$bufferID[$tableName] = [];
                self::$bufferData[$tableName] = [];
            }
            return $detail[$tableName];
        }
    }

    /**
     * @param string $tableName 表名
     * @param string $foreignKey 关联外键
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function loadBuffered($tableName, $foreignKey)
    {

        if (array_key_exists($tableName, self::$bufferData)) {
            if (empty(self::$bufferData[$tableName])) {
                self::$bufferData[$tableName] = Db::name($tableName)
                    ->where($foreignKey, 'in', self::$bufferID[$tableName])
                    ->where('is_delete', '=', 0)
                    ->select();
            } else {
                return;
            }
        } else {
            self::$bufferData[$tableName] = Db::name($tableName)
                ->where($foreignKey, 'in', self::$bufferID[$tableName])
                ->where('is_delete', '=', 0)
                ->select();
        }
    }
}
