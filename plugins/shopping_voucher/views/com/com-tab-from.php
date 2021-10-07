<template id="com-tab-from">
    <div class="com-tab-from">
        <div style="background: white;margin-bottom: 20px;padding-left:20px;">
            <el-tabs v-model="activeName" @tab-click="tabClick">
                <el-tab-pane v-for="item in tabList" :label="item.label" :name="item.key"></el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>

<script>
    Vue.component('com-tab-from', {
        template: '#com-tab-from',
        props: {
            current: String
        },
        data() {
            return {
                activeName: "",
                tabList: [
                    {key:"store", label: "商户列表", r:"plugin/shopping_voucher/mall/from-store/list"},
                    {key: "1688_distribution", label: "1688分销商品", r:""}
                ]
            };
        },
        created() {
            this.activeName = this.current;
        },
        watch: {
            current(val, oldVal){
                this.activeName = val;
            }
        },
        methods: {
            tabClick(tab){
                if(tab.name == this.current)
                    return;
                for(var i=0; i < this.tabList.length; i++){
                    if(this.tabList[i].key == tab.name){
                        navigateTo({
                            r: this.tabList[i].r
                        });
                        break;
                    }
                }
            }
        }
    });
</script>
