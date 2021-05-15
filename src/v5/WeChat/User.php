<?php

namespace jcbowen\yiieasywechat\v5\WeChat;

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
    public string $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $nickname;

    /**
     * @var string
     */
    public string $avatar;

    /**
     * @var string|null
     */
    public ?string $email;

    /**
     * @var array
     */
    public array $raw;

    /**
     * @var string
     */
    public string $access_token;

    /**
     * @var string
     */
    public string $refresh_token;

    /**
     * @var int
     */
    public int $expires_in;

    /**
     * 数据初始化处理
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @lasttime: 2021/5/15 10:55 下午
     */
    public function init()
    {
        global $_B;
        parent::init();
        $_B['openid'] = $this->getOpenId();
        $_B['unionid'] = $this->getUnionid();
    }

    /**
     *
     * @return string
     * @lasttime: 2021/5/15 10:53 下午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getOpenId(): string
    {
        return isset($this->raw['openid']) ? $this->raw['openid'] : '';
    }

    /**
     *
     * @return string
     * @lasttime: 2021/5/15 10:53 下午
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getUnionid()
    {
        return isset($this->raw['unionid']) ? $this->raw['unionid'] : '';
    }
}
