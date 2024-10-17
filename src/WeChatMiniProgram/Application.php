<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram;

use EasyWeChat\MiniProgram\Application as EasyWeChatMiniProgramApplication;

/**
 * Class Application.
 *
 * @property Express\Waybill\Client $waybill
 */
class Application extends EasyWeChatMiniProgramApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [], array $prepends = [], string $id = null)
    {
        $this->providers[] = Express\Waybill\ServiceProvider::class;
        parent::__construct($config, $prepends, $id);
    }
}
