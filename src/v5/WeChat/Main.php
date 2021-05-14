<?php

namespace jcbowen\yiieasywechat\v5\WeChat;

use Yii;
use yii\base\Component;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;

/**
 *
 * Class Main
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2021/5/13 7:21 下午
 * @package jcbowen\yiieasywechat\v5\wechat
 *
 * @property User $user
 */
class Main extends Component
{
    /**
     * 微信SDK
     * @var Application
     */
    public static $_app = 'Not Init';

    /**
     * @var User
     */
    private static $_user = 'Not Init';

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

    public function init()
    {
        parent::init();

        if (!self::$_app instanceof Application) {
            self::$_app = Factory::officialAccount(Yii::$app->params['WeChatConfig']);

            if (!empty($this->rebinds)) {
                $app = self::$_app;
                foreach ($this->rebinds as $key => $class) $app->rebind($key, new $class());
                self::$_app = $app;
            }
        }
    }

    /**
     * 获取 EasyWeChat 微信实例
     *
     * @return Factory|Application
     */
    public function getApp()
    {
        return self::$_app;
    }

    /**
     * 通过session判断当前用户是否已经授权
     *
     * @return bool
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 6:53 下午
     */
    public function isAuthorized(): bool
    {
        $hasSession = Yii::$app->session->has($this->SessionKeyUser);
        $sessionVal = Yii::$app->session->get($this->SessionKeyUser);
        return ($hasSession && !empty($sessionVal));
    }

    /**
     * 发起授权请求
     *
     * @return Yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 6:52 下午
     */
    public function authorizeRequired(): Yii\web\Response
    {
        $code = Yii::$app->request->get('code');
        if ($code) {
            // 接收微信的回调，并处理网页授权
            return $this->authorize(self::$_app->oauth->userFromCode($code));
        } else {
            // 将当前页面的绝对链接作为微信回调页面，并跳转到微信授权页面
            $this->setReturnUrl(Yii::$app->request->getUrl());
            return Yii::$app->response->redirect(self::$_app->oauth->scopes(['snsapi_userinfo'])->redirect(Yii::$app->request->absoluteUrl));
        }
    }

    /**
     *
     * @param \Overtrue\Socialite\User $user
     * @return \yii\web\Response
     * @lasttime: 2021/5/15 12:01 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     */
    public function authorize(\Overtrue\Socialite\User $user): \yii\web\Response
    {
        Yii::$app->session->set($this->SessionKeyUser, $user->toJSON());
        return Yii::$app->response->redirect($this->getReturnUrl());
    }

    /**
     * @param string|array $url
     */
    public function setReturnUrl($url)
    {
        Yii::$app->session->set($this->SessionKeyReturnUrl, $url);
    }

    /**
     *
     * @param null $defaultUrl
     * @return mixed|string
     * @lasttime: 2021/5/15 12:01 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getReturnUrl($defaultUrl = null): string
    {
        $url = Yii::$app->session->get($this->SessionKeyReturnUrl, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->getUrlManager()->createUrl($url);
            } else {
                $url = null;
            }
        }

        return $url === null ? Yii::$app->getHomeUrl() : $url;
    }

    /**
     * 实例化微信身份信息
     *
     * @return User|string
     * @lasttime: 2021/5/15 12:02 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getUser()
    {
        if (!$this->isAuthorized()) {
            return new User();
        }

        if (!self::$_user instanceof User) {
            $userInfo = Yii::$app->session->get($this->SessionKeyUser);
            $config = $userInfo ? (array)@json_decode($userInfo, true) : [];
            self::$_user = new User($config);
        }
        return self::$_user;
    }
}
