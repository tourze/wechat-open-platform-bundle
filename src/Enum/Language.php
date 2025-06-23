<?php

namespace WechatOpenPlatformBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 临时的 Language 枚举，用于解决依赖问题
 */
enum Language: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case zh_CN = 'zh_CN';
    case zh_TW = 'zh_TW';
    case en = 'en';

    public function getLabel(): string
    {
        return match ($this) {
            self::zh_CN => '简体中文',
            self::zh_TW => '繁体中文',
            self::en => '英文',
        };
    }
}