<?php

namespace tomorrow\think\Support;

use \GraphQL\Type\Definition\ResolveInfo;
use \GraphQL\Type\Definition\ObjectType as GraphQLObjectType;
use think\Db;
use think\Validate;
use tomorrow\think\Tools\Tools;

class ObjectQuery extends GraphQLObjectType
{
    public $typeConfig;

    public function __construct($args)
    {
        $this->typeConfig = $args;
        // 获取属性
        $attrs = $this->getAttrs($args);
        $self = $this;

        $paging = [
            'page' => [
                'type' => Types::int(),
                'description' => '页码',
                'defaultValue' => 1
            ],
            'limit' => [
                'type' => Types::int(),
                'description' => '限制',
                'defaultValue' => 10
            ]
        ];

        $config = [
            'name' => $attrs['name'],
            'description' => $attrs['desc'],
            'fields' => function () use ($self, $args, $attrs, $paging) {
                // 判断是否从args传入
                if (array_key_exists('fields', $args)) {
                    $fields = array_merge($self->fields(), $args['fields']);
                } else {
                    //默认生成根据ID查询;
                    $detail = $self->detail(lcfirst($attrs['name']));
                    $fields = array_merge($self->fields(), $detail);
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
                    //判断是否是分页类型
                    $pagingKey = substr($field['type']->name, -6);
                    if ($pagingKey === 'Paging') {
                        if (array_key_exists('args', $field) && is_array($field['args'])) {
                            $field['args'] = array_merge($field['args'], $paging);
                        } else {
                            $field['args'] = [
                                'page' => [
                                    'type' => Types::int(),
                                    'desc' => '页码',
                                    'defaultValue' => 1
                                ],
                                'limit' => [
                                    'type' => Types::int(),
                                    'desc' => '限制',
                                    'defaultValue' => 10
                                ]
                            ];
                        }
                    }
                }
                return $fields;
            },
            'resolveField' => function ($val, $args, $context, ResolveInfo $info) {
                //字段验证
                if (method_exists($this, 'validate')) {
                    $rule = $this->validate();
                    if (isset($rule['message'])) {
                        $validate = Validate::make($rule['rule'], $rule['message']);
                    } else {
                        $validate = Validate::make($rule['rule']);
                    }
                    if (!$validate->check($args)) {
                        Tools::gqlErrors($validate->getError(), 500);
                    }
                }
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
                    $fieds = $this->fields();
                    foreach ($fieds as $key => $item) {
                        if (is_array($item)) {
                            $pagingKey = substr($item['type']->name, -6);
                            if ($pagingKey === 'Paging') {
                                $redata = $this->{$methodName}($val, $args, $context, $info);
                                if ($redata && is_array($redata)) {
                                    if (!array_key_exists('total', $redata) || empty($redata['total'])) {
                                        Tools::gqlErrors('total required?', 500);
                                    }
                                    if (!array_key_exists('data', $redata) || !is_array($redata)) {
                                        Tools::gqlErrors('data required?', 500);
                                    }
                                    return [
                                        'page' => $args['page'],
                                        'limit' => $args['limit'],
                                        'total' => $redata['total'],
                                        'paging' => $redata['data'],
                                    ];
                                } else {
                                    return null;
                                }
                            } else {
                                return $this->{$methodName}($val, $args, $context, $info);
                            }
                        }
                    }
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
        return [];
    }

    public function fieldsMap()
    {
        return [];
    }

    public function detail($fieldName)
    {
        return [
            'detail' => [
                'type' => Types::{$fieldName}('type'),
                'desc' => '根据ID查询',
                'args' => [
                    'id' => [
                        'type' => Types::nonNull(Types::id()),
                        'desc' => 'ID'
                    ],
                ],
                'resolve' => function ($val, $args) use ($fieldName) {
                    return Db::name($fieldName)->where('id', $args['id'])->find();
                },
            ],
        ];
    }
}
