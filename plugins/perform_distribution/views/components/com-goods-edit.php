<?php
Yii::$app->loadComponentView('goods/com-select-goods');
?>

<template id="com-goods-edit">
    <el-dialog :visible.sync="dialogVisible" width="50%" :title="cTitle" :close-on-click-modal="false">
        <div>
            <el-form @submit.native.prevent label-width="150px">
                <el-card shadow="always">
                    <div style="display: flex;align-items: center">
                        <com-image :src="data.goods.cover_pic"></com-image>
                        <div style="display: flex;flex-direction: column;margin-left:20px;justify-content: space-between">
                            <span>(ID:{{data.goods_id}}) {{data.goods.name}}</span>
                            <span>利润：{{data.goods.profit_price}}</span>
                            <span>
                                价格：<b style="color: darkred">{{data.goods.price}}</b>
                                <a style="margin-left:5px;" :href="'?r=mall/goods/edit&id=' + data.goods_id" target="_blank">详情</a>
                            </span>
                        </div>
                    </div>
                </el-card>

                <el-tabs type="card" style="margin-top:20px;" v-loading="cardLoading">
                    <el-tab-pane label="奖励规则">
                        <div style="display:flex;align-items: center">
                            <el-input min="0" v-model="awardValue" type="number" placeholder="请输入"  style="width:350px;">
                                <el-select v-model="ruleForm.award_type" slot="prepend" placeholder="奖励类型" style="width:100px;">
                                    <el-option label="百分比" value="0"></el-option>
                                    <el-option label="固定值" value="1"></el-option>
                                </el-select>
                                <template slot="append">{{ruleForm.award_type == 0 ? '%' : '元'}}</template>
                            </el-input>
                            <el-button @click="batchEdit" type="primary" style="margin-left:10px;">批量操作</el-button>
                        </div>
                        <el-table ref="multipleTable" @selection-change="handleSelectionChange" :data="awardRules" border style="margin-top:10px;width: 100%">
                            <el-table-column type="selection" width="55"></el-table-column>
                            <el-table-column prop="level" label="等级" width="100" align="center"></el-table-column>
                            <el-table-column prop="name" label="名称" width="180"></el-table-column>
                            <el-table-column :label="ruleForm.award_type == 0 ? '百分比（%）' : '固定值（元）'"  width="180">
                                <template slot-scope="scope">
                                    <el-input type="number" min="0" v-model="scope.row.value" placeholder="请输入" size="small"></el-input>
                                </template>
                            </el-table-column>
                            <el-table-column label="备注说明" >
                                <template slot-scope="scope">
                                    <span style="color:gray;">{{cAwardTip(scope.row)}}</span>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-tab-pane>
                </el-tabs>

            </el-form>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="editCancel" type="default">取消</el-button>
            <el-button type="primary" :loading="btnLoading" style="margin-bottom: 10px;"@click="editSave">保存</el-button>
        </span>
    </el-dialog>
</template>
<script>
    Vue.component('com-goods-edit', {
        template: '#com-goods-edit',
        props: {
            value: {
                type: Boolean,
                default: false
            },
            data: Object
        },
        data() {
            return {
                btnLoading: false,
                dialogVisible: false,
                cardLoading: false,
                ruleForm: {},
                awardValue: '',
                awardRules: [],
                multipleSelection: []
            }
        },
        computed: {
            cTitle (){
                return this.data.id != 0 ? '编辑商品' : '添加商品';
            },
            cAwardTip(item){
                return function(item){
                    let tip = '';
                    if(this.ruleForm.award_type == 1){ //按固定值
                        tip = '成交一单，预计可获得' + item.value + '元奖励';
                    }else{ //百分比
                        let money = (parseFloat(item.value)/100) * parseFloat(this.ruleForm.goods.profit_price);
                        tip = '成交一单，预计可获得' + money + '元奖励';
                    }
                    return tip;
                }
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.dialogVisible = true;
                } else {
                    this.dialogVisible = false;
                }
            },
            dialogVisible() {
                if (!this.dialogVisible) {
                    this.editCancel();
                }
            },
            data:{
                handler(newVal){
                    this.ruleForm = newVal;
                    this.getLevel();
                },
                deep: true
            }
        },
        mounted() {
            this.ruleForm = this.data;
            this.getLevel();
        },
        methods: {
            batchEdit(){
                if(!this.multipleSelection || this.multipleSelection.length <= 0){
                    this.$message.error("请选择要操作的记录");
                    return;
                }
                let i;
                for(i=0; i < this.multipleSelection.length; i++){
                    this.multipleSelection[i].value = this.awardValue ? this.awardValue : 0;
                }
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            setAwardRules(levels){
                let i, j, awardRules = [], rule;
                for(i=0; i < levels.length; i++){
                    rule = {
                        id: levels[i].id,
                        level: levels[i].level,
                        name: levels[i].name,
                        value: 0
                    };
                    if(this.ruleForm.award_rules){
                        let awardRules = JSON.parse(this.ruleForm.award_rules);
                        for(j=0; j < awardRules.length; j++){
                            if(awardRules[j].id == rule.id){
                                rule.value = awardRules[j].value;
                                break;
                            }
                        }
                    }
                    awardRules.push(rule);
                }
                this.awardRules = awardRules;
            },
            getLevel(){
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/level/index',
                        page: 1
                    },
                    method: 'get',
                }).then(e => {
                    if(e.data.code == 0){
                        this.cardLoading = false;
                        this.setAwardRules(e.data.data.list);
                    }else{
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            editSave(){
                this.btnLoading = true;
                this.ruleForm.award_rules = JSON.stringify(this.awardRules);
                request({
                    params: {
                        r: 'plugin/perform_distribution/mall/goods/edit',
                    },
                    method: 'post',
                    data: this.ruleForm
                }).then(res => {
                    this.btnLoading = false;
                    if (res.data.code == 0) {
                        this.$message.success('保存成功');
                        this.editCancel();
                        this.$emit('on-save');
                    } else {
                        this.$message.error(res.data.msg);
                    }
                }).catch(res => {
                    this.btnLoading = false;
                });
            },
            editCancel() {
                this.$emit('input', false);
            }
        }
    });
</script>
