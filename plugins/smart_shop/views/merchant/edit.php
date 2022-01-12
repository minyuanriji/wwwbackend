<?php
echo $this->render("com-choose-mch");
echo $this->render("com-add-smartshop");
?>
<div id="app" v-cloak v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>设置分账商户</span>
            </div>
        </div>
        <div class="form-body">

            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="200px" size="small">
                <el-form-item label="选择商户" prop="bsh_mch_id">
                    <el-card class="box-card" >
                        <div v-if="ruleForm.bsh_mch_id" style="display:flex;margin-bottom:20px;">
                            <image :src="MchSet.logo ? MchSet.logo : '/web/static/header-logo.png'" style="width:95px;height:95px;"/>
                            <div style="margin-left:20px;display:flex;flex-direction: column;justify-content: space-between">
                                <span>{{MchSet.name}}</span>
                                <span>手机：{{MchSet.mobile}}</span>
                                <span>ID：{{ruleForm.bsh_mch_id}}</span>
                            </div>
                        </div>
                        <com-choose-mch @confirm="chooseMch" style=""></com-choose-mch>
                    </el-card>
                </el-form-item>
                <el-form-item label="智慧经营门店">
                    <el-card class="box-card" >
                        <el-table :data="shopList" border >
                            <el-table-column prop="ss_store_id" label="门店ID" width="100" align="center"></el-table-column>
                            <el-table-column prop="ss_mch_id" label="商户ID" width="100" align="center"></el-table-column>
                            <el-table-column label="名称" width="200" >
                                <template slot-scope="scope">
                                    <div flex="cross:center">
                                        <com-image width="25" height="25" :src="scope.row.logo"></com-image>
                                        <div style="margin-left: 10px;width: 140px;overflow:hidden;text-overflow: ellipsis;">{{scope.row.name}}</div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="mobile" label="手机" width="150" align="center"></el-table-column>
                            <el-table-column prop="address" label="地址" width="200" ></el-table-column>
                            <el-table-column label="操作">
                                <template slot-scope="scope">
                                    <el-button @click="deleteSmartshop(scope.row)" type="text" circle size="mini">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <com-add-smartshop @confirm="chooseSmartshop" style="margin-top:20px;"></com-add-smartshop>
                    </el-card>
                </el-form-item>
                <el-form-item label="">
                    <el-button :loading="btnLoading" @click="saveIt" type="danger" size="big">确认保存</el-button>
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
                cardLoading: false,
                activeName: "first",
                ruleForm: {
                    bsh_mch_id: ''
                },
                rules: {
                    bsh_mch_id: [
                        {required: true, message: '请选择商户', trigger: 'change'},
                    ]
                },
                MchSet: {
                    name: '',
                    logo: '',
                    mobile: ''
                },
                shopList: [],
                btnLoading: false
            };
        },
        mounted: function () {
            if(getQuery("id")){
                this.getDetail()
            }
        },
        methods: {
            getDetail(){
                request({
                    params: {
                        r: 'plugin/smart_shop/mall/merchant/edit',
                        id: getQuery("id")
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        let data = e.data.data;
                        this.shopList = data.shopList;
                        this.ruleForm.bsh_mch_id = data.merchant.bsh_mch_id;
                        this.MchSet.name = data.store.name;
                        this.MchSet.logo = data.store.cover_url;
                        this.MchSet.mobile = data.mch.mobile;

                    }
                }).catch(e => {
                });
            },
            saveIt(){
                let that = this;
                this.$refs['ruleForm'].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/smart_shop/mall/merchant/edit'
                            },
                            method: 'post',
                            data: Object.assign(that.ruleForm, {shop_list:that.shopList})
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success("保存成功");
                                history.go(-1);
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error("请求失败");
                            that.btnLoading = false;
                        });
                    }
                });
            },
            deleteSmartshop(item){
                let self = this;
                self.$confirm('你确定要删除吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let i, newShopList = [];
                    for(i=0; i < self.shopList.length; i++){
                        if(self.shopList[i].ss_store_id != item.ss_store_id){
                            newShopList.push(self.shopList[i]);
                        }
                    }
                    self.shopList = newShopList;
                }).catch((e) => {

                });
            },
            chooseMch(data){
                this.ruleForm.bsh_mch_id = data.id;
                this.MchSet.name = data.store.name;
                this.MchSet.mobile = data.mobile;
                this.MchSet.logo = data.store.cover_url;
            },
            chooseSmartshop(rows){
                let i, existKeys = {};
                for(i=0; i < this.shopList.length; i++){
                    existKeys[this.shopList[i].ss_store_id] = 1;
                }
                for(i=0; i < rows.length; i++){
                    if(typeof existKeys[rows[i].store_id] != "undefined")
                        continue;
                    this.shopList.push({
                        id: 0,
                        ss_mch_id: rows[i].merchant_id,
                        ss_store_id: rows[i].store_id,
                        name: rows[i].store_name,
                        logo: rows[i].store_logo,
                        mobile: rows[i].mobile,
                        address: (rows[i].province + rows[i].city + rows[i].address)
                    });
                }
            }
        }
    });
</script>
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
    }

    .img-type .el-form-item__content {
        width: 100% !important;
    }
</style>
