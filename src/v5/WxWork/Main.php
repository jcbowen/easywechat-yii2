<?php

namespace jcbowen\yiieasywechat\v5\WxWork;

use Closure;
use Yii;
use yii\base\Component;
use EasyWeChat\Factory;
use EasyWeChat\Work\Application;
use EasyWeChat\Kernel\Messages\TextCard;
use jcbowen\yiieasywechat\components\Util;
use yii\helpers\ArrayHelper;

/**
 * 企业微信 封装方法
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
        global $_B;
        parent::init();

        if (!self::$_app instanceof Application) {
            if (!empty($_B['EasyWeChat']['configs']['WxWork'])) {
                self::$_app = Factory::work($_B['EasyWeChat']['configs']['WxWork']);

                if (!empty($this->rebinds)) {
                    $app = self::$_app;
                    foreach ($this->rebinds as $key => $class) $app->rebind($key, new $class());
                    self::$_app = $app;
                }
            } else {
                return Util::result(9001002, '企业微信配置信息不存在');
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
     * 处理网页授权
     *
     * @param bool $goAuth 没获取网页授权的情况下是否进行授权跳转
     * @return Yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/13 6:52 下午
     */
    public function authorizeRequired($goAuth = true): Yii\web\Response
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
     * @param $callback
     * @param string $redirect_uri
     *
     * @return array|array[]|object|object[]|string|string[]|\yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Overtrue\Socialite\Exceptions\AuthorizeFailedException
     * @throws \yii\base\InvalidConfigException
     * @author Bowen
     * @email bowen@jiuchet.com
     * @lastTime 2021/5/16 9:32 下午
     */
    public function qrConnect($callback, $redirect_uri = '')
    {
        global $_B;
        if (!empty(Yii::$app->request->get('code'))) {
            $this->authorizeRequired(false);
            $_B['fans'] = ArrayHelper::toArray($this->getUser());

            if (is_object($callback) && ($callback instanceof Closure)) {
                return $callback($_B['fans']);
            }

            return $_B['fans'];
        } else {
            $apiUrl = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?';
            $query = [
                'appid'        => $_B['EasyWeChat']['configs']['WxWork']['corp_id'],
                'agentid'      => $_B['EasyWeChat']['configs']['WxWork']['agent_id'],
                'redirect_uri' => $redirect_uri ? $redirect_uri : Yii::$app->request->absoluteUrl,
                'state'        => time()
            ];;
            $apiUrl .= http_build_query($query);
            return Yii::$app->response->redirect($apiUrl);
        }
    }

    /**
     * 将获取到的信息保存到session中，并跳转到设置的回调url中
     *
     * @param \Overtrue\Socialite\User $user
     * @return \yii\web\Response
     * @lasttime: 2021/5/15 12:01 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function authorize(\Overtrue\Socialite\User $user): \yii\web\Response
    {
        Yii::$app->session->set($this->SessionKeyUser, $user->toJSON());
        return Yii::$app->response->redirect($this->getReturnUrl());
    }

    /**
     * 储存回调url
     *
     * @param string|array $url
     * @lasttime: 2021/5/15 10:02 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function setReturnUrl($url)
    {
        Yii::$app->session->set($this->SessionKeyReturnUrl, $url);
    }

    /**
     * 获取并输出回调URL
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
     * 实例化粉丝身份信息
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

    /**
     * 发送文字消息
     *
     * @param string $msg 文字消息
     * @param string $to 企业微信UserId
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @lasttime: 2021/5/16 12:29 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function sendText(string $msg, string $to)
    {
        $messager = $this->getApp()->messenger;
        return $messager->message($msg)->toUser($to)->send();
    }

    /**
     * 发送卡片消息
     *
     * @param array $msg
     * [
     * 'title'       => '测试审批',
     * 'description' => '单号：1928373, ....',
     * 'url'         => 'http://www.jiuchet.com'
     * ]
     * @param string $to 企业微信UserId
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @lasttime: 2021/5/16 12:29 上午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function sendCard(array $msg, string $to)
    {
        $messager = $this->getApp()->messenger;

        $message = new TextCard($msg);

        return $messager->message($message)->toUser($to)->send();
    }
}
