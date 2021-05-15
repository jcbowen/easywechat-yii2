<?php

namespace jcbowen\yiieasywechat\v5;

use Exception;
use jcbowen\yiieasywechat\components\Agent;
use Yii;
use yii\base\Component;

/**
 *
 * Class EasyWeChat
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2021/5/13 4:48 下午
 * @package jcbowen\yiieasywechat
 *
 * @property \EasyWeChat\OfficialAccount\Application $WeChat 微信实例
 * @property \EasyWeChat\Payment\Application $WeChatPay 微信支付实例
 * @property \EasyWeChat\MiniProgram\Application $WeChatMiniProgram 微信小程序实例
 * @property \EasyWeChat\OpenPlatform\Application $WeChatOpenPlatform 微信开放平台实例
 * @property \EasyWeChat\Work\Application $WxWork 企业微信实例
 * @property \EasyWeChat\OpenWork\Application $WeChatOpenWork 企业微信开放平台实例
 * @property \EasyWeChat\MicroMerchant\Application $WeChatMicroMerchant 小微商户实例
 */
class EasyWeChat extends Component
{
    /**
     * 存放用户信息session的key
     * @var string
     */
    public string $SessionKeyUser = '_EasyWechatUser';

    /**
     * @var string
     */
    public string $SessionKeyReturnUrl = '_EasyWechatReturnUrl';

    /**
     * @var array
     */
    public array $rebinds = [];

    /**
     * 浏览器类型
     * @var string
     */
    public string $container = 'unknown';

    /**
     * 实例化应用SDK
     *
     * @var \jcbowen\yiieasywechat\v5\WeChat\Main
     * @var \jcbowen\yiieasywechat\v5\WxWork\Main
     */
    private static $_app = 'Not Init';

    public function init()
    {
        global $_B;

        parent::init();

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

        $_B['EasyWeChat']['configs'] = $this->getConfig();
    }

    /**
     * 判断客户端是否为微信
     *
     * @return bool
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:03 下午
     */
    public function getIsWechat(): bool
    {
        global $_B;
        return $_B['isWechat'] = $this->container === 'WeChat';
    }

    /**
     * 判断客户端是否为企业微信
     *
     * @return bool
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:03 下午
     */
    public function getIsWxwork(): bool
    {
        global $_B;
        return $_B['isWxwork'] = $this->container === 'WxWork';
    }

    /**
     * 判断客户端是否为微信系浏览器
     *
     * @return bool
     * @lasttime: 2021/5/15 2:11 下午
     * @author Bowen
     * @email bowen@jiuchet.com
     * s     */
    public function getIsMicroMessage(): bool
    {
        global $_B;
        return $_B['isMicroMessage'] = ($this->getIsWechat() || $this->getIsWxwork());
    }

    /**
     * 获取EasyWeChat 页面类实例
     *
     * @return \jcbowen\yiieasywechat\v5\WeChat\Main|\jcbowen\yiieasywechat\v5\WxWork\Main|bool
     */
    public function getApp($appName = '')
    {
        $appName = $appName ? $appName : $this->container;
        if (!self::$_app || self::$_app === 'Not Init') {
            switch ($appName) {
                case 'WeChat':
                case 'WxWork':
                    $nameSpace = '\jcbowen\yiieasywechat\v5\%s\Main';
                    $nameSpace = sprintf($nameSpace, $appName);
                    self::$_app = new $nameSpace([
                        'SessionKeyUser'      => $this->SessionKeyUser,
                        'SessionKeyReturnUrl' => $this->SessionKeyReturnUrl,
                        'rebinds'             => $this->rebinds,
                    ]);
                    break;
                default:
                    self::$_app = null;
            }
        }
        return self::$_app ? self::$_app : false;
    }

    /**
     * 获取配置信息
     *
     * @return array[]
     * @lasttime: 2021/5/15 4:36 下午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    private function getConfig(): array
    {
        $checkArr = [
            'WeChat'              => [// 微信公众号
                'checkKey' => 'secret',
            ],
            'WeChatPay'           => [// 微信支付
                'checkKey' => 'key',
            ],
            'WeChatMiniProgram'   => [// 微信小程序
                'checkKey' => 'secret',
            ],
            'WeChatOpenPlatform'  => [// 微信开放平台
                'checkKey' => 'secret',
            ],
            'WxWork'              => [// 企业微信
                'checkKey' => 'secret',
            ],
            'WeChatOpenWork'      => [// 企业微信开放平台
                'checkKey' => 'secret',
            ],
            'WeChatMicroMerchant' => [// 小微商户
                'checkKey' => 'secret',
            ]
        ];

        $params = Yii::$app->params;

        $newArr = [];
        foreach ($checkArr as $k => $v) {
            $thisConfig = $params["{$k}Config"];
            if (!empty($thisConfig[$v['checkKey']])) {
                $newArr[$k] = $thisConfig;
            }
        }
        return $newArr;
    }

    /**
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 8:22 下午
     *
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (Exception $e) {
            throw $e->getPrevious();
        }
    }

}
