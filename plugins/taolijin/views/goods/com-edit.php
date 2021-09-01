<?php
echo $this->render("com-ali-selects");
Yii::$app->loadComponentView('com-rich-text');
?>

<template id="com-edit">
    <div class="com-edit">

        <com-ali-selects @confirm="aliDialogConfirm" @close="aliDialogClose" :visible="aliDialogVisisble"></com-ali-selects>

        <el-dialog v-if="!aliDialogVisisble" title="编辑商品" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-form :rules="rules" ref="formData" label-width="20%" :model="formData" size="small">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基本信息" name="first">
                        <el-form-item :label="formData.ali_type == 'ali' ? '淘宝联盟' : '京东联盟'">
                            <el-card class="box-card">
                                <el-row type="flex">
                                    <el-col :span="4">
                                        <com-image mode="aspectFill" width='80px' height='80px' :src="formData.ali_other_data.image"></com-image>
                                    </el-col>
                                    <el-col :span="14">{{formData.ali_other_data.title}}</el-col>
                                    <el-col :span="6" >
                                        <div style="display:flex;display:-webkit-flex;justify-content:center;align-items:center;height:100%;">
                                            <el-link @click="aliDialogOpen" type="primary" icon="el-icon-edit" style="font-size:16px;">
                                                设置
                                            </el-link>
                                        </div>
                                    </el-col>
                                </el-row>
                                <div class="ali-info">
                                    <span>ID：{{formData.ali_unique_id}}</span>
                                    <span style="color:darkred">一口价：{{formData.price}}</span>
                                    <span style="color:royalblue">佣金比：{{formData.ali_rate}}%</span>
                                </div>
                                <div style="font-size:11px;line-height:25px;background:#f1f1f1;padding:5px 5px;margin-top:5px;border:1px solid #eee;">{{formData.ali_url}}</div>
                            </el-card>
                        </el-form-item>

                        <el-form-item label="商品名称" prop="name">
                            <el-input v-model="formData.name"></el-input>
                        </el-form-item>
                        <el-form-item label="封面" prop="cover_pic">
                            <com-attachment :multiple="false" :max="1" v-model="formData.cover_pic">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:240 * 240"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px' :src="formData.cover_pic"></com-image>
                        </el-form-item>
                        <el-form-item prop="pic_url">
                            <template slot="label">
                                <span>商品轮播图(多张)</span>
                                <el-tooltip effect="dark" placement="top" content="第一张图片为封面图">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <div class="pic-url-remark">
                                第一张图片为缩略图,其它图片为轮播图,建议像素750*750,可拖拽使其改变顺序，最多支持上传9张
                            </div>
                            <div flex="dir:left">
                                <template v-if="formData.pic_url.length">
                                    <draggable v-model="formData.pic_url" flex="dif:left">
                                        <div v-for="(item,index) in formData.pic_url" :key="index" style="margin-right: 20px;position: relative;cursor: move;">
                                            <com-attachment @selected="updatePicUrl" :params="{'currentIndex': index}">
                                                <com-image mode="aspectFill" width="100px" height='100px' :src="item.pic_url">
                                                </com-image>
                                            </com-attachment>
                                            <el-button class="del-btn" size="mini" type="danger" icon="el-icon-close" circle @click="delPic(index)"></el-button>
                                        </div>
                                    </draggable>
                                </template>
                                <template v-if="formData.pic_url.length < 5">
                                    <com-attachment style="margin-bottom: 10px;" :multiple="true" :max="9" @selected="picUrl">
                                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 750" placement="top">
                                            <div flex="main:center cross:center" class="add-image-btn">
                                                + 添加图片
                                            </div>
                                        </el-tooltip>
                                    </com-attachment>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item label="商品视频" prop="video_url">
                            <el-input v-model="formData.video_url" placeholder="请输入视频原地址或选择上传视频">
                                <template slot="append">
                                    <com-attachment :multiple="false" :max="1" @selected="videoUrl" type="video">
                                        <el-tooltip class="item" effect="dark" content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB" placement="top">
                                            <el-button size="mini">添加视频</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                </template>
                            </el-input>
                            <el-link class="box-grow-0" type="primary" style="font-size:12px" v-if='formData.video_url' :underline="false" target="_blank" :href="formData.video_url">视频链接
                            </el-link>
                        </el-form-item>
                        <el-form-item label="红包最大抵扣" prop="deduct_integral">
                            <el-input v-model="formData.deduct_integral" style="width:60%;">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="礼金面额" prop="gift_price">
                            <el-input v-model="formData.gift_price" style="width:60%;">
                                <template slot="append">元</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="单位" prop="unit">
                            <el-input v-model="formData.unit" style="width:60%;"></el-input>
                        </el-form-item>
                        <el-form-item label="上架状态" prop="status">
                            <el-switch active-value="1" inactive-value="0" v-model="formData.status"></el-switch>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="详情" name="second">
                        <com-rich-text v-model="formData.detail"></com-rich-text>
                    </el-tab-pane>
                </el-tabs>
            </el-form>
            <div slot="footer" style="text-align:center;padding-bottom:50px">
                <el-button :loading="btnLoading"  @click="save" type="primary" style="margin-right:15px;">保存提交</el-button>
                <el-button type="default" @click="close" style="margin-left:15px;">取消关闭</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    function initFormData(){
        return {
            id:0,
            name: "",
            deduct_integral: 0.00,
            price: 0.00,
            cover_pic: "",
            pic_url: [],
            video_url: "",
            unit: "件",
            status: "1",

            gift_price: 0.00,
            ali_type: '',
            ali_unique_id: '',
            ali_rate: 0.00,
            ali_other_data: {image:'', title: ''},
            ali_url: ''
        };
    }

    Vue.component('com-edit', {
        template: '#com-edit',
        props: {
            goodsInfo: Object,
            visible: Boolean
        },
        data() {
            return {
                aliDialogVisisble: false,
                dialogTitle: "编辑商品",
                activeName: "first",
                dialogVisible: false,
                formData: initFormData(),
                rules: {
                    name: [
                        {required: true, message: '商品名称不能为空', trigger: 'change'},
                    ],
                    deduct_integral: [
                        {required: true, message: '抵扣红包不能为空', trigger: 'change'},
                    ],
                    gift_price: [
                        {required: true, message: '礼金面额不能为空', trigger: 'change'},
                    ],
                    cover_pic: [
                        {required: true, message: '封面不能为空', trigger: 'change'}
                    ],
                    unit: [
                        {required: true, message: '单位不能为空', trigger: 'change'}
                    ],
                },
                btnLoading: false
            };
        },
        created() {

        },

        watch: {
            visible(val, oldVal){
                this.dialogVisible = val;
            },
            goodsInfo(val, oldVal){
                if(typeof val['id'] == "undefined" || parseInt(val.id) <= 0){
                    this.formData = initFormData();
                }else{
                    this.formData = Object.assign(this.formData, val);
                }
                if(this.formData.ali_unique_id == ''){
                    this.aliDialogOpen();
                }
            },
        },
        methods: {
            //选择联盟商品
            aliDialogOpen(){
                this.aliDialogVisisble = true;
            },
            //确认选择联盟商品
            aliDialogConfirm(tab, data){
                this.aliDialogVisisble = false;
                this.formData.ali_type = tab;
                if(tab == "ali"){ //淘宝联盟
                    this.formData.ali_url = data.click_url;
                    this.formData.ali_unique_id = data.item_id;
                    this.formData.ali_rate = data.commission_rate;
                    this.formData.ali_other_data = {
                        image: data.pict_url,
                        title: data.title,
                        origin: data
                    }
                    this.formData.name      = data.title;
                    this.formData.cover_pic = data.pict_url;
                    this.formData.price     = data.reserve_price;
                    this.formData.pic_url   = [];
                    var small_images = data.small_images['string'];
                    for(var i=0; i < small_images.length; i++){
                        this.formData.pic_url.push({pic_url: small_images[i]});
                    }
                }

            },
            //关闭联盟商品选择框
            aliDialogClose(){
                this.aliDialogVisisble = false;
            },
            // 商品轮播图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    e.forEach(function(item, index) {
                        if (self.formData.pic_url.length >= 5) {
                            return;
                        }
                        self.formData.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            delPic(index) {
                this.formData.pic_url.splice(index, 1)
            },
            updatePicUrl(e, params) {
                this.formData.pic_url[params.currentIndex].id = e[0].id;
                this.formData.pic_url[params.currentIndex].pic_url = e[0].url;
            },
            // 商品视频
            videoUrl(e) {
                if (e.length) {
                    this.formData.video_url = e[0].url;
                }
            },
            save(){
                this.btnLoading = true;
                let that = this;
                this.$refs['formData'].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'plugin/taolijin/mall/goods/edit'
                            },
                            method: 'post',
                            data: that.formData
                        }).then(e => {
                            that.btnLoading = false;
                            if (e.data.code == 0) {
                                that.$message.success(e.data.msg);
                                that.update();
                            } else {
                                that.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            that.$message.error(e.data.msg);
                            that.btnLoading = false;
                        });
                    }
                });
            },
            close(){
                this.$emit('close');
            },
            update(){
                this.$emit('update');
            }
        }
    });
</script>
<style>
.ali-info{
    margin-top:10px;
    height:100%;
    display: -webkit-flex; /* Safari */
    display: flex;
    justify-content:flex-start
}
.ali-info > span:first-child{border-left:none;padding-left:0px;}
.ali-info > span{border-left:1px solid #ddd;padding-left:10px;padding-right:10px;}
.com-edit .add-image-btn {
    width: 100px;
    height: 100px;
    color: #419EFB;
    border: 1px solid #e2e2e2;
    cursor: pointer;
}
.com-edit .pic-url-remark {
    font-size: 13px;
    color: #c9c9c9;
    margin-bottom: 12px;
}
.com-edit .add-image-btn {
    width: 100px;
    height: 100px;
    color: #419EFB;
    border: 1px solid #e2e2e2;
    cursor: pointer;
}
.com-edit .del-btn {
    position: absolute;
    right: -8px;
    top: -8px;
    padding: 4px 4px;
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
