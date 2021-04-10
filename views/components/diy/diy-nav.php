<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-23
 * Time: 10:30
 */
?>
<template id="diy-nav">
    <div class="diy-nav">
        <div class="diy-component-preview">
            <div class="nav-container" :style="cContainerStyle">
                <div :style="cStyle" flex="dir:left">
                    <div v-for="(navGroup,groupIndex) in cNavGroups" flex="dir:left"
                         style="width: 750px;flex-wrap:wrap;">
                        <div v-for="(nav,navIndex) in navGroup" :style="cNavStyle" class="nav-item">
                            <img :src="nav.icon">
                            <div :style="'color:'+data.color+';'">{{nav.name}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px">
                <el-form-item label="背景颜色">
                    <el-color-picker v-model="data.background"></el-color-picker>
                </el-form-item>
                <el-form-item label="文字颜色">
                    <el-color-picker v-model="data.color"></el-color-picker>
                </el-form-item>
                <el-form-item label="每页行数">
                    <el-input size="small" v-model.number="data.rows" type="number" min="1" max="100"></el-input>
                </el-form-item>
                <el-form-item label="每行个数">
                    <el-radio v-model="data.columns" :label="3">3</el-radio>
                    <el-radio v-model="data.columns" :label="4">4</el-radio>
                    <el-radio v-model="data.columns" :label="5">5</el-radio>
                </el-form-item>
<!--                <el-form-item label="左右滑动">-->
<!--                    <el-switch-->
<!--                            v-model="data.scroll"-->
<!--                            active-value="1"-->
<!--                            inactive-value="0"-->
<!---->
<!--                    ></el-switch>-->
<!--                </el-form-item>-->

                <el-form-item label="导航图标">
                    <draggable v-model="data.navs">
                        <div v-for="(nav,index) in data.navs" class="edit-nav-item">
                        <div class="nav-edit-options">
                            <el-button @click="navItemDelete(index)"
                                       type="primary"
                                       icon="el-icon-delete"
                                       style="top: -6px;right: -31px;"></el-button>
                        </div>
                        <div flex="dir:left box:first cross:center">
                            <div>
                                <com-image-upload style="margin-right: 5px;" v-model="nav.icon" width="100"
                                                  height="100"></com-image-upload>
                            </div>
                            <div>
                                <el-input v-model="nav.name" placeholder="名称" size="small"
                                          style="margin-bottom: 5px"></el-input>
                                <div @click="pickLinkClick(index)">
                                    <el-input v-model="nav.url" placeholder="点击选择链接" readonly
                                              size="small">
                                        <com-pick-link slot="append" @selected="linkSelected">
                                            <el-button size="small">选择链接</el-button>
                                        </com-pick-link>
                                    </el-input>
                                </div>
                            </div>
                        </div>
                    </div>
                    </draggable>
                    <el-button size="small" @click="addNav">添加图标</el-button>
                </el-form-item>
            </el-form>


        </div>
    </div>
</template>
<script src="//cdn.jsdelivr.net/npm/sortablejs@1.8.3/Sortable.min.js"></script>
<script>
    Vue.component('diy-nav', {
        template: '#diy-nav',
        props: {
            value: Object
        },
        data() {
            return {
                currentEditNavIndex: null,
                data: {
                    background: '#ffffff',
                    color: '#353535',
                    rows: 1,
                    columns: 3,
                    scroll: 1,
                    navs: [],
                },
                dialogTableVisible: false,
                page: 1,
                pageCount: 0,
                navList: [],
                listLoading: false,
                multipleSelection: [],
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
            cContainerStyle() {
                return `background:${this.data.background};overflow-x:${this.data.scroll == 1 ? 'auto' : 'hidden'};`;
            },
            cStyle() {
                let width = (this.cNavGroups.length ? this.cNavGroups.length : 1) * 750;
                return `width:${width}px;`;
            },
            cNavGroups() {
                const navGroups = [];
                const groupNavCount = this.data.rows * this.data.columns;
                for (let i in this.data.navs) {
                    const groupIndex = parseInt(i / groupNavCount);
                    if (!navGroups[groupIndex]) {
                        navGroups[groupIndex] = [];
                    }
                    navGroups[groupIndex].push(this.data.navs[i]);
                }
                return navGroups;
            },
            cNavStyle() {
                return `width:${100 / this.data.columns}%;`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal);
                },
            }
        },
        methods: {
            addNav() {
                this.data.navs.push({
                    icon: '',
                    name: '',
                    url: '',
                    openType: '',
                });
            },
            navItemDelete(index) {
                this.data.navs.splice(index, 1);
            },
            linkSelected(list, params) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentEditNavIndex !== null) {
                    this.data.navs[this.currentEditNavIndex].openType = link.open_type;
                    this.data.navs[this.currentEditNavIndex].url = link.new_link_url;
                    this.data.navs[this.currentEditNavIndex].params = link.params;
                    this.currentEditNavIndex = null;
                }
            },
            pickLinkClick(index) {
                this.currentEditNavIndex = index;
            },

            updateNav() {
                let self = this;
                self.multipleSelection.forEach(function (item, index) {
                    self.data.navs.push(item)
                });
                self.dialogTableVisible = false;
            }
        }
    });
</script>
<style>
    .diy-nav .nav-container {
        min-height: 100px;
        width: 100%;
        overflow-x: auto;
    }

    .diy-nav .nav-item {
        text-align: center;
        font-size: 24px;
        padding: 20px 0;
    }

    .diy-nav .nav-item > div {
        height: 25px;
        line-height: 25px;
    }

    .diy-nav .nav-item img {
        display: block;
        width: 88px;
        height: 88px;
        margin: 0 auto 5px auto;
    }

    .diy-nav .edit-nav-item {
        border: 1px solid #e2e2e2;
        line-height: normal;
        padding: 5px;
        margin-bottom: 5px;
    }

    .diy-nav .nav-icon-upload {
        display: block;
        width: 65px;
        height: 65px;
        line-height: 65px;
        border: 1px dashed #8bc4ff;
        color: #8bc4ff;
        background: #f9f9f9;
        cursor: pointer;
        background-size: 100% 100%;
        font-size: 28px;
        text-align: center;
        vertical-align: middle;
    }

    .diy-nav .nav-edit-options {
        position: relative;
    }

    .diy-nav .nav-edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }
</style>