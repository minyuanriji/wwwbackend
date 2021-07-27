<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>

<template id="diy-video">
    <div>
        <div class="diy-component-preview">
            <div class="diy-video">
                <img :src="data.pic_url" style="width: 100%;height:100%;" v-if="data.pic_url">
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="视频封面图片">
                    <com-attachment title="选择图片" :multiple="false" :max="1" type="image" v-model="data.pic_url">
                        <el-tooltip class="item" effect="dark"
                                    content="建议尺寸750*400"
                                    placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <com-gallery :url="data.pic_url" :show-delete="true"
                                 @deleted="deletePic()"></com-gallery>
                </el-form-item>
                <el-form-item label="视频链接">
                    <label slot="label">视频链接
                        <el-tooltip class="item" effect="dark"
                                    content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input size="small" v-model="data.url" placeholder="请输入视频原地址或选择上传视频">
                        <template slot="append">
                            <com-attachment :multiple="false" :max="1" v-model="data.url"
                                            type="video">
                                <el-button size="mini">选择文件</el-button>
                            </com-attachment>
                        </template>
                    </el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-video', {
        template: '#diy-video',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    pic_url: '',
                    url: ''
                },
            }
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
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
            deletePic() {
                this.data.pic_url = '';
            }
        }
    });
</script>
<style>
    .diy-video {
        width: 100%;
        height: 400px;
        background: #353535;
    }

    .diy-video .el-input-group__append {
        background-color: #fff
    }
</style>