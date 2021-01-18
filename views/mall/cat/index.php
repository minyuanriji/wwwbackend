<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

$components = [
    'com-cat-list',
    'com-transfer',
    'com-style'
];
$html = "";
foreach ($components as $component) {
    $html .= $this->renderFile(__DIR__ . "/{$component}.php") . "\n";
}
echo $html;
?>
<style>
    .new-table-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>分类列表</span>
                <div style="float: right;margin-top: -5px">
                    <el-button type="primary" @click="edit" size="small">添加分类</el-button>
                </div>
            </div>
        </div>
        <div class="new-table-body">
            <template>
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="商品分类" name="first">
                        <com-cat-list></com-cat-list>
                    </el-tab-pane>
                    <el-tab-pane label="商品分类转移" name="second">
                        <com-transfer></com-transfer>
                    </el-tab-pane>
                    <el-tab-pane label="分类样式" name="third">
                        <com-style></com-style>
                    </el-tab-pane>
                </el-tabs>
            </template>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: 'first',
            };
        },
        methods: {
            handleClick(tab, event) {
                console.log(tab, event);
            },
            // 编辑
            edit(id) {
                navigateTo({
                    r: 'mall/cat/edit',
                });
            },
        },
        mounted: function () {

        },
    });
</script>
