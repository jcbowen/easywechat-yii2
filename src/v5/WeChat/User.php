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
     * @return string
     */
    public function getOpenId(): string
    {
        return isset($this->raw['openid']) ? $this->raw['openid'] : '';
    }
}
