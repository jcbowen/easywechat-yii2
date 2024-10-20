<?php

namespace Jcbowen\EasyWechatYii2;

use Exception;
use Jcbowen\EasyWechatYii2\components\Agent;
use Throwable;
use Yii;
use yii\base\Component;

/**
 *
 * Class WeChatSDK
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2022/9/13 2:31 PM
 * @package Jcbowen\EasyWechatYii2
 *
 * @property \EasyWeChat\OfficialAccount\Application $WeChat 微信实例
 * @property \EasyWeChat\Payment\Application $WeChatPay 微信支付实例
 * @property \EasyWeChat\MiniProgram\Application $WeChatMiniProgram 微信小程序实例
 * @property \EasyWeChat\OpenPlatform\Application $WeChatOpenPlatform 微信开放平台实例
 * @property \EasyWeChat\Work\Application $WxWork 企业微信实例
 * @property \EasyWeChat\OpenWork\Application $WeChatOpenWork 企业微信开放平台实例
 * @property \EasyWeChat\MicroMerchant\Application $WeChatMicroMerchant 小微商户实例
 */
class WeChatSDK extends Component
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
     * @var WeChat\Main|WxWork\Main|WeChatMiniProgram\Main
     */
    private static $_app;

    public function init()
    {
        parent::init();

        if (empty(Yii::$app->request->isConsoleRequest)) {
            $browserType = Agent::browserType();
            if (Agent::MICRO_MESSAGE_WORK_YES == Agent::isMicroMessage()) {
                $this->container = 'WxWork';
            } elseif (Agent::MICRO_MESSAGE_YES == Agent::isMicroMessage()) {
                $this->container = 'WeChat';
            } elseif (Agent::BROWSER_TYPE_ANDROID == $browserType) {
                $this->container = 'Android';
            } elseif (Agent::BROWSER_TYPE_IPAD == $browserType) {
                $this->container = 'Ipad';
            } elseif (Agent::BROWSER_TYPE_IPHONE == $browserType) {
                $this->container = 'Iphone';
            } elseif (Agent::BROWSER_TYPE_IPOD == $browserType) {
                $this->container = 'Ipod';
            } else {
                $this->container = 'Unknown';
            }
        } else {
            $this->container = 'Console';
        }
    }

    /**
     * 判断客户端是否为微信
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2022/9/13 2:32 PM
     * @return bool
     */
    public function getIsWeChat(): bool
    {
        return $this->container === 'WeChat';
    }

    /**
     * 判断客户端是否为企业微信
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2022/9/13 2:32 PM
     * @return bool
     */
    public function getIsWxWork(): bool
    {
        return $this->container === 'WxWork';
    }

    /**
     * 判断客户端是否为微信系浏览器
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @return bool
     * @lasttime: 2022/9/13 2:32 PM
     */
    public function getIsMicroMessage(): bool
    {
        return ($this->getIsWechat() || $this->getIsWxwork());
    }

    /**
     * 初始化SDK
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param string $appName 应用名称 ['WeChat', 'WxWork', 'WeChatMiniProgram']
     * @return WeChat\Main|WeChatMiniProgram\Main|WxWork\Main|mixed
     * @lasttime: 2022/9/16 3:14 PM
     */
    public function app(string $appName = '')
    {
        $appName = $appName ?: $this->container;
        $appName = in_array($appName, ['WeChat', 'WxWork', 'WeChatMiniProgram'], true) ? $appName : 'WeChat';

        if (!self::$_app || !self::$_app[$appName]) {
            switch ($appName) {
                case 'WeChat':
                case 'WxWork':
                    $nameSpace = '\Jcbowen\EasyWechatYii2\%s\Main';
                    $nameSpace = sprintf($nameSpace, $appName);

                    self::$_app[$appName] = new $nameSpace([
                        'SessionKeyUser'      => $this->SessionKeyUser,
                        'SessionKeyReturnUrl' => $this->SessionKeyReturnUrl,
                        'rebinds'             => $this->rebinds,
                    ]);

                    break;
                case 'WeChatMiniProgram':
                    $nameSpace = '\Jcbowen\EasyWechatYii2\%s\Main';
                    $nameSpace = sprintf($nameSpace, $appName);

                    self::$_app[$appName] = new $nameSpace([
                        'rebinds' => $this->rebinds,
                    ]);

                    break;
            }
        }

        return self::$_app[$appName];
    }

    /**
     * 获取配置信息
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @return array[]
     * @lasttime: 2022/9/13 2:32 PM
     */
    public function getConfig(): array
    {
        $checkArr = [
            // 微信公众号
            'WeChat'              => [
                'checkKey' => 'secret',
            ],
            // 微信支付
            'WeChatPay'           => [
                'checkKey' => 'key',
            ],
            // 微信小程序
            'WeChatMiniProgram'   => [
                'checkKey' => 'secret',
            ],
            // 微信开放平台
            'WeChatOpenPlatform'  => [
                'checkKey' => 'secret',
            ],
            // 企业微信
            'WxWork'              => [
                'checkKey' => 'secret',
            ],
            // 企业微信开放平台
            'WeChatOpenWork'      => [
                'checkKey' => 'secret',
            ],
            // 小微商户
            'WeChatMicroMerchant' => [
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
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2022/9/13 2:33 PM
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception|Throwable
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
