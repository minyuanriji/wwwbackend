<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:11
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
Yii::$app->loadComponentView('wechat/com-tags-edit');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>标签设置</span>
            </div>
        </div>
        <div class="form_box">

            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px" @submit.native.prevent>
                <el-card style="margin-top: 10px" shadow="never">
                    <el-col :span="14">
                        <el-form-item label="标签名称" style='display: flex;'>
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="标签类型" style='display: flex;'>
                            <el-radio v-model="ruleForm.type" label="1">价值分层</el-radio>
                            <el-radio v-model="ruleForm.type" label="2">生命周期</el-radio>
                            <el-radio v-model="ruleForm.type" label="3">营销偏好</el-radio>
                            <el-radio v-model="ruleForm.type" label="4">行为偏好</el-radio>
                        </el-form-item>
                    </el-col>
					
                    <el-col :span="14" v-if='ruleForm.type == 1'>
                       <div class='title'>
                            <div class="title-one">条件设置</div>
                            <div>选择多个条件时，需全部满足</div>
                       </div>
                        <el-form-item label="交易条件">
                           <el-checkbox-group v-model="ruleForm.basicList1">
                               <el-col :span="10">
                                   <div class="margin-button-20px flex">
                                       <el-checkbox @change='change($event,"ruleForm.basicList1Data.pay_count")' :checked="ruleForm.basicList1Data.pay_count.use" label="pay_count" style="margin-right: 20px;">历史付费次数</el-checkbox>
                                       <div class="enter flex">
                                           <div class="flex flex-y-center">
                                               <el-input :disabled='!ruleForm.basicList1Data.pay_count.use' v-model="ruleForm.basicList1Data.pay_count.min" class='inp'></el-input>
                                               <div class="inp-text">次</div>
                                           </div>
                                           <div class="segmentation">-</div>
                                           <div class="flex flex-y-center">
                                               <el-input :disabled='!ruleForm.basicList1Data.pay_count.use' v-model="ruleForm.basicList1Data.pay_count.max" class='inp'></el-input>
                                               <div class="inp-text">次</div>
                                           </div>
                                       </div>
                                   </div>
                                   <div class="margin-button-20px flex">
                                       <el-checkbox @change='change($event,"ruleForm.basicList1Data.pay_money")' :checked="ruleForm.basicList1Data.pay_money.use" label="pay_money" style="margin-right: 20px;">历史付费金额</el-checkbox>
                                       <div class="enter flex">
                                           <div class="flex flex-y-center">
                                               <el-input :disabled='!ruleForm.basicList1Data.pay_money.use' v-model="ruleForm.basicList1Data.pay_money.min" class='inp'></el-input>
                                               <div class="inp-text">RMB</div>
                                           </div>
                                           <div class="segmentation">-</div>
                                           <div class="flex flex-y-center">
                                               <el-input :disabled='!ruleForm.basicList1Data.pay_money.use' v-model="ruleForm.basicList1Data.pay_money.max" class='inp'></el-input>
                                               <div class="inp-text">RMB</div>
                                           </div>
                                       </div>
                                   </div>
                               </el-col>
                           </el-checkbox-group>
                        </el-form-item>
                    </el-col>

                    <el-col :span="14" v-if='ruleForm.type == 2'>
                        <div class='title'>
                            <div class="title-one">条件</div>
                            <div>选择多个条件时，需全部满足</div>
                        </div>
                        <el-form-item label="满足条件">
                            <el-checkbox-group v-model="ruleForm.basicList2">
                                <el-col :span="10">

                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList2Data.new_user")' label="ruleForm.basicList2Data.new_user" style="margin-right: 20px;">新用户(新注册且没买过商品)</el-checkbox>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList2Data.active_user")' :checked="ruleForm.basicList2Data.active_user.use" label="active_user" style="margin-right: 20px;">活跃用户(连续N天登录并浏览商城)</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList2Data.active_user.use' v-model="ruleForm.basicList2Data.active_user.num" class='inp'></el-input>
                                                <div class="inp-text">天</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList2Data.silence_user")' :checked="ruleForm.basicList2Data.silence_user.use" label="silence_user" style="margin-right: 20px;">沉默用户(连续N天没有登录商城浏览或下单)</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList2Data.silence_user.use' v-model="ruleForm.basicList2Data.silence_user.num" class='inp'></el-input>
                                                <div class="inp-text">天</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList2Data.lose_user")' :checked="ruleForm.basicList2Data.lose_user.use" label="lose_user" style="margin-right: 20px;">失去用户(连续N天没有登录商城浏览和下单)</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList2Data.lose_user.use' v-model="ruleForm.basicList2Data.lose_user.num" class='inp'></el-input>
                                                <div class="inp-text">天</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList2Data.after_purchase_user")' :checked="ruleForm.basicList2Data.after_purchase_user.use" label="after_purchase_user" style="margin-right: 20px;">复购用户(购买了相同的产品N件以上)</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList2Data.after_purchase_user.use' v-model="ruleForm.basicList2Data.after_purchase_user.num" class='inp'></el-input>
                                                <div class="inp-text">件</div>
                                            </div>
                                        </div>
                                    </div>
                                </el-col>
                            </el-checkbox-group>
                        </el-form-item>
                    </el-col>

                    <el-col :span="24" v-if='ruleForm.type == 3'>
                        <div class='title'>
                            <div class="title-one">条件</div>
                            <div>选择多个条件时，需全部满足</div>
                        </div>
                        <el-form-item label="满足条件">
                            <el-checkbox-group v-model="ruleForm.basicList3">
                                <el-col :span="16">

                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList3Data.direct_drive_num")' :checked="ruleForm.basicList3Data.direct_drive_num.use" label="direct_drive_num" style="margin-right: 20px;">直推人数</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">直推人数达到</div>
                                                <el-input :disabled='!ruleForm.basicList3Data.direct_drive_num.use' v-model="ruleForm.basicList3Data.direct_drive_num.num" class='inp'></el-input>
                                                <div class="inp-text">人</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList3Data.card_praise_num")' :checked="ruleForm.basicList3Data.card_praise_num.use" label="card_praise_num" style="margin-right: 20px;">名片点赞数</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">点赞数达到</div>
                                                <el-input :disabled='!ruleForm.basicList3Data.card_praise_num.use' v-model="ruleForm.basicList3Data.card_praise_num.num" class='inp'></el-input>
                                            </div>
                                        </div>
                                    </div>

                                </el-col>

                            </el-checkbox-group>
                        </el-form-item>
                    </el-col>

                    <el-col :span="24" v-if='ruleForm.type == 4'>
                        <div class='title'>
                            <div class="title-one">条件</div>
                            <div>选择多个条件时，需全部满足</div>
                        </div>
                        <el-form-item label="需求条件">
                            <el-checkbox-group v-model="ruleForm.basicList4">
                                <el-col :span="16">

                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.buy_kind_goods")' :checked="ruleForm.basicList4Data.buy_kind_goods.use" label="buy_kind_goods" style="margin-right: 20px;">购买同类型商品次数统达到N次</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList4Data.buy_kind_goods.use' v-model="ruleForm.basicList4Data.buy_kind_goods.num" class='inp'></el-input>
                                                <div class="inp-text">次</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.collect_kind_goods")' :checked="ruleForm.basicList4Data.collect_kind_goods.use" label="collect_kind_goods" style="margin-right: 20px;">收藏同类型商品超过N件以上</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <el-input :disabled='!ruleForm.basicList4Data.collect_kind_goods.use' v-model="ruleForm.basicList4Data.collect_kind_goods.num" class='inp'></el-input>
                                                <div class="inp-text">件</div>
                                            </div>
                                        </div>
                                    </div>
<!--                                    <div class="margin-button-20px flex">-->
<!--                                        <el-checkbox @change='change($event,"search_kind_goods")' :checked="search_kind_goods.use" label="search_kind_goods" style="margin-right: 20px;">搜索同类型商品次数达到N次</el-checkbox>-->
<!--                                        <div class="enter flex">-->
<!--                                            <div class="flex flex-y-center">-->
<!--                                                <el-input :disabled='!search_kind_goods.use' v-model="search_kind_goods.num" class='inp'></el-input>-->
<!--                                                <div class="inp-text">次</div>-->
<!--                                            </div>-->
<!--                                        </div>-->
<!--                                    </div>-->
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.visit_kind_page")' :checked="ruleForm.basicList4Data.visit_kind_page.use" label="visit_kind_page" style="margin-right: 20px;">访问深度</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">访问同页面次数达到</div>
                                                <el-input :disabled='!ruleForm.basicList4Data.visit_kind_page.use' v-model="ruleForm.basicList4Data.visit_kind_page.num" class='inp'></el-input>
                                                <div class="inp-text">次</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.collect_goods")' :checked="ruleForm.basicList4Data.collect_goods.use" label="collect_goods" style="margin-right: 20px;">收藏商品</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">收藏商品达到</div>
                                                <el-input :disabled='!ruleForm.basicList4Data.collect_goods.use' v-model="ruleForm.basicList4Data.collect_goods.num" class='inp'></el-input>
                                                <div class="inp-text">件</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.like_num")' :checked="ruleForm.basicList4Data.like_num.use" label="like_num" style="margin-right: 20px;">点赞</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">点赞次数达到</div>
                                                <el-input :disabled='!ruleForm.basicList4Data.like_num.use' v-model="ruleForm.basicList4Data.like_num.num" class='inp'></el-input>
                                                <div class="inp-text">次</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="margin-button-20px flex">
                                        <el-checkbox @change='change($event,"ruleForm.basicList4Data.share_num")' :checked="ruleForm.basicList4Data.share_num.use" label="share_num" style="margin-right: 20px;">分享</el-checkbox>
                                        <div class="enter flex">
                                            <div class="flex flex-y-center">
                                                <div class="inp-text">分享次数达到</div>
                                                <el-input :disabled='!ruleForm.basicList4Data.share_num.use' v-model="ruleForm.basicList4Data.share_num.num" class='inp'></el-input>
                                                <div class="inp-text">次</div>
                                            </div>
                                        </div>
                                    </div>

                                </el-col>

                            </el-checkbox-group>
                        </el-form-item>
                    </el-col>

                </el-card>
            </el-form>

            <el-button :loading="btnLoading" class="button-item" type="primary" style="margin-top: 24px;"
                       @click="store('ruleForm')" size="small">保存
            </el-button>
        </div>

    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                cat_show: false,
                show_share_level: false,
				textss:'成为客户时间',
				dataForm: {},
                ruleForm: {
                    type: '1',
                    name:"",
                    dataForm:[],
                    basicList1: [],
                    basicList1Data: {
                        pay_count: {
                            use: false,
                            min: '',
                            max: ''
                        },
                        // 历史付费金额
                        pay_money: {
                            use: false,
                            min: '',
                            max: ''
                        },
                    },
                    basicList2: [],
                    basicList2Data: {
                        // 新用户
                        new_user: {
                            use: false
                        },
                        // 活跃用户
                        active_user: {
                            use: false,
                            num: '',
                        },
                        // 沉默用户
                        silence_user: {
                            use: false,
                            num: '',
                        },
                        // 失去用户
                        lose_user: {
                            use: false,
                            num: '',
                        },
                        // 复购用户
                        after_purchase_user: {
                            use: false,
                            num: '',
                        },
                    },
                    basicList3: [],
                    basicList3Data: {
                        // 直推人数
                        direct_drive_num: {
                            use: false,
                            num: '',
                        },
                        // 名片点赞数
                        card_praise_num: {
                            use: false,
                            num: '',
                        },
                    },
                    basicList4: [],
                    basicList4Data: {
                        // 购买同类型商品
                        buy_kind_goods: {
                            use: false,
                            num: '',
                        },
                        // 收藏同类型商品
                        collect_kind_goods: {
                            use: false,
                            num: '',
                        },
                        // 搜索同类型商品
                        // search_kind_goods: {
                        //     use: false,
                        //     num: '',
                        // },
                        // 访问同页面
                        visit_kind_page: {
                            use: false,
                            num: '',
                        },
                        // 收藏商品
                        collect_goods: {
                            use: false,
                            num: '',
                        },
                        // 点赞次数
                        like_num: {
                            use: false,
                            num: '',
                        },
                        // 分享次数
                        share_num: {
                            use: false,
                            num: '',
                        },
                    },
                },
                rules: {

                },
                radio: 1,
				basicList1:[],
				basicList2:[],
				basicList3:[],
				basicList4:[],
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
			change(e,key){
                let temp = key.split(".");
                this[temp[0]][temp[1]][temp[2]].use = e;
                if (!e){
                    return ;
                }
                this.dataForm[temp[2]] = this[temp[0]][temp[1]][temp[2]];
                // this.dataForm[key] = this[temp[0]];
			},
            loadData() {
                this.loading = true;
                let id = getQuery('id');
                if(!id){
                    this.loading = false;
                    return;
                }
                request({
                    params: {
                        r: 'mall/setting/tag',
                        id: getQuery('id')
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data.detail;
                        var type = this.ruleForm.type;
                        this.ruleForm.type = this.ruleForm.type+"";
                        var obj = JSON.parse(this.ruleForm.condition);
                        console.log(obj,'obj');
                        // var basicList = `this.ruleForm.basicList${this.ruleForm.type}`;
                        // basicList  = obj[this.ruleForm.type];
                        if(type == 1){
                            this.ruleForm.basicList1 = [];
                            for (let item in obj[1]){
                                this.ruleForm.basicList1.push(item);
                            }
                            this.ruleForm.basicList1Data = obj[1];
                        }else if(type == 2){
                            this.ruleForm.basicList2 = [];
                            for (let item in obj[2]){
                                this.ruleForm.basicList2.push(item);
                            }
                            this.ruleForm.basicList2Data = obj[2];
                        }else if(type == 3){
                            this.ruleForm.basicList3 = [];
                            for (let item in obj[3]){
                                this.ruleForm.basicList3.push(item);
                            }
                            this.ruleForm.basicList3Data = obj[3];
                        }else if(type == 4){
                            this.ruleForm.basicList4 = [];
                            for (let item in obj[4]){
                                this.ruleForm.basicList4.push(item);
                            }
                            this.ruleForm.basicList4Data = obj[4];
                        }
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            store(formName) {
                let formData =  JSON.stringify(this.dataForm);
                let para = Object.assign({}, this.dataForm);

                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/setting/tag',
                            },
                            method: 'post',
                            data: {
                                name: this.ruleForm.name,
                                type: this.ruleForm.type,
                                id: getQuery('id'),
                                data: formData,
                            }
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/setting/tag-list'
                                })
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        })
                    } else {
                        this.btnLoading = false;
                        return false;
                    }
                });
            },
        }
    });
</script>
<style>
    .form_box {
        background-color: #f3f3f3;
        padding: 0 0 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .margin-button-20px{
        margin-bottom: 20px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }

    .el-form-item > div{
        margin: 0 !important;
    }
    .title{
       display:flex;
	   align-items: center;
	   font-size: 12px;
	   padding-left: 50px;
	   margin-bottom: 16px;
    }
	.title-one{
		font-size: 14px;
		font-weight: 600;
		margin-right: 10px;
	}
	.segmentation{
		padding: 0 10px;
		font-size: 20px;
	}
	.inp{
		width: 140px;
	}
	.inp .el-input__inner{
		border-radius: 4px 0 0 4px;
	}
    .inp2{
        width: 60px;
    }
	.inp-text{
		background: #EFEFEF;
		border: 1px solid #E3E2E5;
		font-size: 15px;
		padding: 0px 15px;
		border-radius: 0 4px 4px 0;
		height: 32px;
	}

	
	/* flex公共样式 */
	.flex {
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
	}
	.flex-x-center {
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
		-webkit-box-pack: center;
		-webkit-justify-content: center;
		-ms-flex-pack: center;
		justify-content: center;
	}
	.flex-x-between {
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
		-webkit-box-pack: space-between;
		-webkit-justify-content: space-between;
		-ms-flex-pack: space-between;
		justify-content: space-between;
	}
	
	.flex-y-center {
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
		-webkit-box-align: center;
		-webkit-align-items: center;
		-ms-flex-align: center;
		-ms-grid-row-align: center;
		align-items: center;
	}
	
	.flex-y-bottom {
		display: -webkit-box;
		display: -webkit-flex;
		display: flex;
		-webkit-box-align: end;
		-webkit-align-items: flex-end;
		-ms-flex-align: end;
		-ms-grid-row-align: flex-end;
		align-items: flex-end;
	}
</style>