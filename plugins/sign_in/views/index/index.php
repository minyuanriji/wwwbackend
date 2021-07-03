<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-20
 * Time: 15:41
 */
?>
<style>
    @media screen and (min-width:1370px){
        .form-body {
            padding: 20px 0;
            padding-right: 50%;
            min-width: 1400px;
        }
    }

    @media screen and (max-width:1369px){
        .form-body {
            padding: 20px 0;
            padding-right: 20%;
        }
    }

    .form-body {
        background-color: #fff;
        margin-bottom: 20px;
    }


    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="loading">
        <div slot="header">签到设置</div>
        <div v-if="load">
            <div class="form-body">
                <el-form ref="form" :model="form" :rules="rule" label-width="120px" size="small">
                    <el-form-item label="是否开启" prop="status">
                        <el-switch v-model="form.status" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>

<!--                    <el-form-item label="自定义名称" prop="name">-->
<!---->
<!--                        <el-input type="text"  v-model="form.name"></el-input>-->
<!--                    </el-form-item>-->



<!--                    <el-form-item label="推送链接" prop="push_url">-->
<!---->
<!--                        <el-input type="text"  v-model="form.push_url"></el-input>-->
<!--                    </el-form-item>-->

                    <div style="font-size: 18px;color:#606266;margin-left: 15px">
                        奖励设置
                        <br/>
                        <br/>
                    </div>

<!--                    <hr style="color: #606266;"/><br/>-->
                    <el-form-item label="日常积分奖励" prop="normal">
                        <label slot="label">日常积分奖励
                            <el-tooltip class="item" effect="dark"
                                        content="每天签到赠送积分"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>
                        <el-input v-model.number="form.normal" type="number" style="width: 50%" placeholder="每天签到赠送"></el-input> 积分
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="日常优惠券奖励" >
                        <label slot="label">日常优惠券奖励</label>

                        <el-tooltip class="item" effect="dark"
                                    content="每天签到赠送优惠券"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>

                        <el-select placeholder="请选择优惠券" v-model="form.coupon" clearable @change="couponChange" @clear="couponClear">

                            <el-option
                                    v-for="item in department_list"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                        <el-input v-model.number="form.coupon_num" type="number" style="width: 20%" placeholder="每天签到赠送"></el-input> 张
                    </el-form-item>


                    <el-form-item label="连续签到" prop="continue">
                        <label slot="label">连续签到
                            <el-tooltip class="item" effect="dark"
                                        content="连续签到额外送XX积分/余额，
                                            只要达到要求才能领取奖励，每个连续时间内只能领取一次连续签到奖励"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </label>

                        <el-card style="margin-bottom: 10px" shadow="never" v-if="form.continue && form.continue.length > 0" v-for="(item, index) in form.continue" :key="item.id">
                                <el-row type="flex">

                                    <el-select placeholder="请选择优惠券" v-model="item.coupon_id">
                                        <el-option
                                                v-for="items in department_list"
                                                :key="items.id"
                                                :label="items.name"
                                                :value="items.id">
                                        </el-option>
                                    </el-select>
                                    <el-col :span="21">
                                        <el-form-item label="连续签到天数" required>
                                            <el-input v-model.number="item.day" type="number"></el-input>
                                        </el-form-item>
                                        <el-form-item label="赠送数量" required>
                                            <el-input v-model.number="item.number" type="number"style="width: 90%;"></el-input> 张
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="1"></el-col>
                                    <el-col :span="2">
                                        <el-button type="text" circle @click="continueDel(index)">
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </el-col>

                            </el-row>
                        </el-card>
                        <el-button @click="continueAdd" size="small" type="text">
                            <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                            <span style="color: #353535;font-size: 14px">新增优惠券规则</span>
                        </el-button>

                        <el-card shadow="never" v-if="form.total && form.total.length > 0" v-for="(item, index) in form.total" :key="item.id">
                            <el-row type="flex">
                                <el-col :span="23">
                                    <el-form-item label="连续签到天数" required>
                                        <el-input v-model.number="item.day" type="number"></el-input>
                                    </el-form-item>
                                    <el-form-item label="赠送数量" required>
                                        <el-input v-model.number="item.number" type="number" style="width: 90%;"></el-input> 积分
                                    </el-form-item>
                                </el-col>
                                <el-col :span="1"></el-col>
                                <el-col :span="2">
                                    <el-button size="small" type="text" @click="totalDel(index)">
                                        <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                            <img src="statics/img/mall/del.png" alt="">
                                        </el-tooltip>
                                    </el-button>
                                </el-col>
                            </el-row>
                        </el-card>

                        <el-button @click="totalAdd" size="small" type="text">
                            <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                            <span style="color: #353535;font-size: 14px">新增积分规则</span>
                        </el-button>
                    </el-form-item>
                </el-form>
            </div>
            <el-button class="button-item" :loading="btnLoading" type="primary" size="small" @click="store('form')">保存</el-button>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            function findEle(value, arr) {
                for (let i in arr) {
                    if (arr[i] == value) {
                        return true;
                    }
                }
                return false;
            }

            let listValidate = (rule, value, callback) => {
                let dayArr = [];
                for (let i in value) {
                    if (findEle(value[i].day, dayArr)) {
                        callback(new Error('不能设置相同的天数'));
                    }
                    if (value[i].coupon_id == '') {
                        callback(new Error('优惠券不能为空'));
                    }
                    dayArr.push(value[i].day);
                }
                callback();
            };
            return {
                testSelect:'',
                department_list:[],
                loading: false,
                load: false,
                btnLoading: false,
                copy_data:'',
                is_clear:false,
                form: {
                    coupon:[],
                    coupon_num:0,
                    status: 0,
                    name: '',
                    normal_type: 'integral',
                    normal: 0,
                    continue: [],
                    total: [],
                    continue_type: 1,
                    rule: ''
                },
                rule: {
                    continue: [
                        {validator: listValidate}
                    ],
                    total: [
                        {validator: listValidate}
                    ],
                    rule: [
                        {required: true, message: '请填写规则', trigger: 'change'},
                    ],
                    normal: [

                    ]
                },
                continue_temp: {
                    day: 0,
                    number: 0,
                    coupon_id:'',
                    type: 'coupon'
                },
                integral_rules: {
                    day: 0,
                    number: 0,
                    type: 'integral'
                }
            };
        },
        created() {
            this.getDepartment();
        },
        methods: {
            couponChange(e){
                console.log(e,'')
                this.copy_data.coupon = e;
            },
            couponClear(e){
                this.is_clear = true;
            },
            getDepartment() {
                request({
                    params: {
                        r: 'plugin/sign_in/mall/index/coupon',
                        limit:100,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        console.log(e.data.data.list,'department_list');
                        this.department_list = e.data.data.list;
                        this.loadData();
                    } else {

                    }
                    this.load = true;
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/sign_in/mall/index/index'
                    }
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        if (e.data.data.config) {
                            this.form = e.data.data.config;
                            this.copy_data = JSON.parse(JSON.stringify(e.data.data.config));

                            var objs = this.department_list.find((item) => item.id == this.form.coupon)
                            this.form.coupon = objs.name;

                            this.form.continue.forEach(item => {
                                this.department_list.forEach( items => {
                                    if(item.coupon_id == items.id){
                                        item.coupon_id = items.name;
                                    }
                                })
                            })
                            console.log(this.form,'this.formthis.form')
                        }
                        this.load = true;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.$message.error(e);
                    this.btnLoading = false;
                });
            },
            store(formName) {
                var formObjs = JSON.parse(JSON.stringify(this.form));
                console.log(formObjs,'formObjsformObjsformObjsformObjs')
                formObjs.continue.forEach(item => {
                    this.department_list.forEach( items => {
                        if(item.coupon_id == items.name){
                            item.coupon_id = items.id;
                        }
                    })
                });
                console.log(this.copy_data.coupon,'this.copy_data.coupon')
                this.is_clear?formObjs.coupon=0:formObjs.coupon = this.copy_data.coupon;

                console.log(formObjs,'formObjsformObjsformObjsformObjs');
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/sign_in/mall/index/index'
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(formObjs),
                                ruleForm: formObjs
                            }
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg)
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        });
                    } else {
                        console.log(4);
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            continueAdd() {
                if (!this.form.continue) {
                    this.form.continue = [];
                }
                console.log(this.continue_temp,'this.continue_tempthis.continue_temp')
                this.form.continue.push(JSON.parse(JSON.stringify(this.continue_temp)));
            },
            totalAdd() {
                if (!this.form.total) {
                    this.form.total = [];
                }
                this.form.total.push(JSON.parse(JSON.stringify(this.integral_rules)));
            },
            continueDel(index) {
                if (this.form.continue && this.form.continue.length > index) {
                    this.form.continue.splice(index, 1);
                }
            },
            totalDel(index) {
                if (this.form.total && this.form.total.length > index) {
                    this.form.total.splice(index, 1);
                }
            }
        }
    });
</script>
