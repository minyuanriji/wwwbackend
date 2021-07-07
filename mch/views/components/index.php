<?php
$components = [
    'com-attachment',
    'com-gallery',
    'com-ellipsis',
    'com-upload',
    'com-ellipsis',
    'com-image',
    'com-image-upload',
    'com-form',
    'com-district',
    /*'com-map',
    'com-upload',
    'com-export-dialog',
    'com-template',
    '',
    'com-test',*/
];
$html = "";
foreach ($components as $component) {
    $html .= $this->renderFile(__DIR__ . "/{$component}.php") . "\n";
}
echo $html;
