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
?>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>基础设置</span>
            </div>
        </div>
        <div class="form_box">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="150px">
                <el-card style="margin-top: 10px" shadow="never">

                    <el-col :span="14">
                        <el-form-item label="公司名" prop="company_name" >
                            <el-input v-model.number="ruleForm.company_name" type="text">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="公司地址" prop="company_address" >
                            <el-input v-model.number="ruleForm.company_address" type="text">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="名片添加命令" prop="card_token" >
                            <el-input v-model.number="ruleForm.card_token" type="text">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="公司logo" prop="company_logo" >
                            <com-attachment v-model="ruleForm.company_logo" :multiple="false" :max="1">
                                <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <div class="customize-share-title">
                                <com-image mode="aspectFill" width='80px' height='80px'
                                           :src="ruleForm.company_logo ? ruleForm.company_logo : ''"></com-image>
                                <el-button v-if="ruleForm.company_logo" class="del-btn" size="mini"
                                           type="danger" icon="el-icon-close" circle
                                           @click="ruleForm.company_logo = ''"></el-button>
                            </div>
                        </el-form-item>


                        <el-form-item prop="company_img">
                            <template slot="label">
                                <span>公司图文介绍(多张)</span>
                                <el-tooltip effect="dark" placement="top" content="第一张图片为封面图">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div class="pic-url-remark">
                                第一张图片为缩略图,可拖拽使其改变顺序，最多支持上传8张
                            </div>
                            <div flex="dir:left">
                                <template v-if="ruleForm.company_img.length">
                                    <draggable v-model="ruleForm.company_img" flex="dif:left">
                                        <div v-for="(item,index) in ruleForm.company_img" :key="index"
                                             style="margin-right: 20px;position: relative;cursor: move;">
                                            <com-attachment @selected="updatePicUrl"
                                                            :params="{'currentIndex': index}">
                                                <com-image mode="aspectFill" width="100px"
                                                           height='100px' :src="item.company_img">
                                                </com-image>
                                            </com-attachment>
                                            <el-button class="del-btn" size="mini" type="danger"
                                                       icon="el-icon-close" circle
                                                       @click="delPic(index)"></el-button>
                                        </div>
                                    </draggable>
                                </template>
                                <template v-if="ruleForm.company_img.length < 8">
                                    <com-attachment style="margin-bottom: 10px;" :multiple="true"
                                                    :max="9" @selected="picUrl">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750"
                                                    placement="top">
                                            <div flex="main:center cross:center" class="add-image-btn">
                                                + 添加图片
                                            </div>
                                        </el-tooltip>
                                    </com-attachment>
                                </template>
                            </div>
                        </el-form-item>


                        <el-form-item label="视频大小(mb)" prop="card_token" >
                            <el-input v-model.number="ruleForm.video_size" type="text">mb
                            </el-input>
                        </el-form-item>
                    </el-col>
                </el-card>
            </el-form>
            <el-button :loading="btnLoading" class="button-item" type="primary"
                       style="margin: 24px 70px"
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
                ruleForm: {
                    company_name: "",
                    card_token: "",
                    company_logo:"",
                    company_img:[],
                    company_address:"",
                    video_size:0
                },
                rules: {

                }
            }
        },
        mounted: function () {
            this.loadData();
        },
        methods: {
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/business_card/mall/setting/index',
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                      this.ruleForm = Object.assign(this.ruleForm, e.data.data.setting);
                      if (this.ruleForm.company_img == null || this.ruleForm.company_img == '') {
                          this.ruleForm.company_img = [];
                      }
                    }
                }).catch(e => {
                    this.loading = false;
                })
            },
            updatePicUrl(e, params) {
                this.ruleForm.company_img[params.currentIndex].id = e[0].id;
                this.ruleForm.company_img[params.currentIndex].company_img = e[0].url;
            },
            delPic(index) {
                this.ruleForm.company_img.splice(index, 1)
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/business_card/mall/setting/index',
                            },
                            method: 'post',
                            data: this.ruleForm
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.$message.error(e);
                            this.btnLoading = false;
                        })
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function (item, index) {
                        if (self.ruleForm.company_img.length >= 9) {
                            return;
                        }
                        self.ruleForm.company_img.push({
                            id: item.id,
                            company_img: item.url
                        });
                    });
                }
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
    .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }
    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .el-input-group__append {
        background-color: #fff;
        color: #353535;
    }

    .add-image-btn {
        border: 1px solid RGB(205,209,217);
        width: 100px;
        height: 100px;
    }
</style>