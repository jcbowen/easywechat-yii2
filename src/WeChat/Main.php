<?php

namespace Jcbowen\EasyWechat5Yii2\WeChat;

use GuzzleHttp\Exception\GuzzleException;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Yii;
use yii\base\Component;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * 微信公众号
 * Class Main
 *
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lasttime: 2022/9/13 1:51 PM
 * @package Jcbowen\EasyWechat5Yii2\WeChat
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
    public string $SessionKeyUser = '_WechatUser';

    /**
     * @var string
     */
    public string $SessionKeyReturnUrl = '_WechatReturnUrl';

    /**
     * @var array
     */
    public array $rebinds = [];

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!self::$_app instanceof Application) {
            if (!empty(Yii::$app->params['WeChatConfig'])) {
                self::$_app = Factory::officialAccount(Yii::$app->params['WeChatConfig']);

                if (!empty($this->rebinds)) {
                    $app = self::$_app;
                    foreach ($this->rebinds as $key => $class) $app->rebind($key, new $class());
                    self::$_app = $app;
                }
            } else {
                throw new InvalidConfigException('WeChatConfig Not Found');
            }
        }
    }

    /**
     * 获取 EasyWeChat 微信实例
     *
     * @return Application
     */
    public function get()
    {
        return self::$_app;
    }

    /**
     * 通过session判断当前用户是否已经授权
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2022/9/13 2:30 PM
     * @return bool
     */
    public function isAuthorized(): bool
    {
        $hasSession = Yii::$app->session->has($this->SessionKeyUser);
        $sessionVal = Yii::$app->session->get($this->SessionKeyUser);
        return ($hasSession && !empty($sessionVal));
    }

    /**
     * 处理网页授权
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2022/9/13 2:28 PM
     * @return Response
     * @throws AuthorizeFailedException
     * @throws InvalidConfigException
     * @throws GuzzleException
     */
    public function authorizeRequired(): Response
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
     * 将获取到的信息保存到session中，并跳转到设置的回调url中
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param \Overtrue\Socialite\User $user
     * @return Response
     * @lasttime: 2022/9/13 2:30 PM
     */
    public function authorize(\Overtrue\Socialite\User $user): Response
    {
        Yii::$app->session->set($this->SessionKeyUser, $user->toJSON());
        return Yii::$app->response->redirect($this->getReturnUrl());
    }

    /**
     * 储存回调url
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param string|array $url
     * @lasttime: 2022/9/13 2:30 PM
     */
    public function setReturnUrl($url)
    {
        Yii::$app->session->set($this->SessionKeyReturnUrl, $url);
    }

    /**
     * 获取并输出回调URL
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param null $defaultUrl
     * @return mixed|string
     * @lasttime: 2022/9/13 2:30 PM
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
     * 实例化粉丝身份信息
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @return User|string
     * @lasttime: 2022/9/13 2:30 PM
     */
    public function getUser()
    {
        if (!$this->isAuthorized()) {
            return new User();
        }

        if (!self::$_user instanceof User) {
            $userInfo    = Yii::$app->session->get($this->SessionKeyUser);
            $config      = $userInfo ? (array)@json_decode($userInfo, true) : [];
            self::$_user = new User($config);
        }
        return self::$_user;
    }
}
