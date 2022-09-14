<?php

namespace Jcbowen\EasyWechat5Yii2\WxWork;

use Closure;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use GuzzleHttp\Exception\GuzzleException;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Yii;
use yii\base\Component;
use EasyWeChat\Factory;
use EasyWeChat\Work\Application;
use EasyWeChat\Kernel\Messages\TextCard;
use Jcbowen\EasyWechat5Yii2\components\Util;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 * 企业微信 封装方法
 *
 * Class Main
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2022/9/13 1:50 PM
 * @package Jcbowen\EasyWechat5Yii2\wechat
 *
 * @property User $user
 */
class Main extends Component
{
    /**
     * 企业微信SDK
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
    public string $SessionKeyUser = '_WxWorkUser';

    /**
     * @var string
     */
    public string $SessionKeyReturnUrl = '_WxWorkReturnUrl';

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
            if (!empty(Yii::$app->params['WxWorkConfig'])) {
                self::$_app = Factory::work(Yii::$app->params['WxWorkConfig']);

                if (!empty($this->rebinds)) {
                    $app = self::$_app;
                    foreach ($this->rebinds as $key => $class) $app->rebind($key, new $class());
                    self::$_app = $app;
                }
            } else {
                throw new InvalidConfigException('WxWorkConfig Not Found');
            }
        }
    }

    /**
     * 获取 EasyWeChat 微信实例
     *
     * @return Application
     */
    public function getApp()
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
     * @lastTime 2022/9/13 2:30 PM
     * @param bool $goAuth 没获取网页授权的情况下是否进行授权跳转
     * @return Response
     * @throws AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @throws GuzzleException
     */
    public function authorizeRequired(bool $goAuth = true): Response
    {
        $code = Yii::$app->request->get('code');
        if (!empty($code)) {
            // 接收微信的回调，并处理网页授权
            return $this->authorize(self::$_app->oauth->userFromCode($code));
        } elseif ($goAuth) {
            // 将当前页面的绝对链接作为微信回调页面，并跳转到微信授权页面
            $this->setReturnUrl(Yii::$app->request->getUrl());
            return Yii::$app->response->redirect(self::$_app->oauth->redirect(Yii::$app->request->absoluteUrl));
        }
        return Util::result(1, '未知错误');
    }


    /**
     * 构造及处理扫码登录
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param string $redirect_uri
     * @param $callback
     * @return array|array[]|object|object[]|string|string[]|Response
     * @throws AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @throws GuzzleException
     * @lastTime 2022/9/13 1:47 PM
     */
    public function qrConnect($callback, string $redirect_uri = '')
    {
        if (!empty(Yii::$app->request->get('code'))) {
            $this->authorizeRequired(false);
            $fans = ArrayHelper::toArray($this->getUser());

            if ($callback instanceof Closure) {
                return $callback($fans);
            }

            return $fans;
        } else {
            $apiUrl = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?';
            $query  = [
                'appid'        => Yii::$app->params['WxWorkConfig']['corp_id'],
                'agentid'      => Yii::$app->params['WxWorkConfig']['agent_id'],
                'redirect_uri' => $redirect_uri ?: Yii::$app->request->absoluteUrl,
                'state'        => time()
            ];
            $apiUrl .= http_build_query($query);
            return Yii::$app->response->redirect($apiUrl);
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

    /**
     * 发送文字消息
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param string $to 企业微信UserId
     * @param string $msg 文字消息
     * @return mixed
     * @throws RuntimeException
     * @lasttime: 2022/9/13 2:30 PM
     * @throws InvalidArgumentException
     */
    public function sendText(string $msg, string $to)
    {
        $messager = $this->getApp()->messenger;
        return $messager->message($msg)->toUser($to)->send();
    }

    /**
     * 发送卡片消息
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @param string $to 企业微信UserId
     * @param array $msg
     * [
     * 'title'       => '测试审批',
     * 'description' => '单号：1928373, ....',
     * 'url'         => 'http://www.jiuchet.com'
     * ]
     * @return mixed
     * @throws RuntimeException
     * @lasttime: 2022/9/13 1:56 PM
     * @throws InvalidArgumentException
     */
    public function sendCard(array $msg, string $to)
    {
        $messenger = $this->getApp()->messenger;

        $message = new TextCard($msg);

        return $messenger->message($message)->toUser($to)->send();
    }
}
