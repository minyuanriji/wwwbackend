<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 视频类
 * Author: zal
 * Date: 2020-04-20
 * Time: 10:01
 */

namespace app\forms\common\video;


class Video
{
    public static function getUrl($url)
    {
        if (strpos($url, 'v.qq.com') != -1) {
            $model = new TxVideo();
            return $model->getVideoUrl($url);
        } else {
            return $url;
        }
    }
}
