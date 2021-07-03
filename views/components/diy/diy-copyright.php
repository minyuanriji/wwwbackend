<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>

<template id="diy-copyright">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 28px 28px;"
                 :style="'background: ' + data.backgroundColor"
                 flex="main:center cross:center">
                <div>
                    <div v-if="data.picUrl" style="text-align: center;">
                        <img :src="data.picUrl" style="width: 160px;height: 50px;">
                    </div>
                    <div style="text-align: center;color: #333;">{{data.text}}</div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="版权文字">
                    <el-input size="small" v-model="data.text"></el-input>
                </el-form-item>
                <el-form-item label="版权图标">
                    <com-image-upload width="160" height="50" v-model="data.picUrl"></com-image-upload>
                </el-form-item>
                <el-form-item class="chooseLink" label="版权链接">
                    <el-input v-model="data.link.url" placeholder="点击选择链接" :disabled="true" size="small">
                        <com-pick-link slot="append" @selected="linkSelected">
                            <el-button size="small">选择链接</el-button>
                        </com-pick-link>
                    </el-input>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.backgroundColor"></el-color-picker>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-copyright', {
        template: '#diy-copyright',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    picUrl: '',
                    text: '',
                    link: {
                        url: '',
                        openType: '',
                        data: {},
                    },
                    backgroundColor: '#fff',
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            linkSelected(e) {
                if (!e.length) {
                    return;
                }
                this.data.link = {
                    url: e[0].new_link_url,
                    openType: e[0].open_type,
                    data: e[0],
                };

            },
        }
    });
</script>
<style>
    .chooseLink .el-input-group__append {
        background-color: #fff;
    }
</style>