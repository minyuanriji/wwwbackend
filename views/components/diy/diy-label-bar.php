<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-25
 * Time: 12:34
 */


$baseUrl = Yii::$app->request->baseUrl;
$diyIconPath = "{$baseUrl}/statics/img/mall/diy";
?>
<template id="diy-label-bar">
    <div class="diy-label-bar">
        <div class="diy-component-preview">
            <div class="label-list label-list-top" v-if="labelList.length">
                <template v-for="(item,index) in labelList">
                    <div class="label-item" :class="index==label_index?'active':''">
                        <div class="label-name">{{item.title}}</div>
                        <div class="label-sub-name">{{item.sub_title}}</div>
                        <div v-if="index==0"
                             style="width: 50px;height: 5px;background-color:#C32727;margin: 0 auto;margin-top: 10px;border-radius: 20px"></div>
                    </div>
                </template>
            </div>

            <div class="label-list label-list-top" v-if="labelList.length==0">
                    <div style="background-color: #ffffff;width: 100%;height: 100px"></div>
            </div>


        </div>
        <div class="diy-component-edit">
            <el-form label-width='150px' @submit.native.prevent>
                <template>
                    <el-form-item label="标签列表">
                        <div v-for="(label,index) in labelList" class="edit-label-item">
                            <div class="label-edit-options">
                                <el-button @click="deleteLabel(index)"
                                           type="primary"
                                           icon="el-icon-delete"
                                           style="top: -6px;right: -31px;"></el-button>
                            </div>
                            <div flex="dir:left box:first cross:center">
                                <div>
                                    <el-input v-model="label.title" size="small"
                                              style="margin-bottom: 5px" readonly></el-input>
                                    <div>
                                        <el-input v-model="label.sub_title" readonly
                                                  size="small">

                                        </el-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <el-button size="small" @click="labelDialog.visible=true">添加标签</el-button>
                    </el-form-item>

                </template>


        </div>

        <el-dialog title="选择标签" :visible.sync="labelDialog.visible" :close-on-click-modal="false"
                   @open="loadLabelData">
            <el-table :data="labelDialog.list" v-loading="labelDialog.loading" @selection-change="catSelectionChange">
                <el-table-column label="选择" type="selection"></el-table-column>
                <el-table-column label="ID" prop="id" width="100px"></el-table-column>
                <el-table-column label="标题" prop="title"></el-table-column>
                <el-table-column label="小标题" prop="sub_title"></el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="labelDialog.pagination"
                        style="display: inline-block;"
                        background
                        @current-change="labelDialogPageChange"
                        layout="prev, pager, next"
                        :page-size.sync="labelDialog.pagination.pageSize"
                        :total="labelDialog.pagination.total_count">
                </el-pagination>
            </div>
            <div slot="footer">
                <el-button @click="labelDialog.visible = false">取 消</el-button>
                <el-button type="primary" @click="addLabel">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-label-bar', {
        template: '#diy-label-bar',
        props: {
            value: Object,
        },
        data() {
            return {
                label_index: 0,
                labelDialog: {
                    visible: false,
                    page: 1,
                    loading: null,
                    pagination: null,
                    list: null,
                    selectedList: null,
                },
                data: {
                    is_last:true,
                    label_list: [

                    ],
                }
            };
        },
        computed: {
            labelList() {
                let labelList = [];
                for (let i in this.data.label_list) {
                    labelList.push(this.data.label_list[i]);
                }
                return labelList;
            },
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
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
            loadLabelData() {
                this.labelDialog.loading = true;
                this.labelDialog.selectedList = null;
                this.$request({
                    params: {
                        r: 'mall/goods/label',
                        page: this.labelDialog.page,
                    }
                }).then(response => {
                    this.labelDialog.loading = false;
                    if (response.data.code === 0) {
                        let list = response.data.data.list;
                        this.labelDialog.list = list;
                    } else {
                    }
                }).catch(e => {
                });
            },
            labelDialogPageChange(page) {
                this.labelDialog.page = page;
                this.loadLabelData();
            }
            ,
            catSelectionChange(e) {
                this.labelDialog.selectedList = e;
            }
            ,
            addLabel() {
                this.labelDialog.visible = false;
                for (let i in this.labelDialog.selectedList) {
                    this.data.label_list.push({
                        id: this.labelDialog.selectedList[i].id,
                        title: this.labelDialog.selectedList[i].title,
                        sub_title: this.labelDialog.selectedList[i].sub_title,
                    });
                }
                this.labelDialog.selectedList = null;
            } ,
            deleteLabel(index) {
                this.data.label_list.splice(index, 1);
            },
        }
    });
</script>
<style>
    /*-----------------预览部分--------------*/
    .diy-label-bar .diy-component-preview .label-list {
        padding-top: 10px;
        display: flex;
        width: 100%;
    }

    .diy-label-bar .diy-component-preview .label-list .active {
        color: #C32727;
    }

    .diy-label-bar .diy-component-preview .label-list-top {
        background: #fff;
    }

    .diy-label-bar .diy-component-preview .label-list-top .label-item {
        height: 104px;
        padding: 0 10px;
        text-align: center;
        width: 20%;
        white-space: nowrap;
    }

    .diy-label-bar .diy-component-preview .label-list-top .label-item .label-name {
        font-size: 30px;
        font-weight: bold;
    }

    .diy-label-bar .diy-component-preview .label-list-top .label-item .label-sub-name {
        font-size: 11px;
        font-weight: 500;
    }

    .diy-label-bar .diy-component-preview .label-list-top .label-item .active {
        background: #fff;
        color: #ff4544;
    }

    .diy-label-bar .edit-label-item {
        border: 1px solid #e2e2e2;
        line-height: normal;
        padding: 5px;
        margin-bottom: 5px;
    }

    .diy-label-bar .label-edit-options {
        position: relative;
    }

    .diy-label-bar .label-edit-options {
        position: relative;
    }

    .diy-label-bar .label-edit-options .el-button {
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