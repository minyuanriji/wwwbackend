<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 50% 20px 20px;
        background-color: #fff;
        margin-bottom: 20px;
        min-width: 1000px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    }
	.demo-input-suffix{
		display: flex;
	}
	.demo-input-suffix .member-money{
		width: 160px;
		margin-right: 20px;
	}
</style>

 
<section id="app" v-cloak>
    <el-card class="box-card" v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header" class="clearfix">
            <el-breadcrumb separator="/">
				<el-breadcrumb-item>新建卡密</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <el-form-item label="名称" prop="name">
                    <el-input :disabled="isLook" size="small" v-model="form.name" placeholder="请输入卡密名称" autocomplete="off"></el-input>
                </el-form-item>
				
				<!-- 领卡送购物券 -->
				<el-form-item label="领卡送积分" prop="status">
					<div>
						<el-switch
								:disabled="isLook"
								v-model="isPermanent"
								:active-value="1"
								:inactive-value="0"
								active-text="限时有效"
								inactive-text="永久有效"
								@change="isPermanentChange">
						</el-switch>
					</div>
					<div class="demo-input-suffix agent-setting-item">
						
						<el-input :disabled="isLook" size="small" v-model="form.integral_setting.integral_num" type="number" class="member-money" placeholder="">
							<template slot="append">积分券</template>
						</el-input>
						<el-input :disabled="isLook" size="small" v-model="form.integral_setting.period" type="number" class="member-money" placeholder="">
							<template slot="append">{{form.integral_setting.period_unit=='month'?'月':'周'}}</template>
						</el-input>
						<el-input :disabled="isLook" size="small" v-if="isPermanent==1" v-model="form.integral_setting.expire" type="number" class="member-money" style="width: 180px;" placeholder="">
							<template slot="append">有效期(天)</template>
						</el-input>
					</div>
				</el-form-item>
				
				<el-form-item label="绑定用户" prop="user_id">
				    <el-autocomplete :disabled="isLook" size="small" v-model="form.nickname" value-key="nickname"
						:fetch-suggestions="querySearchAsync" placeholder="请输入搜索内容"
						@select="inviterClick"></el-autocomplete>
				</el-form-item>
				
				<el-form-item label="卡有效期" prop="status">
					<div class="block">
					    <el-date-picker size="small"
							:disabled="isLook"
							v-model="form.expire_time"
							type="datetime"
							placeholder="选择卡有效期">
					    </el-date-picker>
					</div>
				</el-form-item>
				
				<el-form-item label="生成张数" prop="status">
					<div class="demo-input-suffix agent-setting-item">
						<el-input :disabled="isLook" v-model="form.generate_num" size="small" type="number" class="member-money"  placeholder="">
							<template slot="append">张</template>
						</el-input>
					</div>
				</el-form-item>
				<!-- <el-form-item label="手续费" prop="status">
					<div class="demo-input-suffix agent-setting-item">
						<el-input :disabled="isLook" v-model="form.fee" size="small" type="number" class="member-money"  placeholder="">
							<template slot="append">元</template>
						</el-input>
					</div>
				</el-form-item> -->
				
            </el-form>
        </div>
        <el-button v-if="!isLook" class="button-item" type="primary" :loading="btnLoading" @click="onSubmit">保存</el-button>
        <el-button v-if="!isLook" class="button-item" :loading="btnLoading" @click="">取消</el-button>
    </el-card>
</section>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
			isLook : false,	//默认不是查看
			isPermanent : 0,	//默认永久
            form: {
				"id":"-1",//编辑必须传送
				"name":"",
				"user_id": "",
				"integral_setting": {
					"integral_num": "",
					"period": "",
					"period_unit": "month",		//week
					"expire": "-1"
				},
				"generate_num": "",
				"expire_time": "",
				// "fee": "",
				"nickname":'',	//发请求时要删除
            },
            keyword: '',
            listLoading: false,
            btnLoading: false,
            FormRules: {
                name: [
                    { required: false, message: '名称不能为空', trigger: 'blur' },
                ],
                virtual_time: [
                    { required: true, message: '评价时间不能为空', trigger: 'blur' },
                ],
                goods_id: [
                    {required: true, message: '商品不能为空', trigger: 'change'},
                ],
                score: [
                    { required: true, message: '评分不能为空', trigger: 'blur' },
                ],
                is_show: [
                    { required: true, message: '是否显示不能为空', trigger: 'blur' },
                ],
                is_anonymous: [
                    { required: true, message: '是否匿名不能为空', trigger: 'blur' },
                ],
            },
        };
    },
    methods: {
		
		//0.1 模糊搜索代理商
		querySearchAsync(queryString, cb) {
		    this.keyword = queryString;
		    this.searchUser(cb);
		},
		// 0.2 模糊搜索代理商的请求
		searchUser(cb) {
		    request({
		        params: {
		            r: 'plugin/integral_card/admin/card/find-agent',
		            keyword: this.keyword
		        },
		    }).then(e => {
		        if (e.data.code === 0) {
					let list = e.data.data.list;
					if(list.length>0){
						list.forEach(function (item, index) {
							item['nickname']=item.nickname;
							delete item.user
						});
					}
		            cb(e.data.data.list);
		        } else {
		            this.$message.error(e.data.msg);
		        }
		    }).catch(e => {
		    });
		},
		// 0.3 拿到这个id
		inviterClick(row) {
		    this.form.user_id = row.id;
			console.log(this.form.user_id);
			console.log(this.form.nickname);
		},
		
		// 如果是效时有效
		isPermanentChange(){
			if(this.isPermanent){
				let integral_setting = this.form.integral_setting;
				'expire' in integral_setting?integral_setting.expire=1:integral_setting['expire']=1;
				console.log(this.form.integral_setting);
			}
		},

        clerkClick(row) {
            this.form = Object.assign(this.form, { goods_id: row.id });
        },
		
		// 新增/编辑--要做表单校验的
        onSubmit() {
            this.$refs.form.validate((valid) => {
                if (valid) {
                    this.btnLoading = true;
                    // let para = Object.assign(this.form);
					let formData = this.form;
					delete formData.nickname
					formData.id==-1?delete formData.id:'';	//新增的时候删掉id
                    request({
                        params: {
                            r: 'plugin/integral_card/admin/card/edit',
                        },
                        data: {form:formData},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            navigateTo({ r: 'plugin/integral_card/admin/card/index' });
                        } else {
                            this.$message.error(e.data.msg);
                        }
                        this.btnLoading = false;
                    }).catch(e => {
                        this.btnLoading = false;
                    });
                }
            });
        },
		// 2.0 获取卡券编辑回显信息
        getList() {
            this.listLoading = true;
            request({
                params: {
                    r: 'plugin/integral_card/admin/card/edit',
                    id: getQuery('id'),
                },
            }).then(e => {
                if (e.data.code == 0) {
                    if (e.data.data.info) {
						let info = e.data.data.info;
						this.form.id = info.id;
						this.form.name = info.name;
						this.form.user_id = info.user_id;
						this.form.integral_setting = info.integral_setting;
						if(this.form.integral_setting.expire&&this.form.integral_setting.expire!=-1){
							this.isPermanent = 1;
						}
						this.form.generate_num = info.generate_num;
						this.form.expire_time = this.formatDate(info.expire_time);
						console.log('this.form.expire_time:'+this.form.expire_time);
						// this.form.fee = info.fee;
						this.form.nickname = info.user.nickname;
                        // console.log(this.form, 123);
                    }
                }
                this.listLoading = false;
            }).catch(e => {
                this.listLoading = false;
            });
        },
		
		// 秒级时间戳转标准时间格式
		formatDate: function (value) {
			let date = new Date(value*1000);
			let y = date.getFullYear();
			let MM = date.getMonth() + 1;
			MM = MM < 10 ? ('0' + MM) : MM;
			let d = date.getDate();
			d = d < 10 ? ('0' + d) : d;
			let h = date.getHours();
			h = h < 10 ? ('0' + h) : h;
			let m = date.getMinutes();
			m = m < 10 ? ('0' + m) : m;
			let s = date.getSeconds();
			s = s < 10 ? ('0' + s) : s;
			return y + '-' + MM + '-' + d + ' ' + h + ':' + m + ':' + s;
		}
    },

    mounted() {
		// 如果是编辑的时候就回显
		if(getQuery('id')){
			this.getList();
		}
		if(getQuery('isLook')){
			this.isLook = true;
			console.log(this.isLook);
		}
		
    }
})
</script>