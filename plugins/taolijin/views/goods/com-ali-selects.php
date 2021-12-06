<template id="com-ali-selects">
    <div class="com-ali-selects">
        <el-dialog width="80%" title="选择商品" :visible.sync="dialogVisible" :close-on-click-modal="false" @close="close">
            <el-tabs v-model="activeName">
                <el-tab-pane label="淘宝联盟" name="ali">
                    <el-row type="flex" v-loading="loading">
                        <el-col :span="2" class="page-l">
                            <div><el-link :disabled="search.page <= 1" @click="prev" :underline="false" class="page-btn"><span class="el-icon-arrow-left"></span></el-link></div>
                        </el-col>
                        <el-col :span="20">
                            <el-row v-for="datas in list" :gutter="20" style="margin-bottom:20px;">
                                <el-col v-for="data in datas" :span="4">
                                    <el-card shadow="hover" :body-style="{ padding: '0px' }">
                                        <img :src="data.pict_url" class="image">
                                        <div style="padding:14px;">
                                            <div class="title-n2">
                                                <a :href="data.click_url" target="_blank" style="text-decoration: none;color:#333">{{data.title}}</a>
                                            </div>
                                        </div>
                                        <div class="info" style="margin-bottom:14px;">
                                            <div style="">
                                                <span style="color:darkred">{{data.reserve_price}}元</span>
                                                <div>一口价</div>
                                            </div>
                                            <div style="border-left:1px solid #eee;">
                                                <span style="color:royalblue">{{data.commission_rate}}%</span>
                                                <div>佣金比</div>
                                            </div>
                                            <div style="border-left:1px solid #eee;">
                                                <el-button @click="confirm(data)" type="primary" icon="el-icon-check" circle></el-button>
                                            </div>
                                        </div>
                                    </el-card>
                                </el-col>
                            </el-row>
                        </el-col>
                        <el-col :span="2"  class="page-r">
                            <div><el-link :disabled="nextDis" @click="next" :underline="false" class="page-btn"><span class="el-icon-arrow-right"></span></el-link></div>
                        </el-col>
                    </el-row>
                    <div style="text-align: center;color:gray;">{{search.page}}</div>
                </el-tab-pane>
            </el-tabs>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('com-ali-selects', {
        template: '#com-ali-selects',
        props: {
            visible: Boolean
        },
        data() {
            return {
                autoplay: false,
                loop:false,
                loading: false,
                dialogVisible: false,
                activeName: "ali",
                list: [],
                nextDis: false,
                search: {
                    page:1
                }
            };
        },
        created() {

        },
        watch: {
            visible(val) {
                this.dialogVisible = val;
                if(val){
                    this.loadData();
                }
            }
        },
        methods: {
            prev(){
                this.search.page -= 1;
                if(this.search.page <= 0){
                    this.search.page = 1;
                }
                this.loadData();
            },
            next(){
                this.search.page += 1;
                this.loadData();
            },
            loadData(){
                this.loading = true;
                let params = {
                    r: 'plugin/taolijin/mall/ali/search',
                    ali_id: getQuery("ali_id")
                };
                params = Object.assign(params, this.search);
                let that = this;
                request({
                    params: params,
                    method: 'get',
                }).then(e => {
                    that.loading = false;
                    if (e.data.code == 0) {
                        var rows = [], list = [];
                        for(var i=0; i < e.data.data.list.length; i++){
                            rows.push(e.data.data.list[i]);
                            if(rows.length >= 6){
                                list.push(rows);
                                rows = [];
                            }
                        }
                        if(rows.length > 0){
                            list.push(rows);
                        }
                        that.list = list;
                    } else {
                        that.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            close(){
                this.$emit('close');
            },
            confirm(data){
                this.$emit('confirm', 'ali', data);
            }
        }
    });
</script>

<style>
.page-l,.page-r{text-align: center}
.page-l > div, .page-r > div{width:100%;height:100%;display: table;}
.page-btn{
    display: table-cell;vertical-align: middle;
    font-size: 60px;
}
.image {
    width: 100%;
    display: block;
}
.title-n2{
    word-break: break-all;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}
.info{color:gray;display:flex;}
.info > div{flex:1;text-align: center}
</style>
