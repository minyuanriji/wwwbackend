<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */
$mchId = Yii::$app->admin->identity->mch_id;
Yii::$app->loadComponentView('goods/com-add-cat');
?>

<style>
    .com-search .search-box {
        margin-bottom: 10px;
    }

    .com-search .div-box {
        margin-right: 10px;
    }

    .com-search .input-item {
        display: inline-block;
        width: 250px;
    }

    .com-search .input-item .el-input__inner {
        border-right: 0;
    }

    .com-search .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .com-search .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .com-search .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .com-search .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .com-search .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .com-search .clear-where {
        color: #419EFB;
        cursor: pointer;
    }
</style>

<template id="com-search">
    <div class="com-search">
        <el-tabs v-if="tabs.length > 0" v-model="activeName" @tab-click="handleClick">
            <el-tab-pane v-for="(item, index) in tabs" :key="index" :label="item.name" :name="item.value"></el-tab-pane>
        </el-tabs>
        <div class="search-box" flex="dir:left cross-center">

            <div v-if="isShowSearch" class="input-item div-box" flex="cross-center">
                <div>
                    <el-input @keyup.enter.native="toSearch" size="small" placeholder="请输入商品ID或名称搜索"
                              v-model="search.keyword" clearable
                              @clear="toSearch">
                        <el-button slot="append" icon="el-icon-search" @click="toSearch"></el-button>
                    </el-input>
                </div>
            </div>
            <div v-if="isShowClear" @click="clearWhere" class="div-box clear-where" flex="cross:center">清空筛选条件</div>
        </div>
        <com-add-cat ref="cats" :new-cats="newSearch.cats" @select="selectCat" :mch_id="mch_id"></com-add-cat>
    </div>
</template>

<script>
    Vue.component('com-search', {
        template: '#com-search',
        props: {
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        keyword: '',
                        status: '-1',
                        sort_prop: '',
                        sort_type: '',
                        cats: [],
                        date_start: null,
                        date_end: null,
                    }
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '全部',
                            value: '-1'
                        },
                        {
                            name: '销售中',
                            value: '1'
                        },
                        {
                            name: '下架中',
                            value: '0'
                        },
                        {
                            name: '售罄',
                            value: '2'
                        },
                    ];
                }
            },
            isShowCat: {
                type: Boolean,
                default: true
            },
            isShowSearch: {
                type: Boolean,
                default: true
            },
            newActiveName: {
                type: String,
                default: '-1'
            }
        },
        data() {
            return {
                activeName: '-1',
                dialogVisible: false,
                dialogLoading: false,
                options: [],
                cats: [],
                children: [],
                third: [],
                datetime: [],
                mch_id: <?= $mchId ?>,
                isShowClear: false,
            }
        },
        methods: {
            handleClick(res) {
                this.search.status = this.activeName;
                this.getList();
            },
            toSearch() {
                this.dialogVisible = false;
                this.getList();
            },
            clearCat() {
                this.search.cats = [];
                this.getList();
            },
            changeTime() {
                if (this.datetime) {
                    this.search.date_start = this.datetime[0];
                    this.search.date_end = this.datetime[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.getList();
            },
            getList() {
                this.$emit('to-search', this.search);
                this.checkClear();
            },
            clearWhere() {
                this.search.cats = [];
                this.search.keyword = '';
                this.search.date_start = null;
                this.search.date_end = null;
                this.datetime = [];
                this.getList();
            },
            checkClear() {
                if (this.search.keyword || (this.search.cats && this.search.cats.length > 0)
                    || (this.search.date_start && this.search.date_end)) {
                    this.isShowClear = true;
                } else {
                    this.isShowClear = false;
                }
            },
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                });
                this.search.cats = arr;
                this.getList();
            },
        },
        created() {
            this.search = this.newSearch;
            if (this.search.date_start && this.search.date_end) {
                this.datetime = [this.search.date_start, this.search.date_end];
            }
            this.activeName = this.newActiveName;
            this.checkClear();
        }
    })
</script>