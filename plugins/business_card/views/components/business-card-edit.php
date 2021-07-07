<?php
?>
<style>
    .mt-24 {
        margin-bottom: 24px;
    }

    .business-card-edit .el-form-item__label {
        padding: 0 20px 0 0;
    }

    .business-card-edit .el-dialog__body h3 {
        font-weight: normal;
        color: #999999;
    }

    .business-card-edit .form-body {
        padding: 10px 20px 20px;
        background-color: #fff;
        margin-bottom: 30px;
    }

    .business-card-edit .button-item {
        padding: 9px 25px;
        margin-bottom: 10px;
    }

    .business-card-edit .sortable-chosen {
        /* border: 2px solid #3399ff; */
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .business-card-edit .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .business-card-edit .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .business-card-edit .app-share .app-share-bg .title {
        width: 160px;
        height: 29px;
        line-height: 1;
        word-break: break-all;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .business-card-edit .app-share .app-share-bg .pic-image {
        background-repeat: no-repeat;
        background-position: 0 0;
        background-size: cover;
        width: 160px;
        height: 130px;
    }

    .bottom-div {
        border-top: 1px solid #E3E3E3;
        position: fixed;
        bottom: 0;
        background-color: #ffffff;
        z-index: 999;
        padding: 10px;
        width: 80%;
    }

    .business-card-edit .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .business-card-edit .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .business-card-edit .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .business-card-edit .share-title {
        font-size: 16px;
        color: #303133;
        padding-bottom: 22px;
        border-bottom: 1px solid #e2e2e2;
    }

    .box-grow-0 {
        /* flex 子元素固定宽度*/
        min-width: 0;
        -webkit-box-flex: 0;
        -webkit-flex-grow: 0;
        -ms-flex-positive: 0;
        flex-grow: 0;
        -webkit-flex-shrink: 0;
        -ms-flex-negative: 0;
        flex-shrink: 0;
    }
</style>
<template id="business-card-edit">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="business-card-edit"
             v-loading="cardLoading">
        <div class='form-body'>
            <el-form :model="cForm" :rules="cRule" ref="ruleForm" label-width="180px" size="small"
                     class="demo-ruleForm">
                <el-tabs v-model="activeName" @tab-click="handleClick">
                    <el-tab-pane label="名片详情" name="first" >
                        <!-- 基本信息 -->
                        <slot name="before_info"></slot>
                        <el-card shadow="never" class="mt-24">
                            <div slot="header">
                                <span>基本信息</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <template v-if="is_info == 1">
                                        <el-form-item prop="app_share_pic">
                                            <label slot="label">
                                                <span>头像</span>
                                            </label>
                                            <com-image mode="aspectFill" width="100px"
                                                       height='100px' :src="ruleForm.head_img">
                                            </com-image>
                                            <!--                                        <com-attachment v-model="ruleForm.head_img" :multiple="false" :max="1">-->
                                            <!--                                            <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"-->
                                            <!--                                                        placement="top">-->
                                            <!--                                                <el-button size="mini">选择图片</el-button>-->
                                            <!--                                            </el-tooltip>-->
                                            <!--                                        </com-attachment>-->
                                            <!--                                        <div class="customize-share-title">-->
                                            <!--                                            <com-image mode="aspectFill" width='80px' height='80px'-->
                                            <!--                                                       :src="ruleForm.app_share_pic ? ruleForm.app_share_pic : ''"></com-image>-->
                                            <!--                                            <el-button v-if="ruleForm.app_share_pic" class="del-btn" size="mini"-->
                                            <!--                                                       type="danger" icon="el-icon-close" circle-->
                                            <!--                                                       @click="ruleForm.app_share_pic = ''"></el-button>-->
                                            <!--                                        </div>-->
                                            <!--                                        <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"-->
                                            <!--                                                   type="text">查看图例-->
                                            <!--                                        </el-button>-->
                                        </el-form-item>
                                        <el-form-item label="姓名" prop="full_name">
                                            <div>{{ruleForm.full_name}}</div>
                                        </el-form-item>
                                        <el-form-item label="部门" prop="department_name">
                                            <div>{{ruleForm.department.name}}</div>
                                        </el-form-item>
                                        <el-form-item label="职位" prop="position_name">
                                            <div>{{ruleForm.position.name}}</div>
                                        </el-form-item>
                                        <el-form-item label="邮箱" prop="email">
                                            <div>{{ruleForm.email}}</div>
                                        </el-form-item>
                                        <el-form-item label="电话" prop="mobile">
                                            <div>{{ruleForm.mobile}}</div>
                                        </el-form-item>
                                        <el-form-item label="地址" prop="address">
                                            <div>{{ruleForm.address}}</div>
                                        </el-form-item>
                                        <el-form-item label="公司名称" prop="company_name">
                                            <div>{{ruleForm.company_name}}</div>
                                        </el-form-item>
                                        <el-form-item label="公司地址" prop="company_address">
                                            <div>{{ruleForm.company_address}}</div>
                                        </el-form-item>
                                        <el-form-item label="座机" prop="landline">
                                            <div>{{ruleForm.landline}}</div>
                                        </el-form-item>
                                    </template>

                                </el-col>
                            </el-row>
                        </el-card>


                        <!-- 图片简介 -->
                        <slot name="before_detail"></slot>
                        <el-card shadow="never" class="mt-24" v-if="is_detail == 1">
                            <div slot="header">
                                <span>详细信息</span>
                            </div>
                            <el-row>
                                <el-col :xl="12" :lg="16">
                                    <el-form-item label="个人标签">
                                        <el-tag v-for="(item,index) in ruleForm.tag" :key="index" effect="plain" style="margin: 0 10px 10px 0">{{item.name}}</el-tag>
                                    </el-form-item>
                                    <el-form-item label="自动标签">
                                        <el-tag v-for="(item,index) in ruleForm.auto_tag" :key="index" effect="plain" style="margin: 0 10px 10px 0">{{item.name}}</el-tag>
                                    </el-form-item>
                                    <el-form-item label="个人简介" prop="introduction">
                                        <div>{{ruleForm.introduction}}</div>
                                    </el-form-item>
                                    <el-form-item prop="images">
                                        <template slot="label">
                                            <span>图片简介(多张)</span>
                                        </template>
                                        <div flex="dir:left">
                                            <template v-if="ruleForm.images">
                                                <draggable v-model="ruleForm.images" flex="dif:left">
                                                    <div v-for="(item,index) in ruleForm.images" :key="index"
                                                         style="margin-right: 20px;position: relative;cursor: move;">
                                                        <com-attachment @selected="updatePicUrl"
                                                                        :params="{'currentIndex': index}">
                                                            <com-image mode="aspectFill" width="100px"
                                                                       height='100px' :src="item">
                                                            </com-image>
                                                        </com-attachment>
                                                        <!--                                                        <el-button class="del-btn" size="mini" type="danger"-->
                                                        <!--                                                                   icon="el-icon-close" circle-->
                                                        <!--                                                                   @click="delPic(index)"></el-button>-->
                                                    </div>
                                                </draggable>
                                            </template>
                                            <template v-if="ruleForm.pic_url && ruleForm.pic_url.length < 9">
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
                                    <el-form-item label="视频简介" prop="videos">

                                        <video :src="ruleForm.videos" controls="controls"></video>
                                        
<!--                                        <el-input v-model="ruleForm.videos" placeholder="请输入视频原地址或选择上传视频">-->
<!--                                            <template slot="append">-->
<!--                                                <com-attachment :multiple="false" :max="1" @selected="videoUrl"-->
<!--                                                                type="video">-->
<!--                                                    <el-tooltip class="item"-->
<!--                                                                effect="dark"-->
<!--                                                                content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"-->
<!--                                                                placement="top">-->
<!--                                                    </el-tooltip>-->
<!--                                                </com-attachment>-->
<!--                                            </template>-->
<!--                                        </el-input>-->
<!--                                        <el-link class="box-grow-0" type="primary" style="font-size:12px"-->
<!--                                                 v-if='ruleForm.videos' :underline="false" target="_blank"-->
<!--                                                 :href="ruleForm.videos">视频链接-->
<!--                                        </el-link>-->
                                    </el-form-item>

                                </el-col>
                            </el-row>
                        </el-card>
                        <slot name="after_detail"></slot>
                    </el-tab-pane>
                    <slot name="tab_pane"></slot>
                </el-tabs>
            </el-form>
            <div class="bottom-div" flex="cross:center">
                <el-button class="button-item" :loading="btnLoading" type="primary" size="small"
                           @click="store('ruleForm')">保存
                </el-button>
            </div>
        </div>

    </el-card>
</template>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>
<script>
    Vue.component('business-card-edit', {
        template: '#business-card-edit',
        props: {
            // 选择分类  0--不显示 1--显示可编辑
            is_cats: {
                type: Number,
                default: 0
            },
            // 基本信息
            is_basic: {
                type: Number,
                default: 1
            },
            is_info: {
                type: Number,
                default: 0
            },
            // 规格库存
            is_attr: {
                type: Number,
                default: 1
            },
            // 商品设置
            is_goods: {
                type: Number,
                default: 1
            },
            // 商品详情
            is_detail: {
                type: Number,
                default: 1
            },
            // 请求数据地址
            url: {
                type: String,
                default: 'plugin/business_card/mall/business-card/detail'
            },
            // 请求数据地址
            get_goods_url: {
                type: String,
                default: 'plugin/business_card/mall/business-card/detail'
            },
            // 保存之后返回地址
            referrer: {
                default: 'plugin/business_card/mall/business-card/index'
            },
            is_mch: {
                type: Number,
                default: 0
            },
            mch_id: {
                type: Number,
                default: 0
            },
            // 页面上数据
            form: Object,
            // 数据验证方式
            rule: Object,
            status_change_text: {
                type: String,
                default: '',
            },
            // 是否使用表单
            is_form: {
                type: Number,
                default: 1
            },
            sign: String,

            is_save_btn: {
                type: Number,
                default: 1
            },
            previewInfo: {
                type: Object,
                default: function () {
                    return {
                        is_head: true,
                        is_cart: true,
                        is_mch: this.is_mch == 1
                    }
                }
            },
        },
        data() {
            let ruleForm = {
                images: [],
                department: '',
                position: '',
                browsing_history: '',
                tag: [],
                status: 0,
                visitors: 0,
                head_img: '',
                videos: '',
                full_name: '',
                address: '',
                mobile: '',
                company_logo: '',
                company_name: '',
                email: '',
                introduction: '',
                landline: '',
                wechat_qrcode: '',
            };
            let rules = {
                name: [
                    {required: true, message: '请输入商品名称', trigger: 'change'},
                ],
                price: [
                    {required: true, message: '请输入商品价格', trigger: 'change'}
                ],
                original_price: [
                    {required: true, message: '请输入商品原价', trigger: 'change'}
                ],
                cost_price: [
                    {required: false, message: '请输入商品成本价', trigger: 'change'}
                ],
                unit: [
                    {required: true, message: '请输入商品单位', trigger: 'change'},
                    {max: 5, message: '最大为5个字符', trigger: 'change'},
                ],
                goods_num: [
                    {required: true, message: '请输入商品总库存', trigger: 'change'},
                ],
                is_area_limit: [
                    {required: false, type: 'integer', message: '请选择是否开启', trigger: 'blur'}
                ],
                pic_url: [
                    {required: true, message: '请上传商品轮播图', trigger: 'change'},
                ],
            };
            return {
                id: 0,
                keyword: '',
                goods_type: 'MALL_GOODS',
                label_list: [],
                cardLoading: false,
                btnLoading: false,
                dialogLoading: false,
                activeName: 'first',
                ruleForm: ruleForm,
                rules: rules,
                images: [], // 图片
                position: [], //职位
                department: [],//部门
                tag: [], //标签
            };
        },
        created() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.goods_id = getQuery('id');
            }
            //this.getSvip();
            //this.getLabels();
        },
        computed: {
            cForm() {
                let form = {};
                let ruleForm = JSON.parse(JSON.stringify(this.ruleForm));
                if (this.form) {
                    form = Object.assign(ruleForm, JSON.parse(JSON.stringify(this.form)));
                } else {
                    form = ruleForm;
                }
                if (getQuery('id')) {
                    form.id = getQuery('id')
                }
                return form;
            },
            cRule() {
                return this.rule ? Object.assign({}, this.rules, this.rule) : this.rules;
            },
            isConfineCount() {
                return this.ruleForm.confine_count === -1;
            },

        },
        methods: {
            showPreview(){

            },
            delPic(index) {
                this.ruleForm.pic_url.splice(index, 1)
            },

            getPermissions() {
                let self = this;
                request({
                    params: {
                        r: 'mall/index/mall-permissions'
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.is_show_distribution = 0;
                        e.data.data.permissions.forEach(function (item) {
                            if (item === 'distribution') {
                                self.is_show_distribution = 1;
                            }
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            store(formName) {
                let self = this;
                try {
                    self.cForm.attr.map(item => {
                        if (item.price < 0 || item.price === '') {
                            throw new Error('规格价格不能为空');
                        }
                        if (item.stock < 0 || item.stock === '') {
                            throw new Error('库存不能为空');
                        }
                    })
                } catch (error) {
                    self.$message.error(error.message);
                    return;
                }
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.btnLoading = true;
                        if (self.is_svip) {
                            self.cForm.is_vip_card_goods = self.is_vip_card_goods
                        } else {
                            delete self.cForm['is_vip_card_goods']
                        }
                        request({
                            params: {
                                r: this.url
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(self.cForm),
                                attrGroups: JSON.stringify(self.attrGroups),
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code === 0) {
                                //保存成功
                                self.$message.success(e.data.msg);
                                /*  if (typeof this.referrer === 'object') {
                                      navigateTo(this.referrer)
                                  } else {
                                      navigateTo({
                                          r: this.referrer,
                                      })
                                  }*/
                                navigateTo({
                                    r: this.referrer,
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
                        console.log('error submit!!');
                        self.$message.error('请填写必填参数');
                        return false;
                    }
                });
            },
            getDetail(id, url = '') { //请求初始化数据
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: url ? url : this.get_goods_url,
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    console.log(e,'---------');
                    if (e.data.code == 0) {
                        this.ruleForm = e.data.data;
                        console.log(this.ruleForm,'ruleFormruleFormruleFormruleForm')
                        let detail = e.data.data;
                        self.department = detail.department;
                        if (self.department) {
                            let department = [];
                            for (let i in self.department) {
                                department.push(self.department[i].value.toString());
                            }
                            detail.department = department;
                        }

                        self.position = detail.position;
                        if (detail.position) {
                            let position = [];
                            for (let i in detail.position) {
                                position.push(detail.position[i].value.toString());
                            }
                            detail.position = position;
                        }

                        self.ruleForm = Object.assign(self.ruleForm, detail);
                        self.attrGroups = e.data.data.detail.attr_groups;
                        self.goods_warehouse = e.data.data.detail.goods_warehouse;

                        self.defaultServiceChecked = !!parseInt(self.ruleForm.is_default_services);

                        self.$emit('goods-success', self.ruleForm);

                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.cardLoading = false;
                    console.log(e);
                });
            },
            // 标签页
            handleClick(tab, event) {
                this.$emit('change-tabs', tab.name);
                if (tab.name == "third") {
                    this.getMembers();
                }
            },

            // 批量设置
            batchAttr(key) {
                let self = this;
                if (self.batch[key] && self.batch[key] >= 0 || key === 'no') {
                    self.ruleForm.attr.forEach(function (item, index) {
                        // 批量设置会员价
                        // 判断字符串是否出现过，并返回位置
                        if (key.indexOf('level') !== -1) {
                            item['member_price'][key] = self.batch[key];
                        } else {
                            item[key] = self.batch[key];
                        }
                    });
                }
            },
            destroyCat(value, index) {
                let self = this;
                self.ruleForm.cats.splice(self.ruleForm.cats.indexOf(value), 1)
                self.cats.splice(index, 1)
            },
            destroyCat_2(value, index) {
                let self = this;
                self.ruleForm.mchCats.splice(self.ruleForm.mchCats.indexOf(value), 1)
                self.mchCats.splice(index, 1)
            },
            // 商品视频
            videoUrl(e) {
                if (e.length) {
                    this.ruleForm.videos = e[0].url;
                }
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function (item, index) {
                        if (self.ruleForm.pic_url.length >= 9) {
                            return;
                        }
                        self.ruleForm.images.push({
                            id: item.id,
                            images: item.url
                        });
                    });
                }
            },

            updatePicUrl(e, params) {
                this.ruleForm.images[params.currentIndex].id = e[0].id;
                this.ruleForm.images[params.currentIndex].pic_url = e[0].url;
            },

            selectForm(data) {
                this.ruleForm.form = data;
                this.ruleForm.form_id = data ? data.id : -1;
            },

            change(e){
                this.$forceUpdate();
            }
        }
    });
</script>
