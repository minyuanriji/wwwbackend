<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-24
 * Time: 19:33
 */
?>
<style>
    .com-dialog-dialog {
        min-width: 700px;
    }
</style>
<template id="com-dialog-select">
    <div class="com-dialog-select">
        <el-dialog append-to-body :title="title" :visible.sync="visible" :close-on-click-modal="false"
                   custom-class="com-dialog-dialog" :before-close="close" @close="close">
            <div>
                <el-input v-model="search.keyword" placeholder="根据名称搜索" @keyup.enter.native="getDetail(1)">
                    <el-button slot="append" @click="getDetail(1)">搜索</el-button>
                </el-input>
                <el-table border v-loading="listLoading" :data="list" style="margin-top: 24px;"
                          @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="60px" label="ID" props="id" v-if="multiple">
                    </el-table-column>
                    <el-table-column width="100px" label="ID" props="id" v-else>
                        <template slot-scope="props">
                            <el-radio-group v-model="radioSelection" @change="handleSelectionChange(props.row)">
                                <el-radio :label="props.row.id"></el-radio>
                            </el-radio-group>
                        </template>
                    </el-table-column>
                    <el-table-column label="名称">
                        <template slot-scope="props">
                            <com-ellipsis :line="2">{{props.row[listKey]}}</com-ellipsis>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div style="margin-top: 24px;">
                <el-row>
                    <el-pagination
                        v-if="pagination"
                        style="display: inline-block;"
                        background
                        :page-size="pagination.pageSize"
                        @current-change="getDetail"
                        layout="prev, pager, next"
                        :total="pagination.total_count">
                    </el-pagination>
                    <el-button type="primary" size="small" style="float: right" @click="confirm">选择</el-button>
                </el-row>
            </div>
        </el-dialog>
        <div @click="click" style="display: inline-block">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('com-dialog-select', {
        template: '#com-dialog-select',
        props: {
			visible : {
				type: String,
			},
            url: {
                type: String,
                default: 'mall/goods/index'
            },
            multiple: Boolean,
            title: {
                type: String,
                default: '商品选择'
            },
            listKey: {
                type: String,
                default: 'name'
            },
            params: Object,
            display: Boolean,
            extraSearch: Object,
        },
        data() {
            return {
                visible: false,
                listLoading: false,
                list: [],
                pagination: null,
                radioSelection: 0,
                search: {
                    keyword: ''
                },
                multipleSelection: []
            }
        },

        methods: {
            close(){
                this.visible=false;
                this.$emit('close');
            },
            click() {
                this.getDetail(1);
                this.visible = !this.visible;
            },
            getDetail(page) {
                this.list = [];
                this.listLoading = true;
                let params = Object.assign({
                    r: this.url,
                    search: Object.assign(this.search, this.extraSearch),
                    page: page
                }, this.params);
                console.log(params);
                request({
                    params: params
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.listLoading = false;
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            confirm() {
                this.visible = false;
                this.$emit('selected', this.multipleSelection);
                this.$emit('input', this.multipleSelection);
            }
        }
    });
</script>
