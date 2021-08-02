<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-24
 * Time: 19:32
 */
$components = [
    'com-attachment',
    'com-gallery',
    'com-picker',
    'com-pick-link',
    'com-banner',
    'com-image',
    'com-ellipsis',
    'com-map',
    'com-district',
    'com-upload',
    'com-export-dialog',
    'com-template',
    'com-image-upload',
    'com-form',
    'com-test',
];
$html = "";
foreach ($components as $component) {
    $html .= $this->renderFile(__DIR__ . "/{$component}.php") . "\n";
}
echo $html;
