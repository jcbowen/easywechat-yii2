<?php

namespace Jcbowen\EasyWechat5Yii2\WeChat;

use yii\base\Component;

/**
 *
 * Class WechatUser
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2022/9/13 2:29 PM
 * @package Jcbowen\EasyWechat5Yii2
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
     * @lasttime: 2022/9/13 2:29 PM
     */
    public function init()
    {
        parent::init();
    }

    /**
     *
     * @return string
     * @lasttime: 2022/9/13 2:29 PM
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getOpenId(): string
    {
        return $this->raw['openid'] ?? '';
    }

    /**
     *
     * @return string
     * @lasttime: 2022/9/13 2:29 PM
     * @author Bowen
     * @email bowen@jiuchet.com
     */
    public function getUnionid(): string
    {
        return $this->raw['unionid'] ?? '';
    }
}
