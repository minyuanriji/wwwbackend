<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/23
 * Time: 11:17
 */
?>

<template id="diy-search">
    <div>
        <div class="diy-component-preview">
            <div class="diy-search" :style="cBackground">
                <div :style="cSearchBlock" style="">{{data.placeholder}}</div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px">
                <el-form-item label="搜索框颜色">
                    <el-color-picker v-model="data.color"></el-color-picker>
                </el-form-item>
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.background"></el-color-picker>
                </el-form-item>
                <el-form-item label="圆角">
                    <el-input size="small" v-model.number="data.radius" type="number">
                        <template slot="append">px</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="提示文字">
                    <el-input size="small" v-model="data.placeholder"></el-input>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker v-model="data.textColor"></el-color-picker>
                </el-form-item>
                <el-form-item label="文字位置">
                    <el-radio-group v-model="data.textPosition">
                        <el-radio label="left">居左</el-radio>
                        <el-radio label="center">居中</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-search', {
        template: '#diy-search',
        props: {
            value: Object
        },
        data() {
            return {
                data: {
                    color: '#ffffff',
                    background: '#f2f2f2',
                    radius: 4,
                    placeholder: '搜索',
                    textColor: '#555555',
                    textPosition: 'left',
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
            cBackground() {
                return `background: ${this.data.background}`;
            },
            cSearchBlock() {
                return `background: ${this.data.color};`
                    + `border-radius: ${this.data.radius}px;`
                    + `color: ${this.data.textColor};`
                    + `text-align: ${this.data.textPosition};`;
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
<style>
    .diy-search {
        padding: 24px;
        cursor: pointer;
    }

    .diy-search > div {
        height: 60px;
        line-height: 60px;
        padding: 0 24px;
        font-size: 28px;
    }
</style>