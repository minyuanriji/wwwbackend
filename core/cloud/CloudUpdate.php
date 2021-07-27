<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 更新云插件
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:22
 */

namespace app\core\cloud;

use Alchemy\Zippy\Zippy;
use app\forms\common\CommonOption;
use app\logic\OptionLogic;
use app\models\Option;

class CloudUpdate extends CloudBase
{
    public $classVersion = '4.2.31';

    /**
     * 获取云插件版本数据
     * @return mixed
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function getVersionData()
    {
        $data = $this->httpGet('/mall/update/index');
        return $data;
    }

    /**
     * 更新
     * @return bool
     * @throws CloudException
     * @throws CloudNotLoginException
     */
    public function update()
    {
        $versionData = $this->getVersionData();
        if (!isset($versionData['next_version']) || !$versionData['next_version']) {
            throw new CloudException('已无新版本。');
        }
        $version = $versionData['next_version']['version'];
        $src = $versionData['next_version']['src_file'];
        $tempFile = \Yii::$app->runtimePath . '/update-package/' . $version . '/src.zip';
        $this->download($src, $tempFile);
        $zippy = Zippy::load();
        $archive = $zippy->open($tempFile);
        $archive->extract(\Yii::$app->basePath);
        $this->clearOpcache();
        unset($archive);

        $currentVersion = OptionLogic::get(Option::NAME_VERSION);
        if (!$currentVersion) {
            $currentVersion = '0.0.0';
        }
        $lastVersion = $currentVersion;

        $versions = require \Yii::$app->basePath . '/versions.php';
        foreach ($versions as $v => $f) {
            $lastVersion = $v;
            if (version_compare($v, $currentVersion) > 0) {
                if ($f instanceof \Closure) {
                    $f();
                }
            }
        }
        OptionLogic::set(Option::NAME_VERSION, $lastVersion);
        return true;
    }

    /**
     * 清理缓存
     */
    private function clearOpcache()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}
