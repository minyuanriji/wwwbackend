<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Author: zal
 * Date: 2020-04-14
 * Time: 17:16
 */
Yii::$app->loadComponentView('com-rich-text')
?>

<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
        border-radius: 50%;
    }
    .text {
        cursor: pointer;
        color: #419EFB;
    }
</style>
<section id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="cardLoading">
        <div slot="header">
            <div flex="cross:center box:first">
                <div><span @click="$navigate({r:'mall/store/index'})" class="text">门店管理</span>/门店编辑</div>
                <div flex="dir:right">
                    <div>
                        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="门店名称" prop="name">
                            <el-input v-model="ruleForm.name" placeholder="请输入门店名称"></el-input>
                        </el-form-item>
                        <el-form-item label="联系电话" prop="mobile">
                            <el-input v-model="ruleForm.mobile" placeholder="请输入门店联系电话"></el-input>
                        </el-form-item>
                        <el-form-item label="门店地址" prop="address">
                            <el-input v-model="ruleForm.address" placeholder="请输入门店地址"></el-input>
                        </el-form-item>
                        <el-form-item prop="latitude_longitude">
                            <template slot='label'>
                                <span>门店经纬度</span>
                                <el-tooltip effect="dark" content="纬度 | 经度,可在地图上选择位置"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input v-model="ruleForm.latitude_longitude" placeholder="请输入门店经纬度"></el-input>
                        </el-form-item>
                        <el-form-item label="地图">
                            <com-map @map-submit="mapEvent"
                                     :address="ruleForm.address"
                                     :lat="ruleForm.latitude"
                                     :long="ruleForm.longitude">
                                <el-button size="mini">展开地图</el-button>
                            </com-map>
                        </el-form-item>
                        <el-form-item label="门店评分" prop="score">
                            <el-select v-model="ruleForm.score" placeholder="请选择">
                                <el-option
                                        v-for="item in scoreOptions"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="门店封面图" prop="cover_url">
                            <com-attachment v-model="ruleForm.cover_url" :multiple="false" :max="1">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:150 * 150"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px'
                                       :src="ruleForm.cover_url"></com-image>
                        </el-form-item>

                        <el-form-item label="门店轮播图" prop="pic_url">
                            <com-attachment :max="6" @selected="picUrl" :multiple="true">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 360"
                                            placement="top">
                                    <el-button size="mini">选择图片</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <div style="margin-top: 20px;">
                                <template v-if="ruleForm.pic_url.length">
                                    <draggable v-model="ruleForm.pic_url" flex="warp:warp">
                                        <div style="margin-right: 20px;position: relative;cursor: move;"
                                             v-for="(item, index) in ruleForm.pic_url"
                                             :key="item.id">
                                            <com-attachment @selected="updatePicUrl" :params="{'currentIndex': index}">
                                                <com-image mode="aspectFill"
                                                           width="80px"
                                                           height='80px'
                                                           :src="item.pic_url">
                                                </com-image>
                                            </com-attachment>
                                            <el-button class="del-btn"
                                                       size="mini" type="danger" icon="el-icon-close"
                                                       @click="delPic(index)"></el-button>
                                        </div>
                                    </draggable>
                                </template>
                                <template v-else>
                                    <com-image mode="aspectFill"
                                               width="80px"
                                               height='80px'
                                               :src="ruleForm.default_url">
                                    </com-image>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item label="营业时间" prop="start_time">
                            <el-time-select
                                    placeholder="起始时间"
                                    v-model="ruleForm.start_time"
                                    :picker-options="{
      start: '00:00',
      step: '00:15',
      end: '23:45'
    }">
                            </el-time-select>
                            <el-time-select
                                    placeholder="结束时间"
                                    v-model="ruleForm.end_at"
                                    :picker-options="{
      start: '00:00',
      step: '00:15',
      end: '23:45',
      minTime: ruleForm.start_time
    }">
                            </el-time-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="门店描述" prop="description">
                    <com-rich-text style="width: 455px" v-model="ruleForm.description"
                                   :value="ruleForm.description"></com-rich-text>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    name: '',
                    mobile: '',
                    address: '',
                    latitude_longitude: '',
                    cover_url: '',
                    pic_url: [],
                    score: 5,
                    description: '',
                    business_hours: '',
                    start_time: '',
                    end_at: '',
                },
                scoreOptions: [
                    {
                        label: '1分',
                        value: 1
                    },
                    {
                        label: '2分',
                        value: 2
                    },
                    {
                        label: '3分',
                        value: 3
                    },
                    {
                        label: '4分',
                        value: 4
                    },
                    {
                        label: '5分',
                        value: 5
                    },
                ],
                rules: {
                    name: [
                        {required: true, message: '请输入门店名称', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '请输入门店联系方式', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '请输入门店地址', trigger: 'change'},
                    ],
                    latitude_longitude: [
                        {required: true, message: '请输入门店经纬度', trigger: 'change'},
                        {
                            validator(rule, value, callback, source, options) {
                                let str = value.split(",");
                                if (str.length < 2) {
                                    callback("经纬度不合规范")
                                } else {
                                    callback();
                                }
                            }
                        }
                    ],
                    business_hours: [
                        {required: true, message: '请输入门店营业时间', trigger: 'change'},
                    ],
                    description: [
                        {required: true, message: '请输入门店描述', trigger: 'change'},
                    ],
                    cover_url: [
                        {required: true, message: '请添加门店封面图', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请添加门店轮播图', trigger: 'change'},
                    ],
                    start_time: [
                        {required: true, message: '请添加营业时间', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
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
                                r: 'mall/store/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/store/index'
                                })
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
                        r: 'mall/store/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            coverUrl(e) {
                if (e.length) {
                    this.ruleForm.cover_url = e[0].url;
                    this.$refs.ruleForm.validateField('cover_url');
                }
            },
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    self.ruleForm.pic_url = [];
                    e.forEach(function (item, index) {
                        self.ruleForm.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                    this.$refs.ruleForm.validateField('pic_url');
                }
            },
            mapEvent(e) {
                this.ruleForm.address = e.address;
                this.ruleForm.latitude_longitude = e.lat + ',' + e.long;
            },
            delPic(index) {
                this.ruleForm.pic_url.splice(index, 1);
            },
            updatePicUrl(e, params) {
                this.ruleForm.pic_url[params.currentIndex].id = e[0].id;
                this.ruleForm.pic_url[params.currentIndex].pic_url = e[0].url;
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>
