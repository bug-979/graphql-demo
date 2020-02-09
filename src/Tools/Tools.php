<?php


namespace tomorrow\think\Tools;


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
}