<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-19
 * Time: 14:19
 */
?>
<template id="com-goods-distribution" v-cloak>
    <div v-loading="loading" class="com-goods-distribution" v-if="goods_id!=0">
        <el-form-item label="是否开启独立分销设置" prop="is_alone">
            <el-switch :active-value="1" :inactive-value="0" v-model="form.is_alone">
            </el-switch>
        </el-form-item>

        <template v-if="form.is_alone==1">
            <el-form-item label="分销佣金设置" v-if="use_attr == 1">
                <el-radio v-model="form.attr_setting_type" :label="0">按商品设置</el-radio>
                <el-radio v-model="form.attr_setting_type" :label="1">按规格设置</el-radio>
            </el-form-item>
            <el-form-item label="分销佣金类型" prop="share_type">
                <el-radio v-model="form.share_type" :label="0">固定金额</el-radio>
                <el-radio v-model="form.share_type" :label="1">百分比</el-radio>
            </el-form-item>
            <el-form-item label="分销佣金">
                <template v-if="distributionLevel.length == 0">
                    <el-button type="danger" @click="$navigate({r: 'plugin/distribution/mall/setting/index'})">
                        请先在分销应用的中开启功能
                    </el-button>
                </template>
                <template v-else>
                    <div class="box">
                        <label style="margin-bottom:0;padding:18px 10px;">批量设置</label>
                        <el-select v-model="selectLevel" slot="prepend" placeholder="请选择等级"
                                   v-if="form.attr_setting_type == 1">
                            <el-option v-for="(item, index) in form.distribution_level_list"
                                       :value="index"
                                       :key="item.id"
                                       :label="item.name">{{item.name}}
                            </el-option>
                        </el-select>
                        <el-select v-model="selectData" slot="prepend" placeholder="请选择层级">
                            <el-option v-for="(item, index) in distributionLevel" :value="item.value"
                                       :key="item.id"
                                       :label="item.label">{{item.label}}
                            </el-option>
                        </el-select>
                        <el-input @keyup.enter.native="enter" type="number" style="width: 150px;"
                                  v-model="batchShareLevel">
                            <span slot="append">{{form.share_type == 1 ? '%' : '元'}}</span>
                        </el-input>
                        <el-button type="primary" size="small" @click="batchAttr">设置</el-button>
                    </div>
                    <!--普通分销佣金设置 -->
                    <template v-if="form.attr_setting_type == 0 || use_attr == 0">
                        <el-table ref="normal" :data="form.distribution_level_list" border stripe style="width: 100%;"
                                  @selection-change="handleSelectionChange">
                            <el-table-column type="selection" width="55"></el-table-column>
                            <el-table-column width="100" label="等级名称" prop="name"></el-table-column>
                            <el-table-column :label="item.label" :prop="item.value" :property="item.value"
                                             v-for="(item, index) in distributionLevel" :key="index" width="300">
                                <template slot-scope="scope">
                                    <el-input type="number" v-model="scope.row[scope.column.property]">
                                        <span slot="append">{{form.share_type == 1 ? '%' : '元'}}</span>
                                    </el-input>
                                </template>
                            </el-table-column>
                        </el-table>
                    </template>
                    <template v-else>
                        <!-- 详细分销佣金设置 -->
                        <template v-if="form.attr.length > 0">
                            <el-table ref="detail" :data="form.attr" border class="detail"
                                      @selection-change="handleSelectionChange">
                                <el-table-column type="selection" width="55"></el-table-column>
                                <el-table-column width="100" v-for="(item, index) in attrGroups" :key="item.id"
                                                 :label="item.attr_group_name"
                                                 :prop="'attr_list['+index+'].attr_name'">
                                </el-table-column>
                                <el-table-column v-for="(item, index) in form.distribution_level_list" :key="item.id"
                                                 :label="item.name">
                                    <el-table-column :label="value.label" type="index" :index="index"
                                                     :prop="value.value"
                                                     v-for="(value, key) in distributionLevel" :key="key" width="150">
                                        <template slot-scope="scope">
                                            <el-input type="number"
                                                      v-model="scope.row.distribution_level_list[scope.column.index][scope.column.property]" @input="onInput()">
                                                <span slot="append">{{form.share_type == 1 ? '%' : '元'}}</span>
                                            </el-input>
                                        </template>
                                    </el-table-column>
                                </el-table-column>
                            </el-table>
                        </template>
                        <!-- 默认规格 分销佣金-->
                        <template v-else>
                            <el-tag style="margin-top: 10px;" type="danger">如需设置多规格分销价, 请先添加商品规格</el-tag>
                        </template>
                    </template>
                </template>


            </el-form-item>


        </template>

        <el-form-item>
            <el-button type="primary" style="margin-top: 10px" size="small" @click="saveGoodsDistribution">保存分销设置
            </el-button>
        </el-form-item>

    </div>
</template>
<script>
    Vue.component('com-goods-distribution', {
        template: '#com-goods-distribution',
        props: {
            goods: Number,
            goods_id: String,
            goods_type: String,
        },
        data() {
            return {
                form: {
                    goods_id: 0,
                    attr_setting_type: 0,
                    share_type: 0,
                    distribution_level_list: [],
                    attr: [],
                    is_alone: 0,
                    goods_type: 'MALL_GOODS'
                },
                attrGroups: [],
                distributionLevel: [],
                loading: false,
                selectList: [],
                batchShareLevel: 0,
                selectData: '',
                selectLevel: '',
                use_attr: 0,
            }
        },

        mounted() {
            if (this.goods_id == 0) {
                this.$alert('请先保存商品后重试！', '分销设置提示', {
                    confirmButtonText: '确定',

                });
                return;
            }
            this.form.goods_id = this.goods_id;
            this.form.goods_type = this.goods_type;
            this.loadData();
        },
        methods: {
            onInput(){
                this.$forceUpdate();
            },
            saveGoodsDistribution() {
                let self = this;
                request({
                    params: {
                        r: 'plugin/distribution/mall/distribution/goods-distribution-setting',
                    },
                    method: 'post',
                    data: {
                        form: this.form
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            // 获取分销设置
            loadData() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'plugin/distribution/mall/distribution/distribution-config',
                        goods_id: self.goods_id,
                        goods_type: self.goods_type
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        let detail = e.data.data.detail;
                        if (detail.distribution_goods) {
                            self.form.share_type = detail.distribution_goods.share_type;
                            self.form.attr_setting_type = detail.distribution_goods.attr_setting_type;
                            self.form.is_alone = detail.distribution_goods.is_alone;
                        }
                        if (detail.goods) {
                            self.use_attr = detail.goods.use_attr;
                            self.attrGroups = detail.goods.attr_groups;
                        }
                        self.distributionLevel = e.data.data.distributionLevelArray; //分销层级
                        self.form.distribution_level_list = e.data.data.distributionLevelList; //分销商等级
                        if (e.data.data.detail) {
                            let attr_level_setting_list = e.data.data.detail.attr_distribution_level_setting_list;
                            self.form.attr = e.data.data.detail.attr;

                            if (self.use_attr == 1) {
                                self.form.attr.forEach((attr, index) => {
                                    attr.distribution_level_list = this.setDistriburtionLevel(self.form.distribution_level_list, attr_level_setting_list[index].distribution_level_list);
                                });
                            }
                            // 未使用规格的情况
                            let distribution_level_setting_list = e.data.data.detail.distribution_level_setting_list;
                            self.form.distribution_level_list = this.setDistriburtionLevel(self.form.distribution_level_list, distribution_level_setting_list);
                        }
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            setDistriburtionLevel(distributionLevelList, list) {
                let newDistributionLevelList = [];
                distributionLevelList.forEach((item) => {
                    let newItem = {
                        level: item.level,
                        name: item.name,
                        commission_first: 0,
                        commission_second: 0,
                        commission_third: 0,
                    };
                    for (let i in list) {
                        if (list[i].level == item.level) {
                            newItem = Object.assign(newItem, list[i]);
                        }
                    }
                    newDistributionLevelList.push(newItem);
                });

                return JSON.parse(JSON.stringify(newDistributionLevelList));
            },
            handleSelectionChange(data) {
                this.selectList = data;
            },
            batchAttr() {
                let form = JSON.parse(JSON.stringify(this.form));
                if (form.attr_setting_type == 0) {
                    if (!this.selectList || this.selectList.length === 0) {
                        this.$message.warning('请勾选分销商等级');
                        return;
                    }
                    if (this.selectData === '') {
                        this.$message.warning('请选择分销层级');
                        return;
                    }
                    form.distribution_level_list.forEach((item, index) => {
                        let flag = false;
                        this.selectList.map((item1) => {
                            if (JSON.stringify(item1) === JSON.stringify(item)) {
                                flag = true;
                            }
                        });
                        if (flag) {
                            item[this.selectData] = this.batchShareLevel
                        }
                    })
                } else {
                    if (!this.selectList || this.selectList.length === 0) {
                        this.$message.warning('请勾选商品规格');
                        return;
                    }
                    if (this.selectLevel === '') {
                        this.$message.warning('请选择分销商等级');
                        return;
                    }
                    if (this.selectData === '') {
                        this.$message.warning('请选择分销层级');
                        return;
                    }
                    form.attr.forEach((item, index) => {
                        let flag = false;
                        this.selectList.map((item1) => {
                            if (JSON.stringify(item1.attr_list) === JSON.stringify(item.attr_list)) {
                                flag = true;
                            }
                        });
                        if (flag) {
                            item.distribution_level_list[this.selectLevel][this.selectData] = this.batchShareLevel
                        }
                    })
                }
                this.$set(this, 'form', form);
            }
        }
    });
</script>
<style>
    .com-goods-distribution .box {
        border-top: 1px solid #E8EAEE;
        border-left: 1px solid #E8EAEE;
        border-right: 1px solid #E8EAEE;
        padding: 16px;
    }

    .com-goods-distribution .box .batch {
        margin-left: -10px;
        margin-right: 20px;
    }

    .com-goods-distribution .el-select .el-input {
        width: 130px;
    }

    .com-goods-distribution .detail {
        width: 100%;
    }

    .com-goods-distribution .detail .el-input-group__append {
        padding: 0 10px;
    }

    .com-goods-distribution input::-webkit-outer-spin-button,
    .com-goods-distribution input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    .com-goods-distribution input[type="number"] {
        -moz-appearance: textfield;
    }

    .com-goods-distribution .el-table .cell {
        text-align: center;
    }

    .com-goods-distribution .el-table thead.is-group th {
        background: #ffffff;
    }
</style>