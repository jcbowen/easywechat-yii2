<?php

namespace jcbowen\yiieasywechat\v5\WxWork;

use yii\base\Component;


/**
 *
 * Class WechatUser
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2021/5/13 4:53 下午
 * @package jcbowen\yiieasywechat
 */
class User extends Component
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $nickname;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $avatar;
    /**
     * @var array
     */
    public $original;
    /**
     * @var \Overtrue\Socialite\AccessToken
     */
    public $token;
    /**
     * @var string
     */
    public $provider;

    /**
     * @return string
     */
    public function getOpenId()
    {
        return isset($this->original['openid']) ? $this->original['openid'] : '';
    }
}
