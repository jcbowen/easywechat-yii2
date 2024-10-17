<?php

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram\Express\Waybill;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['waybill'] = function ($app) {
            return new Client($app);
        };
    }
}
