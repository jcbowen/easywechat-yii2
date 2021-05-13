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
 */
class Main extends Component
{
    /**
     * 微信SDK
     * @var null
     */
    public static $_app = null;

    /**
     * @var User
     */
    private static $_user;

    /**
     * 存放用户信息session的key
     * @var string
     */
    private $SessionKeyUser;

    public function __construct($SessionKeyUser = '_EasyWechatUser', $rebinds = [], $config = [])
    {
        parent::__construct($config);
        $this->SessionKeyUser = $SessionKeyUser;

        if (!self::$_app instanceof Application) {
            self::$_app = Factory::officialAccount(Yii::$app->params['WeChatConfig']);

            if (!empty($rebinds)) {
                $app = self::$_app;
                foreach ($rebinds as $key => $class) $app->rebind($key, new $class());
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
    public function isAuthorized()
    {
        $hasSession = Yii::$app->session->has($this->SessionKeyUser);
        $sessionVal = Yii::$app->session->get($this->SessionKeyUser);
        return ($hasSession && !empty($sessionVal));
    }

    /**
     * 发起授权请求
     *
     * @return \yii\console\Response|Yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 6:52 下午
     */
    public function authorizeRequired()
    {
        $code = Yii::$app->request->get('code');
        if (Yii::$app->request->get('code')) {
            // callback and authorize
            return $this->authorize($this->app->oauth->userFromCode($code));
        } else {
            // redirect to wechat authorize page
            $this->setReturnUrl(Yii::$app->request->getUrl());
            return Yii::$app->response->redirect($this->app->oauth->redirect(Yii::$app->request->absoluteUrl)->getTargetUrl());
        }
    }

    /**
     * @param \Overtrue\Socialite\User $user
     * @return yii\web\Response
     */
    public function authorize(\Overtrue\Socialite\User $user)
    {
        Yii::$app->session->set($this->SessionKeyUser, $user->toJSON());
        return Yii::$app->response->redirect($this->getReturnUrl());
    }

    /**
     * @param string|array $url
     */
    public function setReturnUrl($url)
    {
        Yii::$app->session->set($this->returnUrlParam, $url);
    }

    /**
     * @param null $defaultUrl
     * @return mixed|null|string
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $url = Yii::$app->session->get($this->returnUrlParam, $defaultUrl);
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
     * 获取微信身份信息
     *
     * @return User
     */
    public function getUser()
    {
        if (!$this->isAuthorized()) {
            return new User();
        }

        if (!self::$_user instanceof User) {
            $userInfo = Yii::$app->session->get($this->SessionKeyUser);
            $config = $userInfo ? json_decode($userInfo, true) : [];
            self::$_user = new User($config);
        }
        return self::$_user;
    }
}
