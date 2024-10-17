<?php

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram;

use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Jcbowen\EasyWechatYii2\Factory;
use Yii;
use yii\base\Component;

/**
 * 微信小程序
 *
 * @author  Bowen
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
     * @var User
     */
    private static $_user = 'Not Init';

    /**
     * @var array
     */
    public array $rebinds = [];

    /**
     *
     * @author  Bowen
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
     * @author  Bowen
     * @email bowen@jiuchet.com
     *
     * @return Application|string
     * @lasttime: 2022/9/16 2:53 PM
     */
    public function get()
    {
        return self::$_app;
    }

    /**
     * 实例化粉丝身份信息
     *
     * @author  Bowen
     * @email bowen@jiuchet.com
     * @return User|string
     * @lasttime: 2022/9/13 2:30 PM
     */
    public function getUser($session)
    {
        if (!self::$_user instanceof User) {
            self::$_user = new User($session);
        }
        return self::$_user;
    }
}
