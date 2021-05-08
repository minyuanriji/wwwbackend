<?php
echo $this->render('@app/plugins/commission/views/components/com-commission-rule-edit');
echo $this->render('@app/plugins/commission/views/components/com-commission-store-rule-edit');
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
        <div class="form-body">二维码收款
            <el-form label-width="180px" size="small">
                <el-form-item label="是否开启独立分佣设置" >
                    <el-switch @change="commissionOpen(1)" :active-value="1" :inactive-value="0" v-model="is_delete">
                    </el-switch>
                </el-form-item>
                <el-form-item label="" v-if="is_delete == 1">
                    <com-commission-rule-edit @update="updateCommissionRule" :ctype="commissionType" :chains="commissionRuleChains"></com-commission-rule-edit>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" style="margin-top: 10px;margin-bottom:30px;" size="small" @click="saveGoodsCommission(0)">保存分佣设置</el-button>
                </el-form-item>
            </el-form>
        </div>

        <div class="form-body">店铺推荐人
            <el-form label-width="180px" size="small">
                <el-form-item label="是否开启独立分佣设置" >
                    <el-switch @change="commissionOpen(2)" :active-value="1" :inactive-value="0" v-model="is_alone">
                    </el-switch>
                </el-form-item>
                <el-form-item label="" v-if="is_alone == 1">
                    <com-commission-store-rule-edit @update="updateCommissionRule" :ctype="commissionType" :chains="commissionStoreRule" :commiss_value = "commissonValue" @number = "newNumber"></com-commission-store-rule-edit>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" style="margin-top: 10px;margin-bottom:30px;" size="small" @click="saveGoodsCommission(1)">保存分佣设置</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        var validateItemId = (rule, value, callback) => {
            if(!this.ruleForm.apply_all_item){
                if(isNaN(value) || value <= 0){
                    callback(new Error(this.ruleForm.item_type == 'goods' ? '请选择商品' : '请选择门店'));
                }
            }else{
                return callback();
            }
        };
        return {
            is_delete:0,
            is_alone:0,
            loading: false,
            btnLoading: false,
            commissionType: 1,
            commissionRuleChains: [],
            commissionStoreRule: [],
            commissonValue: 0,
            ruleForm: {
                item_type: '',
                apply_all_item: false,
                item_id: 0
            },
            rules: {
                item_type: [
                    {message: '请选择对象类型', trigger: 'change', required: true}
                ]
            },
        };
    },
    mounted: function () {
        this.getDetail();
    },
    methods: {
        newNumber (data) {
            if (data['value'] != null && typeof data.value != "undefined"){
                this.commissonValue = data.value;
            }
        },
        saveGoodsCommission(index){
            if(!(getQuery('id') > 0))
                return;
            var self = this;
            let item_type = '';
            if (index == 1) {
                self.commissionRuleChains = [{
                    "role_type":"user",
                    "level":1,
                    "commisson_value": self.commissonValue,
                    "unique_key":"user#all"
                }]
            }
            if (index == 0) {
                item_type = 'checkout';
            } else {
                item_type = 'store';
            }
            console.log(self);
            self.loading = true;
            request({
                params: {
                    r: 'plugin/commission/mall/rules/edit'
                },
                method: 'post',
                data: {
                    item_type: item_type,
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
                        self.is_delete = 1;
                    }else{
                        self.is_delete = 0;
                    }
                    if(data.store[0].is_delete == 0){
                        self.is_alone = 1;
                    }else{
                        self.is_alone = 0;
                    }
                    self.commissionType       = data.rule.commission_type;
                    self.commissionRuleChains = data.chains;
                    self.commissionStoreRule  = data.store;
                }
            }).catch(e => {

            })
        },
        commissionOpen(index){
            console.log(index);
            let open = '';
            let item_type = '';
            if(!(getQuery('id') > 0))
                return;
            let self = this;
            self.loading = true;
            if (index == 1) {
                open = self.is_delete > 0 ? 1 : 0;
                item_type = 'checkout';
            } else {
                open = self.is_alone > 0 ? 1 : 0;
                item_type = 'store';
            }

            request({
                params: {
                    r: 'plugin/commission/mall/rules/commission-store-open',
                },
                method: 'post',
                data: {
                    store_id: getQuery('id'),
                    open: open,
                    item_type: item_type
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
