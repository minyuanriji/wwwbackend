<style type="text/css">
.commission-rule{margin:5px 0px;border-left:1px solid #ddd;border-top:1px solid #ddd;}
.commission-rule td{padding:10px 10px;text-align:center;border-right:1px solid #ddd;border-bottom:1px solid #ddd;}
.commission-rule td:last-child{width:230px;}
</style>
<template id="com-commission-store-rule-edit">
    <el-card class="box-card">
        <div slot="header" class="clearfix">
            <el-radio-group @change="changeCommissionType" v-model="commission_type">
                <el-radio :label="1">按百分比</el-radio>
                <el-radio :label="2">按固定值</el-radio>
            </el-radio-group>
        </div>
        <el-input type="number" placeholder="请输入内容" @input = "number" v-model="commisson_value">
            <template slot="append">{{commission_type == 1 ? '%' : '元'}}</template>
        </el-input>
    </el-card>
</template>
<script>
    Vue.component('com-commission-store-rule-edit', {
        template: '#com-commission-store-rule-edit',
        props: ['ctype', 'chains','commiss_value'],
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
                commisson_value: '',
            };
        },
        created() {
            this.commisson_value = this.commiss_value
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
                    value: this.commisson_value,
                });
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