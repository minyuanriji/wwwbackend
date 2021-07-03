<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
$pluginUrl = \app\helpers\PluginHelper::getPluginBaseAssetsUrl();
$mallUrl = Yii::$app->request->hostInfo
    . Yii::$app->request->baseUrl
    . '/statics/img/app';
?>

<template id="diy-coupon">
    <div>
        <div class="diy-component-preview">
            <div class="diy-coupon" flex="dir:left">
                <div class="diy-coupon-one" flex="dir:left" :style="cStyle1" v-for="item in 2">
                    <div style="text-align: center;width: 215px">
                        <div style="height: 80px;line-height: 80px;font-size: 28px">￥1000</div>
                        <div style="height: 50px;line-height: 50px;font-size: 24px">满200元可用</div>
                    </div>
                    <div class="right" flex="main:center cross:center">立即领取</div>
                </div>
                <div class="diy-coupon-one" flex="dir:left" :style="cStyle2" v-for="item in 2">
                    <div style="text-align: center;width: 215px">
                        <div style="height: 80px;line-height: 80px;font-size: 28px">￥1000</div>
                        <div style="height: 50px;line-height: 50px;font-size: 24px">满200元可用</div>
                    </div>
                    <div class="right" flex="main:center cross:center">已领取</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="字体颜色">
                    <el-color-picker v-model="data.textColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="不可领取">
                    <com-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.receiveBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <com-gallery :url="data.receiveBg" :show-delete="true"
                                 @deleted="deletePic('receiveBg')"></com-gallery>
                </el-form-item>
                <el-form-item label="可领取">
                    <com-attachment title="选择图片" :multiple="false" :max="1" type="image"
                                    v-model="data.unclaimedBg">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸256*130"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <com-gallery :url="data.unclaimedBg" :show-delete="true"
                                 @deleted="deletePic('unclaimedBg')"></com-gallery>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-coupon', {
        template: '#diy-coupon',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    textColor: '#ffffff',
                    receiveBg: '<?= $mallUrl?>/coupon/icon-coupon-no.png',
                    unclaimedBg: '<?= $mallUrl?>/coupon/icon-coupon-index.png'
                },
                defaultData: {}
            };
        },
        created() {
            let data = JSON.parse(JSON.stringify(this.data));
            this.defaultData = data;
            if (!this.value) {
                this.$emit('input', data)
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cStyle1() {
                return `background-image: url('${this.data.unclaimedBg}');`
                    + `color: ${this.data.textColor}`;
            },
            cStyle2() {
                return `background-image: url('${this.data.receiveBg}');`
                    + `color: ${this.data.textColor}`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            deletePic(param) {
                this.data[param] = this.defaultData[param]
            }
        }
    });
</script>
<style>
    .diy-coupon {
        width: 100%;
        padding: 16px;
        min-height: 150px;
        background: #ffffff;
        overflow-x: auto;
        padding-left: 24px;
    }

    .diy-coupon .diy-coupon-one {
        width: 256px;
        height: 130px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        margin-right: 24px;
        flex: none;
    }

    .diy-coupon .diy-coupon-one .right {
        width: 1.6rem;
        font-size: 26px;
        line-height: 1.25;
        text-align: center;
        margin-right: 2px;
    }
</style>