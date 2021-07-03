<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>
<template id="diy-empty">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 20px 0">
                <div class="diy-empty" :style="cStyle"></div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.background"></el-color-picker>
                </el-form-item>
                <el-form-item label="高度">
                    <el-input size="small" v-model.number="data.height" type="number" min="1">
                        <div slot="append">px</div>
                    </el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-empty', {
        template: '#diy-empty',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    background: '#ffffff',
                    height: 10,
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
        },
        computed: {
            cStyle() {
                return `background: ${this.data.background};`
                    + `height: ${this.data.height}px;`;
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
        methods: {}
    });
</script>