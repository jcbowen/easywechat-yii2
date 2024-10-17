<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jcbowen\EasyWechatYii2;

use EasyWeChat\Factory as EasyWeChatFactory;

/**
 * Class Factory.
 *
 * @method static WeChatMiniProgram\Application miniProgram(array $config)
 */
class Factory extends EasyWeChatFactory
{
    public static function __callStatic($name, $arguments)
    {
        if ('miniProgram' === $name)
            return new WeChatMiniProgram\Application(...$arguments);

        return parent::__callStatic($name, $arguments);
    }
}
