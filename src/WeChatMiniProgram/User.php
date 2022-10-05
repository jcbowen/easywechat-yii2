<?php

namespace Jcbowen\EasyWechatYii2\WeChatMiniProgram;

use EasyWeChat\Kernel\Support\AES;
use Jcbowen\JcbaseYii2\components\Component;
use yii\helpers\ArrayHelper;

/**
 *
 * Class WeChatMiniProgramUser
 * @author Bowen
 * @email bowen@jiuchet.com
 * @lastTime 2022/9/13 2:29 PM
 * @package Jcbowen\EasyWechatYii2
 */
class User extends Component
{
    /** @var string */
    public string $session_key;

    /** @var string */
    public string $openid;

    /** @var string */
    public string $unionid;

    /** @var string|null */
    public ?string $nickName;

    /** @var int|null */
    public ?int $gender;

    /** @var string|null */
    public ?string $language;

    /** @var string|null */
    public ?string $city;

    /** @var string|null */
    public ?string $province;

    /** @var string|null */
    public ?string $country;

    /** @var string|null */
    public ?string $avatarUrl;

    /** @var array|null */
    public ?array $watermark;

    public function init()
    {
        parent::init();
    }

    /**
     * 解析加密数据
     *
     * @author Bowen
     * @email 3308725087@qq.com
     *
     * @param string $iv
     * @param string $encrypted
     * @return User|array
     * @lasttime: 2022/10/2 22:58
     */
    public function decryptData(string $iv, string $encrypted)
    {
        $decrypted = AES::decrypt(
            base64_decode($encrypted),
            base64_decode($this->session_key),
            base64_decode($iv)
        );

        $decrypted = json_decode($decrypted, true);

        if (!$decrypted) {
            return [];
        }

        foreach ($decrypted as $key => $value) {
            $this->$key = $value;
        }

        return $this;
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
