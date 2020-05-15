<?php


namespace tomorrow\think;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Cache;
use tomorrow\think\Tools\Tools;

class SynchFields extends Command
{
    protected function configure()
    {
        $this->setName('SynchFields')
            ->setDescription('synch fields');
    }

    protected function execute(Input $input, Output $output)
    {
        //获取数据库配置
        $database = include './config/database.php';
        //获取配置中数据库名
        $tableName = $database['database'];
        //获取所有表
        $sql = "select table_name from information_schema.tables where table_schema='$tableName'";
        $data = Db::query($sql);
        if (!empty($data)) {
            //遍历出表里的字段
            foreach ($data as $key => $val) {
                $table_name = $val['table_name'];
                $sqlColumn = "select column_name, column_comment,data_type from INFORMATION_SCHEMA.Columns where table_name='$table_name'";
                $columnName = Db::query($sqlColumn);
                if (empty($columnName)) {
                    $output->writeln('synch fail');
                    exit();
                }
                //生成fields结构
                $field = [];
                foreach ($columnName as $index => $item) {
                    $field[$item['column_name']] = [
                        'type' => Tools::analysisColumn($item['data_type']),
                        'desc' => $item['column_comment'],
                    ];
                }
                Cache::set($table_name,$field);
            }
            $output->writeln('synch completed');
        } else {
            $output->writeln('synch fail');
        }
    }
}
