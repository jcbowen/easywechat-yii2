<?php

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram\Express\Waybill;

use EasyWeChat\Kernel\BaseClient;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

class Client extends BaseClient
{
    /**
     * 获取运力id列表get_delivery_list
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDeliveryList()
    {
        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/get_delivery_list', []);
    }

    // ----- 物流消息组件 ----- /

    /**
     * 传运单接口 follow_waybill
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param array $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function followWaybill(array $params = [])
    {
        if (empty($params['openid']) || empty($params['receiver_phone']) || empty($params['waybill_id']) || empty($params['goods_info']) || empty($params['trans_id'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }

        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/follow_waybill', $params);
    }

    /**
     * 查运单接口 query_follow_trace
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryFollowTrace($params)
    {
        if (empty($params['waybill_token'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }
        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/query_follow_trace', $params);
    }

    /**
     * 更新物品信息接口 update_follow_waybill_goods
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateFollowWaybillGoods($params)
    {
        if (empty($params['waybill_token']) || empty($params['goods_info'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }
        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/update_waybill_goods', $params);
    }

    // ----- 物流查询组件 ----- /

    /**
     * 传运单接口 trace_waybill
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param array $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function traceWaybill(array $params = [])
    {
        if (empty($params['openid']) || empty($params['receiver_phone']) || empty($params['waybill_id']) || empty($params['goods_info']) || empty($params['trans_id'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }

        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/trace_waybill', $params);
    }

    /**
     * 查询运单接口 query_trace
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function queryTrace($params)
    {
        if (empty($params['waybill_token'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }
        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/query_trace', $params);
    }

    /**
     * 更新物流信息接口 update_waybill_goods
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param $params
     *
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateWaybillGoods($params)
    {
        if (empty($params['waybill_token']) || empty($params['goods_info'])) {
            throw new InvalidArgumentException('Missing parameter.');
        }
        return $this->httpPostJson('cgi-bin/express/delivery/open_msg/update_waybill_goods', $params);
    }
}
