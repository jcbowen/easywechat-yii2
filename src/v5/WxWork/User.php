<?php

namespace jcbowen\yiieasywechat\v5\WxWork;

use yii\base\Component;

/**
 *
 * Class WxWorkUser
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
    public ?string $UserId;

    /**
     * @var string
     */
    public ?string $OpenId;

    /**
     * @var string
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
     * @lasttime: 2021/5/15 10:05 下午
     */
    public function init()
    {
        global $_B;
        parent::init();
        $_B['UserId'] = $this->UserId = $this->raw['UserId'];
        $_B['DeviceId'] = $this->DeviceId = $this->raw['DeviceId'];
        $_B['OpenId'] = $this->OpenId = $this->raw['OpenId'];
        $_B['external_userid'] = $this->external_userid = $this->raw['external_userid'];
    }
}
