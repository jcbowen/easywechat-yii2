<?php

namespace Jcbowen\EasyWechatYii2\WeChat;

use Jcbowen\EasyWechatYii2\components\Component;
use yii\helpers\ArrayHelper;

/**
 *
 * Class WechatUser
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2022/9/13 2:29 PM
 * @package Jcbowen\EasyWechatYii2
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
     * @var array
     */
    public array $token_response;

    /**
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @return string
     * @lasttime: 2022/9/13 2:29 PM
     */
    public function getOpenId(): string
    {
        return $this->raw['openid'] ?: '';
    }

    /**
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     * @return string
     * @lasttime: 2022/9/13 2:29 PM
     */
    public function getUnionid(): string
    {
        return $this->raw['unionid'] ?: '';
    }

    /**
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @param array $properties
     * @param bool $recursive
     * @return array
     * @lasttime: 2022/10/2 14:31
     */
    public function toArray(array $properties = [], bool $recursive = true): array
    {
        return ArrayHelper::toArray($this, $properties, $recursive);
    }
}
