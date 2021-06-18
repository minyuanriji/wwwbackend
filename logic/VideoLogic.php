<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 视频处理类
 * Author: zal
 * Date: 2020-09-21
 * Time: 14:36
 */

namespace app\logic;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

class VideoLogic
{
    /**
     * 获取视频图片
     * @param $saveFile
     * @param $saveViedoImg
     * @param $duration 时长
     */
    public static function getVideoImage($saveFile,$saveViedoImg,$duration = 1)
    {
        try {
            $config = isset(\Yii::$app->params["videoConfig"]) ? \Yii::$app->params["videoConfig"] : [];
            $config = [
                'ffmpeg.binaries'  => '/usr/local/ffmpeg/bin/ffmpeg',
                'ffprobe.binaries' =>  '/usr/local/ffmpeg/bin/ffprobe'
            ];
            $ffmpeg = FFMpeg::create($config);
            \Yii::error("ffmpeg——result:".json_encode($ffmpeg));
            $video = $ffmpeg->open($saveFile);
            $video->filters()
                ->resize(new Dimension(1320, 1240))
                ->synchronize();
            $video
                ->frame(TimeCode::fromSeconds($duration))
                ->save($saveViedoImg);
        } catch (\Exception $ex) {
            \Yii::error("videoHandle 缺少视频配置 error=".CommonLogic::getExceptionMessage($ex));
        }
    }

    /**
     * 获取视频时长
     * @param $saveFile
     * @return int|mixed
     */
    public static function getVideoDuration($saveFile)
    {
        $videoDuration = 0;
        try {
            $config = isset(\Yii::$app->params["videoConfig"]) ? \Yii::$app->params["videoConfig"] : "";
            $ffprobe = FFProbe::create($config);
            $videoDuration = $ffprobe->format($saveFile)->get('duration');
        } catch (\Exception $ex) {
            \Yii::error("videoHandle 缺少视频配置");
        }
        return $videoDuration;
    }
}