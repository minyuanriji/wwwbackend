<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 更新插件数据
 * Author: zal
 * Date: 2020-04-17
 * Time: 17:16
 */

namespace app\forms\admin;

use app\core\ApiCode;
use app\core\cloud\CloudException;
use app\core\exceptions\ClassNotFoundException;
use app\models\BaseModel;

class PluginUpdateDataForm extends BaseModel
{
    public function search()
    {
        $list = \Yii::$app->plugin->getList();
        foreach ($list as &$item) {
            $item = $item->attributes;
            $item['plugin'] = null;
            $item['icon_url'] = null;
          /*  try {
                $plugin = \Yii::$app->plugin->getPlugin($item['name']);
                $detail = $this->getCloudPluginDetail($plugin->getName(), $plugin->getVersionFileContent());
                $item['plugin'] = [
                    'id' => $detail['id'],
                    'name' => $plugin->getName(),
                    'version' => $plugin->getVersionFileContent(),
                    'new_version' => $detail['new_version'],
                ];
                $item['icon_url'] = $plugin->getIconUrl();
            } catch (ClassNotFoundException $exception) {
            } catch (CloudException $exception) {
            }*/
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
            ],
        ];
    }

    private function getCloudPluginDetail($name, $localVersion)
    {
        $cacheKey = md5('CLOUD_PLUGIN_DETAIL_OF_' . $name . '_v' . $localVersion);
        $data = \Yii::$app->cache->get($cacheKey);
        if ($data) {
            return $data;
        }
        $data = \Yii::$app->cloud->plugin->getDetail([
            'name' => $name,
            'version' => $localVersion,
        ]);
        \Yii::$app->cache->set($cacheKey, $data, 300);
        return $data;
    }
}