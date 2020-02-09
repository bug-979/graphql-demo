<?php
namespace tomorrow\think\Support;

use \GraphQL\Type\Definition\ResolveInfo;
use \GraphQL\Type\Definition\ObjectType as GraphQLObjectType;
use think\Db;

class ObjectQuery extends GraphQLObjectType
{
    public $typeConfig;

    public function __construct($args)
    {
        $this->typeConfig = $args;
        // 获取属性
        $attrs = $this->getAttrs($args);
        $self = $this;

        $config = [
            'name' => $attrs['name'],
            'description' => $attrs['desc'],
            'fields' => function () use ($self, $args,$attrs) {
                // 判断是否从args传入
                if (array_key_exists('fields', $args)) {
                    $fields = array_merge($self->fields(), $args['fields']);
                } else {
                    $fieldsObj = $self->fields();
                    foreach ($fieldsObj as $key => $val) {
                        $fieldName = lcfirst($val['type']->name);
                    }
                    //默认生成根据ID查询；
                    $detail = $self->detail(lcfirst($attrs['name']));
                    $fields = array_merge($self->fields(),$detail);
                }
                foreach ($fields as $key => &$field) {
                    if (is_array($field)) {
                        // 过滤fields简写
                        if (array_key_exists('desc', $field)) {
                            $field['description'] = $field['desc'];
                        }
                        // 过滤args简写
                        if (array_key_exists('args', $field) && is_array($field['args'])) {
                            foreach ($field['args'] as $key => &$arg) {
                                if (is_array($arg) && array_key_exists('desc', $arg)) {
                                    $arg['description'] = $arg['desc'];
                                }
                            }
                        }
                    }
                }
                return $fields;
            },
            'resolveField' => function($val, $args, $context, ResolveInfo $info) {
                // 如果定义了resolveField则使用它
                if (method_exists($this, 'resolveField')) {
                    return $this->resolveField($val, $args, $context, $info);
                }

                // 处理fieldsMap
                $fieldName = $info->fieldName;
                $fieldsMap = $this->fieldsMap();
                if (array_key_exists($fieldName, $fieldsMap)) {
                    $fieldName = $fieldsMap[$fieldName];
                }

                // 替换fieldName中的_下划线
                $methodName = "resolve" . str_replace('_', '', $fieldName);

                if (method_exists($this, $methodName)) {
                    return $this->{$methodName}($val, $args, $context, $info);
                } else {
                    return array_key_exists($fieldName, $val) ? $val[$fieldName] : null;
                }
            }
        ];

        parent::__construct($config);
    }

    public function attrs()
    {
        return [
            'name' => '',
            'desc' => ''
        ];
    }

    public function getAttrs($args)
    {
        $default = [
            'name' => '',
            'desc' => ''
        ];

        return array_merge($default, $this->attrs(), $args);
    }

    public function fields()
    {
        return $this::field();
    }

    public function fieldsMap()
    {
        return [];
    }
    public static function field () {
        return [];
    }

    public function detail ($fieldName) {
        return [
            'detail' => [
                'type' => Types::$fieldName('type'),
                'desc' => '根据ID查询',
                'args' => [
                    'id' => [
                        'type' => Types::nonNull(Types::id()),
                        'desc' => 'ID'
                    ],
                ],
                'resolve' => function ($val,$args) {
//                    return Db::name($fieldName)->where('id',$args['id'])->select();
                    return [
                        'id' => '1',
                        'title' => $args['id'],
                    ];
                },
            ],
        ];
    }
}