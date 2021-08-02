<style type="text/css">
.commission-rule{margin:5px 0px;border-left:1px solid #ddd;border-top:1px solid #ddd;}
.commission-rule td{padding:10px 10px;text-align:center;border-right:1px solid #ddd;border-bottom:1px solid #ddd;}
.commission-rule td:last-child{width:230px;}
</style>
<template id="com-commission-hotel-rule-edit">
    <el-card class="box-card" style="width:600px;">
        <div slot="header" class="clearfix">
            <el-radio-group @change="changeCommissionType" v-model="commission_type">
                <el-radio :label="1">按百分比</el-radio>
                <el-radio :label="2">按固定值</el-radio>
            </el-radio-group>
        </div>
        <div style="display: flex;justify-content: space-evenly;margin: 10px 0 " v-for="(item,index) in level_list" :key="index">
            <span style="display: block;width: 20%">{{item.name}}</span>
            <el-input type="number" placeholder="请输入内容" @input = "levelparam" v-model="item.commisson_value">
                <template slot="append">{{commission_type == 1 ? '%' : '元'}}</template>
            </el-input>
        </div>
    </el-card>
</template>
<script>
    Vue.component('com-commission-hotel-rule-edit', {
        template: '#com-commission-hotel-rule-edit',
        props: ['ctype', 'chains','commission_hotel_value'],
        computed: {},
        watch: {
            chains: {
                handler(rows, oldval) {
                },
                immediate: true
            },
            ctype: {
                handler(val, oldval) {
                    this.commission_type = val;
                },
                immediate: true
            }
        },
        data() {
            return {
                commission_type: 1,
                commission_hotel_value: '',
                level_list:[
                    {
                        name:'普通会员',
                        commisson_value:''
                    },
                    {
                        name:'分公司',
                        commisson_value:''
                    },
                    {
                        name:'店主',
                        commisson_value:''
                    },
                    {
                        name:'合伙人',
                        commisson_value:''
                    }
                ]
            };
        },
        created() {
            console.log(this.chains)
            for (let i = 0; i < this.chains.length; i++) {
                if (this.chains[i].role_type == 'user') {
                    this.level_list[i].name = '普通会员';
                    this.level_list[i].commisson_value = this.chains[i].commisson_value;
                } else if (this.chains[i].role_type == 'branch_office') {
                    this.level_list[i].name = '分公司';
                    this.level_list[i].commisson_value = this.chains[i].commisson_value;
                } else if (this.chains[i].role_type == 'store') {
                    this.level_list[i].name = '店主';
                    this.level_list[i].commisson_value = this.chains[i].commisson_value;
                } else if (this.chains[i].role_type == 'partner') {
                    this.level_list[i].name = '合伙人';
                    this.level_list[i].commisson_value = this.chains[i].commisson_value;
                }
            }
        },
        methods: {
            baseRowClassName({row, rowIndex}){
                return 'base-row-class';
            },
            changeCommissionType(){
                this.$emit('update', {
                    type: this.commission_type,
                });
            },
            number () {
                this.$emit('number', {
                    value: this.commission_hotel_value,
                });
            },
            levelparam () {
                this.$emit('levelparam',this.level_list);
            },
            changeCommissionChain(row, chain){
                var chainList = this.transferChainGroupToList();

                this.$emit('update', {
                    chains: chainList
                });
            },
            transferChainGroupToList(){
                var group =  this.groupData;
                var list = [], m, n, i, item, chain;

                return list;
            },
        }
    });
</script>