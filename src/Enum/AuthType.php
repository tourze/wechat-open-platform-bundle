<?php

namespace WechatOpenPlatformBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 临时的 AuthType 枚举，用于解决依赖问题
 */
enum AuthType: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case BASE = 'base';
    case USER_INFO = 'user_info';

    public function getLabel(): string
    {
        return match ($this) {
            self::BASE => '基础授权',
            self::USER_INFO => '用户信息授权',
        };
    }
}