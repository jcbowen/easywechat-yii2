<?php

namespace Jcbowen\EasyWechat5Yii2\components;

use Yii;
use yii\base\ExitException;
use yii\web\Response;

class Util
{
    public static function result($errCode = '0', string $errmsg = '', $data = [], array $params = [], string $returnType = 'exit')
    {
        $data  = (array)$data;
        $count = count($data);

        $errCode = (int)self::getResponseCode($errCode);
        $errmsg  = self::getResponseMsg($errmsg);
        $data    = self::getResponseData($data);

        $result = [
            'errcode' => $errCode,
            'code'    => $errCode,
            'errmsg'  => $errmsg,
            'msg'     => $errmsg,
            'count'   => $count,
            'data'    => $data
        ];
        if (!empty($params) && is_array($params)) {
            $result = array_merge($result, $params);
        }
        $result['totalCount'] = $result['count'];
        if ($_GET['print_result'] == 1) {
            print_r($result);
            self::_end();
        }
        if ($returnType == 'exit') {
            //  返回封装后的json格式数据
            $response             = Yii::$app->getResponse();
            $response->format     = Response::FORMAT_JSON;
            $response->data       = $result;
            $response->statusCode = 200;

            if ($errCode != 0 && $errCode != 200) {
                $response->send();
                self::_end(0, $response);
            }

            return $response;
        } else {
            return stripslashes(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 获取接口返回的数据
     * @param $data
     * @return array|int|mixed|string
     */
    private static function getResponseData($data)
    {
        if (is_array($data) || is_string($data) || is_numeric($data)) return $data;
        if (is_object($data)) {
            if ($data instanceof Response) {
                self::_end(0, $data);
                return [];
            }
            if (method_exists($data, 'toArray')) return $data->toArray();
        }
        return [];
    }

    /**
     * 获取接口返回的状态码
     * @return int
     */
    private static function getResponseCode($code)
    {
        if (is_numeric($code)) return $code;
        if ($code instanceof Response) {
            self::_end(0, $code);
            return intval($code);
        }
        return 0;
    }

    /**
     * 获取接口返回的消息
     * @param $msg
     * @return string
     */
    private static function getResponseMsg($msg): string
    {
        if (is_string($msg)) return $msg;

        if ($msg instanceof Response) {
            self::_end(0, $msg);
            return '';
        }

        return 'ok';
    }

    /**
     * 结束程序
     * @param int $status
     * @param null $response
     * @return void
     */
    private static function _end(int $status = 0, $response = null)
    {
        try {
            Yii::$app->end($status, $response);
        } catch (ExitException $e) {
        }
        exit;
    }
}
