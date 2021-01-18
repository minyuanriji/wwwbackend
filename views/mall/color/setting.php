<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 全局颜色设置
 * Author: zal
 * Date: 2020-09-14
 * Time: 14:16
 */
?>

<style>
    .nav-box {
        width: 220px;
        height: 45px;
        line-height: 45px;
        border: 1px solid #000000;
        text-align: center;
    }

    .bottom-icon {
        width: 80px;
        height: 80px;
        margin-right: 10px;
        border: 1px solid #eeeeee;
        cursor: move;
    }

    .nav-action {
        cursor: pointer;
    }

    .nav-icon {
        width: 30px;
        height: 30px;
    }

    .nav-add {
        border: 1px dashed #eeeeee;
        cursor: pointer;
    }

    .nav-add-icon {
        font-size: 50px;
        color: #eeeeee;
    }

    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        padding: 9px 25px;
    }

    .mobile {
        width: 404px;
        height: 736px;
        border-radius: 30px;
        background-color: #fff;
        padding: 33px 12px;
        margin-right: 10px;
    }

    .screen {
        border: 2px solid #F3F5F6;
        height: 670px;
        width: 380px;
        margin: 0 auto;
        position: relative;
        background-color: #F7F7F7;
    }

    .screen .head {
        position: absolute;
        top: 0;
        left: 0;
        width: 376px;
        height: 60px;
        line-height: 60px;
        font-size: 18px;
        font-weight: bolder;
        text-align: center;
    }

    .screen .foot {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 376px;
        /*padding: 5px 25px;*/
        height: 45px;
        /*display: flex;*/
        /*text-align: center;*/
        /*justify-content: space-between;*/
        /*font-size: 11px;*/
    }

    .screen .foot .nav-icon {
        height: 20px;
        width: 20px;
    }

    .screen .foot .nav-icon + div {
        margin-top: -10px;
    }

    .title {
        padding: 18px 20px;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }
    .mall-bg{
        position: absolute;
        top: 50px;
        width: 100%;
        height: 180px;
        background-size: 100%;
        background-repeat: no-repeat;
    }
</style>

<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div>
                <span>全局颜色设置</span>
            </div>
        </div>
        <div style="display: flex;">
            <div class="mobile">
                <div class="screen">
                    <div class="head"
                         :style="{backgroundColor: ruleForm.top_background_color, color: ruleForm.global_text_color}">文字颜色
                    </div>
                    <img :src="ruleForm.coupon_pic_url" alt="" class="mall-bg">
                </div>
<!--                <div class="show-box" style="padding-bottom: 20px;">-->
<!--                   -->
<!--                </div>-->
            </div>
            <div style="width: 100%;">
                <el-form :model="ruleForm" size="small" ref="ruleForm" label-width="120px">
                    <div class="title">全局设置</div>
                    <el-row class='form-body'>
                        <el-col :span="6">
                            <el-form-item label="全局文字颜色" prop="color">
                                <el-color-picker
                                        color-format="hex"
                                        v-model="ruleForm.global_text_color"
                                        :predefine="predefineColors">
                                </el-color-picker>
                            </el-form-item>
                            <el-form-item label="优惠券图片" prop="coupon_pic_url">
                                <com-attachment :multiple="false" :max="1" @selected="couponPicUrl">
                                    <el-tooltip class="item" effect="dark" content="建议尺寸:691*196" placement="top">
                                        <el-button size="mini">选择文件</el-button>
                                    </el-tooltip>
                                </com-attachment>
                                <com-image width="80px"
                                           height="80px"
                                           mode="aspectFill"
                                           :src="ruleForm.coupon_pic_url">
                                </com-image>
                            </el-form-item>
<!--                            <el-form-item label="选中文字颜色" prop="active_color">-->
<!--                                <el-color-picker-->
<!--                                        color-format="rgb"-->
<!--                                        v-model="dialogRuleForm.active_color"-->
<!--                                        :predefine="predefineColors">-->
<!--                                </el-color-picker>-->
<!--                            </el-form-item>-->
                        </el-col>
                    </el-row>
                    <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')"
                               size="small">保存
                    </el-button>
                    <el-button class="button-item" :loading="btnLoading" size="small" plain @click="restoreDefault">
                        恢复默认
                    </el-button>
                </el-form>
            </div>
        </div>
    </el-card>
</div>
<script src="//cdn.jsdelivr.net/npm/sortablejs@1.8.3/Sortable.min.js"></script>
<!-- CDNJS :: Vue.Draggable (https://cdnjs.com/) -->
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    coupon_pic_url: '',
                    global_text_color: '#bc0100',
                },
                predefineColors: [
                    '#000',
                    '#fff',
                    '#888',
                    '#ff4544'
                ],
                btnLoading: false,
                cardLoading: false,

                activeName: 'first',
                isNavAction: false,//编辑删除框是否显示
                navIconIndex: -1,//控制导航编辑隐藏显示
                navEditIndex: -1, //当前编辑的导航索引
                dialogFormVisible: false,
                dialogRuleForm: {
                    active_color: '#ff4544',
                    active_icon: '',
                    color: '#888',
                    text: '',
                    icon: '',
                    url: '',
                    params: [],
                },
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/color/setting'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/color/setting',
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(tab, event) {
                console.log(tab, event);
            },
            // 鼠标移入事件
            navIconEnter(index) {
                this.navIconIndex = index;
            },
            // 鼠标离开事件
            navIconAway() {
                this.navIconIndex = -1;
            },
            // 导航图标编辑
            navIconEdit(index) {
                this.dialogFormVisible = true;
                if (index != -1) {
                    this.navEditIndex = index;
                    this.dialogRuleForm = this.ruleForm.navs[index]
                }
            },
            // 弹框关闭事件回调
            dialogColse() {
                this.navEditIndex = -1;
                this.clearDialogData();
            },
            clearDialogData() {
                this.dialogRuleForm = {
                    active_color: '#ff4544',
                    active_icon: '',
                    color: '#888',
                    text: '',
                    icon: '',
                    url: '',
                    params: [],
                };
            },
            // 导航图标删除
            navIconDestroy(index) {
                this.ruleForm.navs.splice(index, 1)
            },
            dialogFormSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.dialogFormVisible = false;
                        if (self.navEditIndex != -1) {
                            self.ruleForm.navs[self.navEditIndex] = self.dialogRuleForm;
                        } else {
                            self.ruleForm.navs.push(self.dialogRuleForm)
                        }
                        self.navEditIndex = -1;
                        this.clearDialogData();
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 添加图标
            iconUrl(e) {
                if (e.length) {
                    this.dialogRuleForm.icon = e[0].url
                    this.$refs.dialogRuleForm.validateField('icon');
                }
            },
            couponPicUrl(e) {
                if (e.length) {
                    // this.ruleForm.coupon_pic_url = e[0].url;
                    this.$set(this.ruleForm,'coupon_pic_url',e[0].url);
                    console.log(e[0].url,'e[0].url')
                    this.$refs.ruleForm.validateField('coupon_pic_url');
                }
            },
            // 添加选择状态图标
            activeIconUrl(e) {
                if (e.length) {
                    this.dialogRuleForm.active_icon = e[0].url
                    this.$refs.dialogRuleForm.validateField('active_icon');
                }
            },
            // 导航链接选择
            selectNavUrl(e) {
                let self = this;
                e.forEach(function (item, index) {
                    self.dialogRuleForm.url = item.new_link_url;
                    self.dialogRuleForm.open_type = !item.open_type || item.open_type === 'navigate' ? 'redirect' : item.open_type;
                    self.dialogRuleForm.params = item.params;
                })
            },
            // 恢复默认
            restoreDefault() {
                let self = this;
                self.$confirm('恢复默认, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.btnLoading = true;
                    request({
                        params: {
                            r: 'mall/color/default',
                        },
                        method: 'post',
                        data: {}
                    }).then(e => {
                        self.btnLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getDetail();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消')
                });
            },
            navClick(index) {
                this.navCurrent = index
            }
        },
        mounted: function () {
            this.getDetail();
        }
    });
</script>
