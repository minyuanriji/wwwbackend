
<template id="diy-take-score">
    <div>
        <div class="diy-component-preview">
            <div class="diy-take-score" :style="cStyle">
                <img v-if="data.pic_url" :src="data.pic_url" :style="cImgStyle"/>
                <div class="button-box" :style="cButtonStyle" v-if="data.isButton">
                    <div>
                        <span>按钮位置</span>
                        <div class="button-box-mask"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item label="背景图片">
                    <com-attachment v-model="data.pic_url" :multiple="false" :max="1">
                        <el-tooltip class="item" effect="dark" content="建议尺寸:750 * 287" placement="top">
                            <el-button size="mini">选择图片</el-button>
                        </el-tooltip>
                    </com-attachment>
                    <div class="customize-share-title">
                        <com-image mode="aspectFill" width='80px' height='80px' :src="data.pic_url"></com-image>
                    </div>
                </el-form-item>
                <el-form-item label="图片宽度" >
                    <el-input style="max-width: 180px" v-model="data.img_width" size="small" type="number" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="图片高度" >
                    <el-input style="max-width: 180px" v-model="data.img_height" size="small" type="number" autocomplete="off"></el-input>
                </el-form-item>
                <!--
                <el-form-item label="间隙" >
                    <el-switch v-model="data.isPadding"></el-switch>
                </el-form-item>
                -->
                <el-form-item label="按钮" >
                    <el-switch v-model="data.isButton"></el-switch>
                </el-form-item>
                <el-form-item label=" " v-if="data.isButton">
                    <div style="display:flex;align-items: center">
                        <span>左：</span>
                        <el-input v-model="data.button_x" style="width: 80px" size="mini" type="number" autocomplete="off"></el-input>
                        <span style="margin-left:10px;">上：</span>
                        <el-input v-model="data.button_y" style="width: 80px" size="mini" type="number" autocomplete="off"></el-input>
                        <span style="margin-left:10px;">宽：</span>
                        <el-input v-model="data.button_w" style="width: 80px" size="mini" type="number" autocomplete="off"></el-input>
                        <span style="margin-left:10px;">高：</span>
                        <el-input v-model="data.button_h" style="width: 80px" size="mini" type="number" autocomplete="off"></el-input>
                    </div>
                </el-form-item>
                <el-form-item label="领取活动">
                    <el-button type="primary" size="small" @click="dialogVisible = true">选择活动</el-button>
                    <template v-if="chooseData" >
                        <el-card class="box-card" style="margin-top:10px;">
                            <div slot="header" class="clearfix">
                                <span>{{chooseData.name}}</span>
                                <el-button @click="deleteOn" style="float: right; padding: 3px 0" type="text">删除</el-button>
                            </div>
                            <div>
                                <span v-if="chooseData.score_give_settings.is_permanent == 1">
                                    永久积分，赠送数量{{chooseData.number}}
                                </span>
                                    <span v-else>
                                    限时积分，赠送数量{{chooseData.number}}，
                                    送{{chooseData.score_give_settings.period}}个月，
                                    有效期{{chooseData.score_give_settings.expire}}天
                                </span>
                            </div>
                        </el-card>
                    </template>
                </el-form-item>
                <el-form-item label="跳转地址" >
                    <el-input size="small" class="link-page" v-model="data.link.name" :disabled="true" style="width:300px;">
                        <template slot="append">
                            <com-pick-link @selected="selectLink">
                                <el-button>选择链接</el-button>
                            </com-pick-link>
                        </template>
                    </el-input>
                </el-form-item>
            </el-form>
        </div>

        <el-dialog title="选择活动" :visible.sync="dialogVisible" @open="dialogOpen">
            <el-table :data="list" v-loading="loading" style="margin-bottom: 20px">
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="name" label="名称" width="200"></el-table-column>
                <el-table-column label="赠送配置">
                    <template slot-scope="scope">
                        <span v-if="scope.row.enable_score != 1">已关闭</span>
                        <span v-else>
                            <span v-if="scope.row.score_give_settings.is_permanent == 1">
                                永久积分，赠送数量{{scope.row.number}}
                            </span>
                            <span v-else>
                                限时积分，赠送数量{{scope.row.number}}，
                                送{{scope.row.score_give_settings.period}}个月，
                                有效期{{scope.row.score_give_settings.expire}}天
                            </span>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="开始时间">
                    <template slot-scope="scope">
                        {{scope.row.start_at}}
                    </template>
                </el-table-column>
                <el-table-column prop="scope" width="150" label="结束时间">
                    <template slot-scope="scope">
                        {{scope.row.end_at}}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="100px">
                    <template slot-scope="scope">
                        <el-button @click="chooseIt(scope.row)" size="small">选择</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: center">
                <el-pagination
                        v-if="pagination"
                        style="display: inline-block"
                        background
                        @current-change="pageChange"
                        layout="prev, pager, next, jumper"
                        :page-size.sync="pagination.pageSize"
                        :total="cTotalCount">
                </el-pagination>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-take-score', {
        template: '#diy-take-score',
        props: {
            value: Object
        },
        data() {
            return {
                chooseData: '',
                data: {
                    send_data: '',
                    isPadding: false,
                    pic_url: '',
                    img_width: 0,
                    img_height: 0,
                    isButton: true,
                    button_x:0,
                    button_y:0,
                    button_w: 200,
                    button_h: 90,
                    link: {name: ''}
                },
                dialogVisible: false,
                page: 1,
                list: null,
                loading: false,
                pagination: null,
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
                if (!this.data.link) {
                    this.data.link = {name: ''};
                }
                this.chooseData = this.data.send_data ? this.data.send_data : '';
            }
        },
        computed: {
            cTotalCount(){
                return this.pagination ? parseInt(this.pagination.totalCount) : 0;
            },
            cButtonStyle(){
                let left, top, width, height, style = {};
                left   = !isNaN(this.data.button_x) && this.data.button_x > 0 ? this.data.button_x : 0;
                top    = !isNaN(this.data.button_y) && this.data.button_y > 0 ? this.data.button_y : 0;
                width  = !isNaN(this.data.button_w) && this.data.button_w > 0 ? this.data.button_w : 0;
                height = !isNaN(this.data.button_h) && this.data.button_h > 0 ? this.data.button_h : 0;
                style['left']       = left + "px";
                style['top']        = top + "px";
                style['width']      = width + "px";
                style['height']     = height + "px";
                style['lineHeight'] = height + "px";
                return style;
            },
            cStyle(){
                let style = {paddingTop: 0, paddingBottom: 0};
                if(this.data.isPadding){
                    style.paddingTop = "20px";
                    style.paddingBottom = "20px";
                }
                return style;
            },
            cImgStyle() {
                let style = {width:"90%"}, img_width = this.data.img_width;
                if(img_width && !isNaN(img_width) && parseInt(img_width) > 0){
                    style['width'] = (img_width > 750 ? 750 : img_width) + 'px';
                }
                if(this.data.img_height && !isNaN(this.data.img_height) && parseInt(this.data.img_height) > 0){
                    style['height'] = this.data.img_height + 'px';
                }
                return style;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            selectLink(e) {
                this.data.link = e[0];
            },
            dialogOpen() {
                if (this.list) {
                    return;
                }
                this.loadData();
            },
            loadData() {
                this.loading = true;
                this.$request({
                    params: {
                        r: 'plugin/integral_card/admin/from-free/list',
                        page: this.page,
                        status: 'enable'
                    }
                }).then(response => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        this.list = response.data.data.list;
                        this.pagination = response.data.data.pagination;
                    }
                }).catch(e => {
                });
            },
            pageChange(page) {
                this.page = page;
                this.loadData();
            },
            chooseIt(item) {
                this.chooseData = {
                    id: item.id,
                    name: item.name,
                    score_give_settings: item.score_give_settings,
                    number: parseFloat(item.number)
                };
                this.data.send_data = this.chooseData;
                this.dialogVisible = false;
            },
            deleteOn() {
                this.chooseData = '';
                this.data.send_data = '';
            }
        }
    });
</script>
<style>
    .diy-take-score{position:relative;display:flex;justify-content: center;width:100%;min-height:100px;background:white;padding-top:20px;padding-bottom:20px;}
    .diy-take-score img{width:90%;}
    .button-box{border:1px dotted #fff;position:absolute;left:0;top:0;width:200px;height:70px;line-height:70px;}
    .button-box > div{width:100%;height:100%;position:relative;text-align:center;}
    .button-box > div span{position:absolute;left:0;top:0;z-index:9;width:100%;height:100%;color:white;}
    .button-box-mask{z-index:8;left:0;top:0;width:100%;height:100%;position:absolute;background: rgba(0, 144, 127, 0.5)}
</style>