<?php
echo $this->render('@app/plugins/commission/views/components/com-commission-rule-edit');
?>
<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>
<div id="app" v-cloak>
    <el-card id="com-goods-distribution" class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/mch/mall/distribution/list'})">商户列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>设置分佣信息</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form label-width="180px" size="small">
                <el-form-item label="是否开启独立分佣设置" >
                    <el-switch @change="commissionOpen" :active-value="1" :inactive-value="0" v-model="is_alone">
                    </el-switch>
                </el-form-item>
                <el-form-item label="" v-if="is_alone == 1">
                    <com-commission-rule-edit @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-rule-edit>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" style="margin-top: 10px;margin-bottom:30px;" size="small" @click="saveGoodsCommission">保存分佣设置</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            is_alone:0,
            loading: false,
            btnLoading: false,
            commissionType: 1,
            commissionRuleChains: []
        };
    },
    mounted: function () {
        this.getDetail();
    },
    methods: {
        saveGoodsCommission(){
            if(!(getQuery('id') > 0))
                return;
            var self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/commission/mall/rules/edit'
                },
                method: 'post',
                data: {
                    item_type: 'checkout',
                    apply_all_item: 0,
                    item_id: getQuery('id'),
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
        getDetail(){
            var self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/commission/mall/rules/edit',
                    store_id: getQuery('id'),
                },
            }).then(e => {
                self.loading = false;
                if (e.data.code == 0) {
                    var data = e.data.data;
                    if(data.rule.is_delete == 0){
                        self.is_alone = 1;
                    }else{
                        self.is_alone = 0;
                    }
                    self.commissionType       = data.rule.commission_type;
                    self.commissionRuleChains = data.chains;
                }
            }).catch(e => {

            })
        },
        commissionOpen(){
            if(!(getQuery('id') > 0))
                return;
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/commission/mall/rules/commission-store-open',
                },
                method: 'post',
                data: {
                    store_id: getQuery('id'),
                    open: self.is_alone > 0 ? 1 : 0
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
        },
        updateCommissionRule(data){
            if(data['type'] != null && typeof data.type != "undefined"){
                this.commissionType = data.type;
            }
            if(data['chains'] != null && typeof data.chains == "object"){
                this.commissionRuleChains = data.chains;
            }
        },
    }
});
</script>
