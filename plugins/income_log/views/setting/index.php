<?php
Yii::$app->loadComponentView('goods/com-dialog-select');
?>

<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="padding: 0 0;">
        <el-form :rules="rules" ref="ruleForm" :model="ruleForm" label-width="150px">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基本设置" name="first">
                    <div style="padding-top: 20px">
                        <el-form-item label="显示商品">
                            <el-card class="box-card">

                                <com-dialog-select :multiple="false" @selected="goodsSelect" title="商品选择">
                                    <el-button type="primary" >指定商品</el-button>
                                </com-dialog-select>

                                <el-table :data="incomeShowGoodsArr" border style="margin-top:10px;width: 100%">
                                    <el-table-column prop="id" label="商品ID" width="180" align="center"> </el-table-column>
                                    <el-table-column label="商品名称" width="350">
                                        <template slot-scope="scope">
                                            <div style="display: flex;align-items: center">
                                                <com-image :src="scope.row.cover_pic"></com-image>
                                                <com-ellipsis :line="1">{{scope.row.name}}</com-ellipsis>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="操作" >
                                        <template slot-scope="scope">
                                            <el-button @click="deleteShowGoods(scope.$index)" circle size="mini" type="text" >
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-card>

                        </el-form-item>

                        <el-form-item>
                            <el-button :loading="submitLoading" type="primary" @click="submitForm('ruleForm')">保存</el-button>
                        </el-form-item>
                    </div>
                </el-tab-pane>
            </el-tabs>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: "first",
                loading: false,
                submitLoading: false,
                incomeShowGoodsArr: [],
                ruleForm: {},
                rules: {}
            };
        },
        created() {
            this.getSetting()
        },
        methods: {
            deleteShowGoods(index){
                this.incomeShowGoodsArr.splice(index, 1);
            },
            goodsSelect(e){
                this.incomeShowGoodsArr.push({
                    id: e.id,
                    name: e.goodsWarehouse.name,
                    cover_pic: e.goodsWarehouse.cover_pic
                });
            },
            getSetting(){
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/income_log/mall/setting/index'
                    },
                }).then(e => {
                    this.loading = false;
                    if (e.data.code === 0) {
                        let data = e.data.data;
                        if(data.income_show_goods){
                            this.incomeShowGoodsArr = JSON.parse(data.income_show_goods);
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                })
            },
            submitForm(formName){
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.submitLoading = true;
                        let ruleForm = JSON.parse(JSON.stringify(this.ruleForm));
                        ruleForm['income_show_goods'] = JSON.stringify(this.incomeShowGoodsArr);
                        request({
                            params: {
                                r: 'plugin/income_log/mall/setting/index',
                            },
                            method: 'post',
                            data: {form:ruleForm}
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                        });
                    } else {
                        this.$message.error('部分参数验证不通过');
                    }
                });
            }
        },
        computed: {}
    });
</script>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .title {
        margin-top: 10px;
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .form-body .item {
        width: 300px;
        margin-bottom: 50px;
        margin-right: 25px;
    }

    .item-img {
        height: 550px;
        padding: 25px 10px;
        border-radius: 30px;
        border: 1px solid #CCCCCC;
        background-color: #fff;
    }

    .item .el-form-item {
        width: 300px;
        margin: 20px auto;
    }

    .left-setting-menu {
        width: 260px;
    }

    .left-setting-menu .el-form-item {
        height: 60px;
        padding-left: 20px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
        cursor: pointer;
    }

    .left-setting-menu .el-form-item .el-form-item__label {
        cursor: pointer;
    }

    .left-setting-menu .el-form-item.active {
        background-color: #F3F5F6;
        border-top-left-radius: 10px;
        width: 105%;
        border-bottom-left-radius: 10px;
    }

    .left-setting-menu .el-form-item .el-form-item__content {
        margin-left: 0 !important
    }

    .no-radius {
        border-top-left-radius: 0 !important;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .reset {
        position: absolute;
        top: 3px;
        left: 90px;
    }

    .app-tip {
        position: absolute;
        right: 24px;
        top: 16px;
        height: 72px;
        line-height: 72px;
        max-width: calc(100% - 78px);
    }

    .app-tip:before {
        content: ' ';
        width: 0;
        height: 0;
        border-color: inherit;
        position: absolute;
        top: -32px;
        right: 100px;
        border-width: 16px;
        border-style: solid;
    }

    .tip-content {
        display: block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 28px;
        font-size: 28px;
    }
</style>