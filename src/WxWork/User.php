<?php

namespace Jcbowen\EasyWechat5Yii2\WxWork;

use yii\base\Component;

/**
 *
 * Class WxWorkUser
 * @author Bowen
 * @email bowen@jiuchet.com
 * @package Jcbowen\EasyWechat5Yii2
 * @lastTime 2022/9/13 1:50 PM
 */
class User extends Component
{
    /**
     * @var string
     */
    public string $id;

    /**
     * @var string|null
     */
    public ?string $UserId;

    /**
     * @var string|null
     */
    public ?string $OpenId;

    /**
     * @var string|null
     */
    public ?string $external_userid;

    /**
     * @var string
     */
    public string $DeviceId;

    /**
     * @var array
     */
    public array $raw;

    /**
     * 初始化操作
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @lasttime: 2022/9/13 2:31 PM
     */
    public function init()
    {
        parent::init();
    }
}
