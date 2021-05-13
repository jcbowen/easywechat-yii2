<?php

namespace jcbowen\yiieasywechat\v5;

use jcbowen\yiieasywechat\components\Agent;
use yii\base\Component;
use EasyWeChat\Factory;

/**
 *
 * Class EasyWeChat
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2021/5/13 4:48 下午
 * @package jcbowen\yiieasywechat
 *
 * @property \EasyWeChat\OfficialAccount\Application $wechat 微信实例
 * @property \EasyWeChat\Payment\Application $wxpay 微信支付实例
 * @property \EasyWeChat\MiniProgram\Application $miniProgram 微信小程序实例
 * @property \EasyWeChat\OpenPlatform\Application $openPlatform 微信开放平台实例
 * @property \EasyWeChat\Work\Application $wxWork 企业微信实例
 * @property \EasyWeChat\OpenWork\Application $openWork 企业微信开放平台实例
 * @property \EasyWeChat\MicroMerchant\Application $microMerchant 小微商户实例
 */
class EasyWeChat extends Component
{
    /**
     * 存放用户信息session的key
     * @var string
     */
    public $SessionKeyUser = '_EasyWechatUser';

    /**
     * @var array
     */
    public $rebinds = [];

    /**
     * 浏览器类型
     * @var string
     */
    public $container = 'unknown';

    /**
     * 实力化应用SDK
     *
     * @var Factory
     */
    private static $_app;

    private $_config = [];

    public function __construct($config = [])
    {
        global $_B;
        $this->_config = $config;
        parent::__construct($config);

        $browserType = Agent::browserType();
        if (Agent::MICRO_MESSAGE_WORK_YES == Agent::isMicroMessage()) {
            $_B['container'] = 'WxWork';
        } elseif (Agent::MICRO_MESSAGE_YES == Agent::isMicroMessage()) {
            $_B['container'] = 'WeChat';
        } elseif (Agent::BROWSER_TYPE_ANDROID == $browserType) {
            $_B['container'] = 'Android';
        } elseif (Agent::BROWSER_TYPE_IPAD == $browserType) {
            $_B['container'] = 'Ipad';
        } elseif (Agent::BROWSER_TYPE_IPHONE == $browserType) {
            $_B['container'] = 'Iphone';
        } elseif (Agent::BROWSER_TYPE_IPOD == $browserType) {
            $_B['container'] = 'Ipod';
        } else {
            $_B['container'] = 'Unknown';
        }
        $this->container = $_B['container'];

        $this->getApp();
    }

    /**
     * 判断客户端是否为微信
     *
     * @return bool
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:03 下午
     */
    public function getIsWechat()
    {
        return $this->container === 'WeChat';
    }

    /**
     * 判断客户端是否为企业微信
     *
     * @return bool
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:03 下午
     */
    public function getIsWxwork()
    {
        return $this->container === 'WxWork';
    }


    /**
     * 获取EasyWeChat实例
     * （目前仅支持WeChat获取）
     *
     * @return Factory
     */
    public function getApp()
    {
        if (!self::$_app) {
            switch ($this->container) {
                case 'WeChat':
//                case 'WxWork':
                    $nameSpace = '\jcbowen\yiieasywechat\v5\%s\Main';
                    $nameSpace = sprintf($nameSpace, $this->container);
                    self::$_app = new $nameSpace($this->SessionKeyUser, $this->rebinds, $this->_config);
                    break;
                default:
                    break;
            }
        }
        return self::$_app;
    }


    /**
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:22 下午
     *
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (\Exception $e) {
            throw $e->getPrevious();
        }
    }

}
