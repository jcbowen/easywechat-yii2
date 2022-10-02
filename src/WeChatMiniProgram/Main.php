<?php

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram;

use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\MiniProgram\Application;
use Yii;
use yii\base\Component;
use EasyWeChat\Factory;

/**
 * 微信小程序
 *
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lasttime: 2022/9/16 2:53 PM
 * @package Jcbowen\EasyWechatYii2\WeChatMiniProgram
 */
class Main extends Component
{
    /**
     * 微信小程序实例
     * @var string|Application
     */
    public static $_app = 'Not Init';

    /**
     * @var array
     */
    public array $rebinds = [];

    /**
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @throws InvalidConfigException
     * @lasttime: 2022/9/16 2:53 PM
     */
    public function init()
    {
        parent::init();

        if (!self::$_app instanceof Application) {
            if (!empty(Yii::$app->params['WeChatMiniProgramConfig'])) {
                self::$_app = Factory::miniProgram(Yii::$app->params['WeChatMiniProgramConfig']);

                if (!empty($this->rebinds)) {
                    $app = self::$_app;
                    foreach ($this->rebinds as $key => $class) $app->rebind($key, new $class());
                    self::$_app = $app;
                }
            } else {
                throw new InvalidConfigException('WeChatMiniProgramConfig Not Found');
            }
        }
    }

    /**
     * 获取微信小程序实例
     *
     * @author Bowen
     * @email bowen@jiuchet.com
     *
     * @return Application|string
     * @lasttime: 2022/9/16 2:53 PM
     */
    public function get()
    {
        return self::$_app;
    }
}
