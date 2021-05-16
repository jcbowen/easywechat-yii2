<?php

namespace jcbowen\yiieasywechat\components;

use Yii;
use yii\base\ExitException;
use yii\web\Response;

class Util
{
    public static function result($errCode = '0', $errmsg = '', $data = [], $params = [], $type = 'exit')
    {
        $req = Yii::$app->request;
        $data = (array)$data;
        $count = count($data);

        $errCode = (int)self::getResponseCode($errCode);
        $errmsg = (string)self::getResponseMsg($errmsg);
        $data = self::getResponseData($data);

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
        if (($req->isAjax && $type == 'exit') || $type == 'exit') {
//            if ($errcode != 0) die(stripslashes(json_encode($result, JSON_UNESCAPED_UNICODE)));
            //  返回封装后的json格式数据
            $response = Yii::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = $result;
            $response->statusCode = 200;

            if ($errCode != 0) {
                $response->send();
                self::_end(0, $response);
            }

            return $response;
        } else {
            return stripslashes(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    public static function result_r($errcode = '0', $errmsg = '', $data = [], $params = [])
    {
        return self::result($errcode, $errmsg, $data, $params, 'return');
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
            if ($data instanceof Response) return self::_end(0, $data);
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
        if (is_object($code) && $code instanceof Response) return self::_end(0, $code);
        return 0;
    }

    /**
     * 获取接口返回的消息
     * @return string
     */
    private static function getResponseMsg($msg)
    {
        if (is_string($msg)) return $msg;

        if (is_object($msg) && $msg instanceof Response) {
            return self::_end(0, $msg);
        }

        return 'ok';
    }

    /**
     * 结束程序
     * @param int $status
     * @param null $response
     * @return mixed
     */
    private static function _end($status = 0, $response = null)
    {
        try {
            Yii::$app->end($status, $response);
        } catch (ExitException $e) {
        }
        exit;
    }
}
