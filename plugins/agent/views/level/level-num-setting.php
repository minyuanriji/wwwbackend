<!-- //名额设置 -->
<?php

/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:50
 */
Yii::$app->loadComponentView('com-dialog-select');
Yii::$app->loadComponentView('com-select-cat');
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/agent/mall/level/index'})">
                        经销商等级
                    </span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>名额赠送设置</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form size="small" label-width="150px">
                <el-row>
                    <el-col :span="24">
						<el-form-item label="升级送名额">
						   <template>
							  <el-table
								:data="levels"
								style="width: 100%;margin-top: 20px;">
								<el-table-column
								  prop="name"
								  label="等级名称"
								  width="180">
								</el-table-column>
								<el-table-column
								    prop="address"
								    label="赠送名额">
										<template slot-scope="scope">
											<el-input v-model="scope.row.val" :min="0" type="number">
												<template slot="append">个</template>
											</el-input>
										</template>
								</el-table-column>
							  </el-table>
							</template>
						</el-form-item>
						
						<el-form-item label="推广送名额">
						   <template>
							  <el-table 
								:data="invited_level"
								style="width: 100%;margin-top: 20px;">
								<el-table-column
								  prop="name"
								  label="等级名称"
								  width="180">
								</el-table-column>
								<el-table-column 
									label="赠送名额">
										<template slot-scope="scope">
											<el-table
												:data="scope.row.levels"
												:show-header="false"
												>
												<el-table-column
												  prop="name"
												  width="80">
												</el-table-column>
												<el-table-column>
													<template slot-scope="scopes">
														<el-input v-model="scopes.row.val" :min="0" type="number">
															<template slot="append">个</template>
														</el-input>
													</template>
												</el-table-column>
											</el-table>
										</template>
								</el-table-column>
							  </el-table>
							</template>
						</el-form-item>
						
						
                    </el-col>
                </el-row>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="submitForm()" size="small">保存
        </el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                msg: 1,
				setting : {},	//等级奖励返显
                levels: [],	    //经销商等级
				invited_level : [],	//推广二维数组
				levelup_give_setting : [],
				invited_give_setting : [],
				
                btnLoading: false,
                cardLoading: false,
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.loadData();
            }
        },
        methods: {
			// 0.1 经销商名额设置获取
			loadData() {
			    this.cardLoading = true;
			    request({
			        params: {
			            r: 'plugin/agent/mall/level/level-num-setting',
			            id: getQuery('id'),
			        },
			        method: 'get'
			    }).then(e => {
			        this.cardLoading = false;
			        if (e.data.code == 0) {
						this.levels = [];
						this.setting = e.data.data.setting;
						let levels = e.data.data.levels;
						let levelup_give_setting = [];
						let invited_give_setting = [];
						// 如果初次设置
						if((!this.setting.levelup_give_setting && !this.setting.invited_give_setting) || (this.setting.levelup_give_setting.length==0 && this.setting.invited_give_setting.length==0)){	//如果是空数组，构造本地数据
							for (var i = 0; i < levels.length; i++) {
								var obj = levels[i];
								let newKey = String(obj.level);
								obj[newKey] = 0*1;	//初始化值
								obj['val'] = 0*1;	//初始化值
							}
							this.levels = JSON.parse(JSON.stringify(levels));
							// console.log(this.levels);
							let invited_level = [];
							for (var i = 0; i < levels.length; i++) {
								var obj = levels[i];
								obj['levels'] = JSON.parse(JSON.stringify(levels));	//深拷贝
								invited_level.push(obj);
							}
							this.invited_level = invited_level;
							console.log(this.invited_level);
						}else{	//有返显数据
							// console.log(this.setting.levelup_give_setting);
							// console.log(this.setting.invited_give_setting);
							levels.forEach((item,index) => {
								let newKey = String(item.level);
								item[newKey] = this.setting.levelup_give_setting[item.level]?this.setting.levelup_give_setting[item.level]:0;
								item['val'] = this.setting.levelup_give_setting[item.level]?this.setting.levelup_give_setting[item.level]:0;
								console.log(this.setting.levelup_give_setting);
								console.log(item);
							})
							this.levels = JSON.parse(JSON.stringify(levels));
							
							let invited_level = JSON.parse(JSON.stringify(this.levels));
							let invited_level_Two = JSON.parse(JSON.stringify(this.levels));
							let invited_give_setting = this.setting.invited_give_setting;
							invited_level.forEach((item,index) => {
								let newKey = String(item.level);	//外层level
								invited_level_Two.forEach((its,ids) => {
									let newTwoKey = String(its.level);	//内层level
									// console.log('newKey+'+newKey)
									// console.log('newTwoKey+'+newTwoKey)
									if(invited_give_setting[newKey]){
										if(invited_give_setting[newKey][newTwoKey]){
											its[newKey] = invited_give_setting[newKey][newTwoKey];
											its.val = invited_give_setting[newKey][newTwoKey];
										}else{
											its[newKey] = 0;
											its.val = 0;
										}
										console.log('its.newKey+'+its[newKey])
									}else{
										its[newKey] = 0;
										its.val = 0;
									}
								})
								item['levels'] = JSON.parse(JSON.stringify(invited_level_Two));	//深拷贝循环赋值
							})
							this.invited_level = JSON.parse(JSON.stringify(invited_level));
						}
						
						
			        } else {
			            this.$message.error(e.data.msg);
			        }
			
			    }).catch(e => {
			        console.log(e);
			    });
			},
			
			// 提交
            submitForm(formName) {
				// console.log(this.levels);
				// console.log(this.invited_level);
				let levels = JSON.parse(JSON.stringify(this.levels));
				let invited_level = JSON.parse(JSON.stringify(this.invited_level));
				// 0.1 获取一维数组
				let levelup_give_setting = {};
				levels.forEach(item => {
					let newKey = String(item.level);
					levelup_give_setting[newKey] = item.val;	
				})
				console.log(levelup_give_setting);
				// 0.1 获取二维数组
				let invited_give_setting = {};
				invited_level.forEach(item => {
					let mini_invited = {};
					item.levels.forEach(its => {
						let newKey = String(its.level);
						mini_invited[newKey] = its.val;	
					})
					let twoKey = String(item.level);
					invited_give_setting[twoKey] = mini_invited;	
				})
				console.log(invited_give_setting);
				self.btnLoading = true;
				request({
				    params: {
				        r: 'plugin/agent/mall/level/level-num-setting'
				    },
				    method: 'post',
				    data: {
				        form: {
							id: getQuery('id'),
							levelup_give_setting : levelup_give_setting,
							invited_give_setting : invited_give_setting,
						},
				    }
				}).then(e => {
				    self.btnLoading = false;
				    if (e.data.code == 0) {
						self.$message.success(e.data.msg);
				        navigateTo({
				            r: 'plugin/agent/mall/level/index'
				        })
				    } else {
				        self.$message.error(e.data.msg);
				    }
				}).catch(e => {
				    self.$message.error(e.data.msg);
				    self.btnLoading = false;
				});
			},
			
		}
    });
</script>

<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 20%;
        min-width: 900px;
    }

    .form-body .el-form-item {
        padding-right: 25%;
        min-width: 850px;
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

    .check-group {
        font-size: 14px !important;
    }

    .check-group .el-col {
        display: flex;
    }

    .check-group .el-input {
        margin: 0 5px;
    }

    .check-group .el-col .el-checkbox {
        display: flex;
        align-items: center;
    }

    .check-group .el-col .el-input {
        width: 100px;
    }

    .check-group .el-col .el-input .el-input__inner {
        height: 30px;
        width: 100px;
    }

    .el-checkbox-group .el-col {
        margin-bottom: 10px;
    }

    .condition_common {
        display: flex;
        align-items: center;
    }
	el-table{
		margin-top: 20px;
	}
</style>