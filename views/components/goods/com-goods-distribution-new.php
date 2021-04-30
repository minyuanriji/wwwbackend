<?php
echo $this->render('@app/plugins/commission/views/components/com-commission-rule-edit');
?>

<template id="com-goods-distribution-new" v-cloak>
    <div v-loading="loading" class="com-goods-distribution" v-if="goods_id!=0">
        <el-form-item label="是否开启独立分销设置" prop="is_alone">
            <el-switch @change="commissionOpen" :active-value="1" :inactive-value="0" v-model="form.is_alone">
            </el-switch>
        </el-form-item>
        <el-form-item label="" v-if="form.is_alone == 1">
            <com-commission-rule-edit @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-rule-edit>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" style="margin-top: 10px;margin-bottom:30px;" size="small" @click="saveGoodsCommission">保存分销设置</el-button>
        </el-form-item>
    </div>
</template>

<script>
    Vue.component('com-goods-distribution-new', {
        template: '#com-goods-distribution-new',
        props: {
            goods: Number,
            goods_id: String,
            goods_type: String,
        },
        data() {
            return {
                form: {
                    goods_id: 0,
                    is_alone: 0,
                    goods_type: 'MALL_GOODS'
                },
                loading: false,
                commissionType: 1,
                commissionRuleChains: []
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
            if(this.goods_id > 0){
                this.getRuleDetail();
            }
        },
        methods: {

            saveGoodsCommission(){
                var self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'plugin/commission/mall/rules/edit'
                    },
                    method: 'post',
                    data: {
                        item_type: 'goods',
                        apply_all_item: 0,
                        item_id: self.goods_id,
                        commission_type: self.commissionType,
                        commission_chains_json: JSON.stringify(self.commissionRuleChains)
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.loading = false;
                });
            },

            getRuleDetail(){
                var self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'plugin/commission/mall/rules/edit',
                        goods_id: self.goods_id,
                    },
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        var data = e.data.data;
                        if(data.rule.is_delete == 0){
                            self.form.is_alone = 1;
                        }else{
                            self.form.is_alone = 0;
                        }
                        self.commissionType       = data.rule.commission_type;
                        self.commissionRuleChains = data.chains;
                    }
                }).catch(e => {

                })
            },

            updateCommissionRule(data){
                if(data['type'] != null && typeof data.type != "undefined"){
                    this.commissionType = data.type;
                }
                if(data['chains'] != null && typeof data.chains == "object"){
                    this.commissionRuleChains = data.chains;
                }
            },

            commissionOpen(){
                if(this.goods_id == 0)
                    return;
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'plugin/commission/mall/rules/commission-goods-open',
                    },
                    method: 'post',
                    data: {
                        goods_id: self.goods_id,
                        open: self.form.is_alone > 0 ? 1 : 0
                    }
                }).then(e => {
                    self.loading = false;
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error('error');
                });
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
