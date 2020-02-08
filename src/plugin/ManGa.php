<?php

/**
 *  Website: https://mudew.com/
 *  Author: Lkeme
 *  License: The MIT License
 *  Email: Useri@live.cn
 *  Updated: 2020 ~ 2021
 */

namespace BiliHelper\Plugin;

use BiliHelper\Core\Log;
use BiliHelper\Core\Curl;
use BiliHelper\Util\TimeLock;

class ManGa
{
    use TimeLock;

    public static function run()
    {
        if (self::getLock() > time() || getenv('USE_MANGA') == 'false') {
            return;
        }
        if (self::sign() && self::share()) {
            self::setLock(24 * 60 * 60);
            return;
        }
        self::setLock(3600);
    }


    private static function sign(): bool
    {
        sleep(1);
        $payload = [
            'access_key' => getenv('ACCESS_TOKEN'),
            'ts' => time()
        ];
        $raw = Curl::post('https://manga.bilibili.com/twirp/activity.v1.Activity/ClockIn', Sign::api($payload));
        $de_raw = json_decode($raw, true);
        # {"code":0,"msg":"","data":{}}
        # {"code":"invalid_argument","msg":"clockin clockin is duplicate","meta":{"argument":"clockin"}}
        if (!$de_raw['code']) {
            Log::notice('漫画签到: 成功~');
        } else {
            Log::warning('漫画签到: 失败或者重复操作~');
        }
        return true;
    }


    private static function share(): bool
    {
        sleep(1);
        $payload = [];
        $url = "https://manga.bilibili.com/twirp/activity.v1.Activity/ShareComic";

        $raw = Curl::post($url, Sign::api($payload));
        $de_raw = json_decode($raw, true);
        # {"code":0,"msg":"","data":{"point":5}}
        # {"code":1,"msg":"","data":{"point":0}}
        if (!$de_raw['code']) {
            Log::notice('漫画分享: 成功~');
        } else {
            Log::warning('漫画分享: 失败或者重复操作~');
        }
        return true;
    }
}