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
     * 累计调用次数
     * @var int
     */
    private static $num = 0;

    /**
     * 储存主键ID
     * @param string $localKey 当前主键
     */
    public static function set($localKey)
    {
        // 储存主键ID
        array_push(self::$bufferID, $localKey);
    }

    /**
     * @param string $foreignKey 关联外键
     * @param string $localKey 当前主键
     * @param string $relation 关联关系
     * @return array
     */
    public static function get($foreignKey, $localKey, $relation)
    {
        // 累计调用次数
        self::$num++;
        // hasMany 多维数组
        $data = [];
        // hasOne 一维数组
        $detail = [];
        if ($relation === 'hasMany') {
            foreach (self::$bufferData['cache'] as $key => $val) {
                if ($val[$foreignKey] === $localKey) {
                    $data[] = $val;
                    //unset(self::$bufferData['cache'][$key]);// 清除已被返回的数组
                }
            }
            if (empty($data)) {
                return null;
            }
            if (count(self::$bufferID) === self::$num) {
                self::$num = 0;
                self::$bufferID = [];
                self::$bufferData = [];
            }
            return $data;
        } else {
            foreach (self::$bufferData['cache'] as $key => $val) {
                if ($val[$foreignKey] === $localKey) {
                    $detail = $val;
                }
            }
            if (empty($detail)) {
                return null;
            }
            if (count(self::$bufferID) === self::$num) {
                self::$num = 0;
                self::$bufferID = [];
                self::$bufferData = [];
            }
            return $detail;
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

        if (array_key_exists('cache', self::$bufferData)) {
            if (empty(self::$bufferData['cache'])) {
                self::$bufferData['cache'] = Db::name($tableName)->where($foreignKey, 'in', self::$bufferID)->select();
            } else {
                return;
            }
        } else {
            self::$bufferData['cache'] = Db::name($tableName)->where($foreignKey, 'in', self::$bufferID)->select();
        }
    }
}
