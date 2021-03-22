<style>
    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
        margin-bottom: 20px;
    }

    .open-img .el-dialog {
        margin-top: 0 !important;
    }

    .click-img {
        width: 100%;
    }

    .el-input-group__append {
        background-color: #fff
    }
</style>
<div id="app" v-cloak>
    <el-card class="box-card" v-loading="cardLoading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <span v-if="is_audit == 1" style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/mch/mall/mch/edit'})">入驻审核</span>
                    <span v-if="is_audit == 0" style="color: #409EFF;cursor: pointer"
                          @click="$navigate({r:'plugin/mch/mall/mch/index'})">商户列表</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item v-if="is_audit == 1">审核</el-breadcrumb-item>
                <el-breadcrumb-item v-if="is_audit == 0">添加商户</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="160px" size="small">
                 <el-tabs v-model="activeName">
                    <el-tab-pane label="基本信息" name="basic">
                        <el-form-item label="小程序用户" prop="user_id">
                            <el-input style="display: none;" v-model="ruleForm.user_id"></el-input>
                            <el-input disabled v-model="nickname">
                                <template slot="append">
                                    <el-button @click="getUsers" type="primary">选择</el-button>
                                </template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="商户账号" prop="username">
                            <el-input  v-model="ruleForm.username"></el-input>
                        </el-form-item>
                        <el-form-item v-if="!ruleForm.admin_id" label="商户密码" prop="password">
                            <el-input type="password" v-model="ruleForm.password"></el-input>
                        </el-form-item>
                        <el-form-item label="联系人" prop="realname">
                            <el-input v-model="ruleForm.realname"></el-input>
                        </el-form-item>
                        <el-form-item label="联系电话" prop="mobile">
                            <el-input v-model="ruleForm.mobile"></el-input>
                        </el-form-item>
                        <el-form-item label="微信号" prop="wechat">
                            <el-input v-model="ruleForm.wechat"></el-input>
                        </el-form-item>
                        <el-form-item label="所售类目" prop="mch_common_cat_id">
                            <el-select v-model="ruleForm.mch_common_cat_id" placeholder="请选择">
                                <el-option
                                        v-for="item in commonCats"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="是否开业" prop="status">
                            <el-switch
                                    v-model="ruleForm.status"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="好店推荐" prop="is_recommend">
                            <el-switch
                                    v-model="ruleForm.is_recommend"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="提现手续费" prop="transfer_rate">
                            <label slot="label">服务费
                                <el-tooltip class="item" effect="dark"
                                            content="0表示不设置服务费"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.transfer_rate">
                                <template slot="append">%</template>
                            </el-input>
                            <div>
                                <span class="text-danger">服务费额外从提现中扣除</span><br>
                                例如：<span style="color: #F56C6C;font-size: 12px">10%</span>的提现服务费：<br>
                                提现<span style="color: #F56C6C;font-size: 12px">100</span>元，扣除服务费<span
                                        style="color: #F56C6C;font-size: 12px">10</span>元，
                                实际到手<span style="color: #F56C6C;font-size: 12px">90</span>元
                            </div>
                        </el-form-item>
                        <el-form-item label="抵扣券额外扣取比例" prop="transfer_rate">
                            <label slot="label">抵扣券扣取比例
                                <el-tooltip class="item" effect="dark"
                                            content="0表示不设置"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.integral_fee_rate">
                                <template slot="append">%</template>
                            </el-input>
                            <div>
                                <span class="text-danger">使用抵扣券支付需额外支付的抵扣券数额</span><br>
                                例如：设置<span style="color: #F56C6C;font-size: 12px">10%</span><br>
                                使用抵扣券抵扣<span style="color: #F56C6C;font-size: 12px">100</span>元时，需要额外收取<span
                                        style="color: #F56C6C;font-size: 12px">10</span>的抵扣券，
                                最终需要<span style="color: #F56C6C;font-size: 12px">110</span>的抵扣券
                            </div>
                        </el-form-item>
                        <el-form-item label="排序" prop="sort">
                            <label slot="label">排序
                                <el-tooltip class="item" effect="dark"
                                            content="升序，数字越小排的越靠前"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </label>
                            <el-input type="number" v-model.number="ruleForm.sort"></el-input>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane label="店铺信息" name="store">
                        <el-form-item label="店铺名称" prop="name">
                            <el-input v-model="ruleForm.name"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺Logo" prop="logo">
                            <com-attachment :multiple="false" :max="1" v-model="ruleForm.logo">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:240 * 240"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px' :src="ruleForm.logo">
                            </com-image>
                        </el-form-item>
                        <el-form-item label="店铺背景图" prop="bg_pic_url">
                            <com-attachment :multiple="false" :max="1" @selected="picUrl">
                                <el-tooltip class="item"
                                            effect="dark"
                                            content="建议尺寸:750 * 200"
                                            placement="top">
                                    <el-button size="mini">选择文件</el-button>
                                </el-tooltip>
                            </com-attachment>
                            <com-image mode="aspectFill" width='80px' height='80px'
                                       :src="ruleForm.bg_pic_url && ruleForm.bg_pic_url.length ? ruleForm.bg_pic_url[0].pic_url : ''">
                            </com-image>
                        </el-form-item>
                        <el-form-item label="省市区" prop="district">
                            <el-cascader
                                    :options="district"
                                    :props="props"
                                    v-model="ruleForm.district">
                            </el-cascader>
                        </el-form-item>
                        <el-form-item label="店铺地址" prop="address">
                            <el-input v-model="ruleForm.address"></el-input>
                        </el-form-item>
                        <el-form-item label="客服电话" prop="service_mobile">
                            <el-input v-model="ruleForm.service_mobile"></el-input>
                        </el-form-item>
                    </el-tab-pane>
                    <el-tab-pane v-if="is_audit == 1 && ruleForm.form_data.length > 0"  label="自定义审核资料" name="customize_review">
                        <template v-for="item in ruleForm.form_data">
                            <el-form-item v-if="item.key == 'text'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'textarea'" :label="item.label">
                                <el-input disabled v-model="item.value" type="textarea"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'date'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'time'" :label="item.label">
                                <el-input disabled v-model="item.value" type="text"></el-input>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'radio'" :label="item.label">
                                <el-radio disabled v-model="item.value" :label="item.value">{{item.value}}
                                </el-radio>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'checkbox'" :label="item.label">
                                <el-checkbox disabled v-for="cItem in item.value" :checked='true'>{{cItem}}
                                </el-checkbox>
                            </el-form-item>
                            <el-form-item v-if="item.key == 'img_upload'" :label="item.label">
                                <template v-if="item.img_type == 2 || Array.isArray(item.value)">
                                    <div flex="dir:left">
                                        <div v-for="imgItem in item.value" @click="dialogImgShow(imgItem)">
                                            <com-image style="margin-right: 10px;"
                                                       mode="aspectFill"
                                                       width="100px"
                                                       height='100px'
                                                       :src="imgItem">
                                            </com-image>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div @click="dialogImgShow(item.value)">
                                        <com-image mode="aspectFill"
                                                   width="100px"
                                                   height='100px'
                                                   :src="item.value">
                                        </com-image>
                                    </div>
                                </template>
                            </el-form-item>
                        </template>
                    </el-tab-pane>
                    <el-tab-pane label="审核信息" name="review_info">

                        <el-tabs v-model="review_status_activeName" type="border-card">
                            <el-tab-pane label="审核状态" name="tab_review_status">
                                <el-form-item label="商户编号" prop="acqMerId">
                                    <div v-if="review.acqMerId != ''">{{review.acqMerId}}</div>
                                    <div style="color:#cc3311" v-else>未申请</div>
                                </el-form-item>
                                <el-form-item label="商户注册名称" prop="merchantName">
                                    <div>{{ruleForm.name}}</div>
                                </el-form-item>
                                <el-form-item label="进件商户" prop="register_type">
                                    <el-radio v-model="review.register_type" label="separate_account">分账商户</el-radio>
                                    <el-radio v-model="review.register_type" label="common">标准商户</el-radio>
                                </el-form-item>
                                <el-form-item v-if="review.register_type != 'separate_account'" label="是否收单" prop="acceptOrder">
                                    <el-switch
                                            v-model="review.acceptOrder"
                                            active-value="1"
                                            inactive-value="0">
                                    </el-switch>
                                </el-form-item>
                                <el-form-item v-if="review.register_type != 'separate_account'" label="是否开户" prop="openAccount">
                                    <el-switch
                                            v-model="review.openAccount"
                                            active-value="1"
                                            inactive-value="0">
                                    </el-switch>
                                </el-form-item>
                                <template v-if="is_audit == 1 && is_review == 0">
                                    <el-form-item label="审核状态" prop="review_status">
                                        <el-tag v-if="ruleForm.review_status == 0" type="info">待审核</el-tag>
                                        <el-tag v-if="ruleForm.review_status == 1" type="success">审核通过</el-tag>
                                        <el-tag v-if="ruleForm.review_status == 2" type="danger">审核不通过</el-tag>
                                    </el-form-item>
                                    <el-form-item label="审核结果" prop="review_remark">
                                        {{ruleForm.review_remark}}
                                    </el-form-item>
                                </template>
                                <template v-if="is_review == 1">
                                    <el-form-item label="审核状态" prop="review_status">
                                        <el-radio v-model="ruleForm.review_status" label="1">审核通过</el-radio>
                                        <el-radio v-model="ruleForm.review_status" label="2">审核不通过</el-radio>
                                    </el-form-item>
                                    <el-form-item label="审核结果">
                                        <el-input v-model="ruleForm.review_remark" type="textarea" :row="5"></el-input>
                                    </el-form-item>
                                </template>
                            </el-tab-pane name="review_info">
                            <el-tab-pane v-if="review.register_type != 'separate_account'" label="营业执照" name="tab_review_license">
                                <el-form-item label="商户类型" prop="paper_merchantType">
                                    <el-radio v-model="review.paper_merchantType" label="1">个体</el-radio>
                                    <el-radio v-model="review.paper_merchantType" label="2">企业</el-radio>
                                    <el-radio v-model="review.paper_merchantType" label="3">个人</el-radio>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="营业执照号" prop="paper_businessLicenseCode">
                                    <el-input v-model="review.paper_businessLicenseCode"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="商户经营名称" prop="paper_businessLicenseName">
                                    <el-input v-model="review.paper_businessLicenseName"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="营业执照照片" prop="paper_businessLicensePhoto">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_businessLicensePhoto">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_businessLicensePhoto">
                                    </com-image>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="营业执照有效期（截止）" prop="paper_businessLicenseTo">
                                    <el-date-picker
                                            v-model="review.paper_businessLicenseTo"
                                            type="datetime"
                                            placeholder="选择日期">
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 || (review.acceptOrder==0 && (review.paper_merchantType==1 || review.paper_merchantType==2))" label="商户简称" prop="paper_shortName">
                                    <el-input v-model="review.paper_shortName"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="营业执照类型" prop="paper_isCc">
                                    <el-radio v-model="review.paper_isCc" label="1">已3证合一</el-radio>
                                    <el-radio v-model="review.paper_isCc" label="0">未3证合一</el-radio>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="法人姓名" prop="paper_lawyerName">
                                    <el-input v-model="review.paper_lawyerName"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="经营范围" prop="paper_businessScope">
                                    <el-input :rows="2" type="textarea" v-model="review.paper_businessScope"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_merchantType==1 || review.paper_merchantType==2" label="注册地址" prop="paper_registerAddress">
                                    <el-input v-model="review.paper_registerAddress"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_isCc==0 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="组织机构代码" prop="paper_organizationCode">
                                    <el-input v-model="review.paper_organizationCode"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.paper_isCc==0 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="组织机构代码照片" prop="paper_organizationCodePhoto">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_organizationCodePhoto">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_organizationCodePhoto">
                                    </com-image>
                                </el-form-item>

                                <el-form-item v-if="review.paper_isCc==0 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="组织机构代码有效期（起始）" prop="paper_organizationCodeFrom">
                                    <el-date-picker
                                            v-model="review.paper_organizationCodeFrom"
                                            type="datetime"
                                            placeholder="选择日期">
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item v-if="review.paper_isCc==0 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="组织机构代码有效期（截止）" prop="paper_organizationCodeTo">
                                    <el-date-picker
                                            v-model="review.paper_organizationCodeTo"
                                            type="datetime"
                                            placeholder="选择日期">
                                    </el-date-picker>
                                </el-form-item>

                            </el-tab-pane>
                            <el-tab-pane label="位置及环境" name="tab_review_place">
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="经营地址" prop="paper_businessAddress">
                                    <el-input v-model="review.paper_businessAddress"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="省/市" prop="paper_province_city">
                                    <el-cascader
                                            v-model="paperProvinceCityValue"
                                            :options="paperProvinceCityOptions"
                                            @change="paperProvinceCityChange"></el-cascader>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="MCC 码" prop="paper_mcc">
                                    <el-cascader
                                            v-model="paperMerchantMccValue"
                                            :options="paperMerchantMccOptions"
                                            @change="paperMerchantMccChange"></el-cascader>
                                </el-form-item>
                                <el-form-item label="银联快捷简称" prop="paper_unionShortName">
                                    <el-input v-model="review.paper_unionShortName"></el-input>
                                </el-form-item>

                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="门店门头照" prop="paper_storeHeadPhoto">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_storeHeadPhoto">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_storeHeadPhoto">
                                    </com-image>
                                </el-form-item>

                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && (review.paper_merchantType==1 || review.paper_merchantType==2)" label="门店内景照" prop="paper_storeHallPhoto">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_storeHallPhoto">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_storeHallPhoto">
                                    </com-image>
                                </el-form-item>

                            </el-tab-pane>
                            <el-tab-pane label="法人资料"  name="tab_review_lawyer">
                                <el-form-item label="证件类型" prop="paper_lawyerCertType">
                                    <el-select v-model="review.paper_lawyerCertType" placeholder="请选择">
                                        <el-option
                                                v-for="item in lawyerCertTypes"
                                                :key="item.value"
                                                :label="item.label"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="证件号码" prop="paper_lawyerCertNo">
                                    <el-input v-model="review.paper_lawyerCertNo"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="证件正面照" prop="paper_lawyerCertPhotoFront">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_lawyerCertPhotoFront">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_lawyerCertPhotoFront">
                                    </com-image>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="证件背面照" prop="paper_lawyerCertPhotoBack">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_lawyerCertPhotoBack">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_lawyerCertPhotoBack">
                                    </com-image>
                                </el-form-item>
                                <el-form-item label="证件人姓名" prop="paper_certificateName">
                                    <el-input v-model="review.paper_certificateName"></el-input>
                                </el-form-item>
                                <el-form-item v-if="(review.acceptOrder==1 && review.openAccount==1) || (review.acceptOrder==1 && review.paper_merchantType!=3)" label="证件有效期（截止）" prop="paper_certificateTo">
                                    <el-date-picker
                                            v-model="review.paper_certificateTo"
                                            type="datetime"
                                            placeholder="选择日期">
                                    </el-date-picker>
                                </el-form-item>

                            </el-tab-pane>
                            <el-tab-pane label="联系人" name="tab_review_contact">
                                <el-form-item v-if="review.acceptOrder==1" label="联系人姓名" prop="paper_contactPerson">
                                    <el-input v-model="review.paper_contactPerson"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.openAccount==1" label="联系人手机号码" prop="paper_contactPhone">
                                    <el-input v-model="review.paper_contactPhone"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1" label="客服电话" prop="paper_serviceTel">
                                    <el-input v-model="review.paper_serviceTel"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1" label="邮箱地址" prop="paper_email">
                                    <el-input v-model="review.paper_email"></el-input>
                                </el-form-item>
                            </el-tab-pane>
                            <el-tab-pane v-if="review.register_type != 'separate_account'" label="对公账户" name="tab_review_account">
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && review.paper_merchantType==2" label="账户名" prop="paper_licenceAccount">
                                    <el-input v-model="review.paper_licenceAccount"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && review.paper_merchantType==2" label="账号" prop="paper_licenceAccountNo">
                                    <el-input v-model="review.paper_licenceAccountNo"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && review.paper_merchantType==2" label="开户银行" prop="paper_licenceOpenBank">
                                    <el-input v-model="review.paper_licenceOpenBank"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && review.paper_merchantType==2" label="开户支行" prop="paper_licenceOpenSubBank">
                                    <el-input v-model="review.paper_licenceOpenSubBank"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.acceptOrder==1 && review.openAccount==1 && review.paper_merchantType==2" label="证明文件（照片）" prop="paper_openingLicenseAccountPhoto">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_openingLicenseAccountPhoto">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_openingLicenseAccountPhoto">
                                    </com-image>
                                </el-form-item>

                            </el-tab-pane>
                            <el-tab-pane label="结算账号" name="tab_review_settle">
                                <el-form-item v-if="review.openAccount==1 || review.paper_merchantType==1 || review.paper_merchantType==2" label="结算账户类型" prop="paper_settleAccountType">
                                    <el-radio v-if="review.register_type != 'separate_account'" v-model="review.paper_settleAccountType" label="1">对公账户</el-radio>
                                    <el-radio v-model="review.paper_settleAccountType" label="2">法人账户</el-radio>
                                    <el-radio v-if="review.register_type != 'separate_account'" v-model="review.paper_settleAccountType" label="3">授权对公</el-radio>
                                    <el-radio v-if="review.register_type != 'separate_account'" v-model="review.paper_settleAccountType" label="4">授权对私</el-radio>
                                </el-form-item>
                                <el-form-item v-if="review.openAccount==1 || review.paper_merchantType==1 || review.paper_merchantType==2" label="结算账户号" prop="paper_settleAccountNo">
                                    <el-input v-model="review.paper_settleAccountNo"></el-input>
                                </el-form-item>
                                <el-form-item v-if="review.openAccount==1 || review.paper_merchantType==1 || review.paper_merchantType==2" label="结算账户名" prop="paper_settleAccount">
                                    <el-input v-model="review.paper_settleAccount"></el-input>
                                </el-form-item>

                                <el-form-item v-if="review.openAccount==1" label="结算账户类型" prop="paper_settleTarget">
                                    <el-radio v-model="review.paper_settleTarget" label="1">自动提现</el-radio>
                                    <el-radio v-model="review.paper_settleTarget" label="2">手动提现</el-radio>
                                </el-form-item>

                                <el-form-item v-if="review.paper_settleAccountType==3 || review.paper_settleAccountType==4" label="结算账户附件" prop="paper_settleAttachment">
                                    <com-attachment :multiple="false" :max="1" v-model="review.paper_settleAttachment">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    placement="top">
                                            <el-button size="mini">选择文件</el-button>
                                        </el-tooltip>
                                    </com-attachment>
                                    <com-image mode="aspectFill" width='80px' height='80px' :src="review.paper_settleAttachment">
                                    </com-image>
                                </el-form-item>

                                <el-form-item v-if="review.openAccount==1 || review.paper_merchantType==1 || review.paper_merchantType==2" label="开户银行" prop="paper_openBank">
                                    <el-input v-model="review.paper_openBank"></el-input>
                                </el-form-item>

                                <el-form-item v-if="review.paper_settleAccountType==1" label="开户支行" prop="paper_openSubBank">
                                    <el-input v-model="review.paper_openSubBank"></el-input>
                                </el-form-item>

                                <el-form-item v-if="review.paper_settleAccountType==1" label="开户行联行号" prop="paper_openBankCode">
                                    <el-input v-model="review.paper_openBankCode"></el-input>
                                </el-form-item>


                            </el-tab-pane>
                            <el-tab-pane label="业务信息" name="tab_review_business">
                                <el-form-item v-if="review.register_type != 'separate_account'" label="业务代码" prop="paper_businessCode">
                                    <el-input v-model="review.paper_businessCode"></el-input>
                                </el-form-item>

                                <el-form-item label="结算周期" prop="paper_settleCycle">
                                    <el-radio v-model="review.paper_settleCycle" label="D+0">D+0</el-radio>
                                    <el-radio v-model="review.paper_settleCycle" label="D+1">D+1</el-radio>
                                    <el-radio v-model="review.paper_settleCycle" label="T+0">T+0</el-radio>
                                    <el-radio v-model="review.paper_settleCycle" label="T+1">T+1</el-radio>
                                </el-form-item>


                                <el-form-item label="结算方式" prop="paper_stage_feeType">
                                    <el-radio v-model="review.paper_stage_feeType" label="0">按比例</el-radio>
                                    <el-radio v-model="review.paper_stage_feeType" label="1">单笔收费</el-radio>
                                </el-form-item>

                                <el-form-item v-if="review.paper_stage_feeType==0" label="比例值" prop="paper_stage_feeRate">
                                    <el-input placeholder="请输入内容" v-model="review.paper_stage_feeRate">
                                        <template slot="append">%</template>
                                    </el-input>
                                </el-form-item>

                                <el-form-item v-else label="单笔收费" prop="paper_stage_feePer">
                                    <el-input placeholder="请输入内容" v-model="review.paper_stage_feePer">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-form-item>

                            </el-tab-pane>
                        </el-tabs>

                    </el-tab-pane>
                </el-tabs>

            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存
        </el-button>
        <el-dialog title="用户列表" :visible.sync="dialogUsersVisible">
            <template>
                <el-input clearable style="width: 260px;margin-bottom: 20px;" size="small" :disabled="ruleForm.is_all"
                          v-model="ruleForm.keyword"
                          @keyup.enter.native="getUsers"
                          @clear="getUsers"
                          placeholder="输入用户ID、昵称搜索">
                    <template slot="append">
                        <el-button size="small" @click="getUsers">搜索</el-button>
                    </template>
                </el-input>
                <el-table
                        v-loading="tableLoading"
                        :data="users"
                        tooltip-effect="dark"
                        style="width: 100%">
                    <el-table-column
                            prop="id"
                            label="ID"
                            width="80">
                    </el-table-column>
                    <el-table-column
                            label="头像">
                        <template slot-scope="scope">
                            <com-image mode="aspectFill" :src="scope.row.avatar"></com-image>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="nickname"
                            label="昵称">
                        <template slot-scope="scope">
                            <div flex="dir:left">
                                <img src="statics/img/mall/ali.png" v-if="scope.row.userInfo.platform == 'aliapp'" alt="">
                                <img src="statics/img/mall/wx.png" v-else-if="scope.row.userInfo.platform == 'wxapp'" alt="">
                                <img src="statics/img/mall/toutiao.png" v-else-if="scope.row.userInfo.platform == 'ttapp'" alt="">
                                <img src="statics/img/mall/baidu.png" v-else-if="scope.row.userInfo.platform == 'bdapp'" alt="">
                                <span style="margin-left: 10px;">{{scope.row.nickname}}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="操作"
                            width="120">
                        <template slot-scope="scope">
                            <el-button @click="selectUser(scope.row)" type="primary" plain size="mini">添加</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </el-dialog>
        <el-dialog :visible.sync="dialogImg" width="45%" class="open-img">
            <img :src="click_img" class="click-img" alt="">
        </el-dialog>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                ruleForm: {
                    user_id: 0,
                    status: '0',
                    is_recommend: '0',
                    realname: '',
                    review_status: '',
                    review_remark: '',
                    wechat: '',
                    mobile: '',
                    address: '',
                    mch_common_cat_id: '',
                    name: '',
                    logo: '',
                    bg_pic_url: [],
                    transfer_rate: 0,
                    account_money: 0,
                    sort: 100,
                    longitude: '',
                    latitude: '',
                    service_mobile: '',
                    district: [],
                    form_data: [],
                    integral_fee_rate:0
                },
                rules: {
                    user_id: [
                        {required: true, message: '小程序用户', trigger: 'change'},
                    ],username: [
                        {required: true, message: '商户账号', trigger: 'change'},
                    ],
                    password: [
                        {required: true, message: '商户密码', trigger: 'change'},
                    ],
                    realname: [
                        {required: true, message: '联系人', trigger: 'change'},
                    ],
                    mobile: [
                        {required: true, message: '联系人电话', trigger: 'change'},
                    ],
                    name: [
                        {required: true, message: '店铺名称', trigger: 'change'},
                    ],
                    logo: [
                        {required: true, message: '店铺Logo', trigger: 'change'},
                    ],
                    bg_pic_url: [
                        {required: true, message: '店铺背景图', trigger: 'change'},
                    ],
                    address: [
                        {required: true, message: '店铺详细地址', trigger: 'change'},
                    ],
                    district: [
                        {required: true, message: '店铺省市区', trigger: 'change'},
                    ],
                    transfer_rate: [
                        {required: true, message: '店铺手续费', trigger: 'change'},
                    ],
                    sort: [
                        {required: true, message: '店铺排序', trigger: 'change'},
                    ],
                    service_mobile: [
                        {required: true, message: '客服电话', trigger: 'change'},
                    ],
                    mch_common_cat_id: [
                        {required: true, message: '所售类目', trigger: 'change'},
                    ],
                    is_recommend: [
                        {required: true, message: '好店推荐', trigger: 'change'},
                    ],
                    status: [
                        {required: true, message: '是否开业', trigger: 'change'},
                    ],
                    review_status: [
                        {required: true, message: '审核状态', trigger: 'change'},
                    ],
                },
                review: {
                    acqMerId: '',
                    acceptOrder: '0',
                    openAccount: '0',
                    register_type: 'separate_account',
                    paper_merchantType: 0,
                    paper_businessLicenseCode: '',
                    paper_businessLicenseName: '',
                    paper_businessLicensePhoto: '',
                    paper_businessLicenseTo: '',
                    paper_shortName: '',
                    paper_isCc: 0,
                    paper_lawyerName: '',
                    paper_businessScope: '',
                    paper_registerAddress: '',
                    paper_organizationCode: '',
                    paper_organizationCodePhoto: '',
                    paper_organizationCodeFrom: '',
                    paper_organizationCodeTo: '',
                    paper_businessAddress: '',
                    paper_province: '',
                    paper_city: '',
                    paper_mcc: '',
                    paper_unionShortName: '',
                    paper_storeHeadPhoto: '',
                    paper_storeHallPhoto: '',
                    paper_lawyerCertType: 0,
                    paper_lawyerCertNo: '',
                    paper_lawyerCertPhotoFront: '',
                    paper_lawyerCertPhotoBack: '',
                    paper_certificateName: '',
                    paper_certificateTo: '',
                    paper_contactPerson: '',
                    paper_contactPhone: '',
                    paper_serviceTel: '',
                    paper_email: '',
                    paper_licenceAccount: '',
                    paper_licenceAccountNo: '',
                    paper_licenceOpenBank: '',
                    paper_licenceOpenSubBank: '',
                    paper_openingLicenseAccountPhoto: '',
                    paper_settleAccountType: 0,
                    paper_settleAccountNo: '',
                    paper_settleAccount: '',
                    paper_settleTarget: 0,
                    paper_settleAttachment: '',
                    paper_openBank: '',
                    paper_openSubBank: '',
                    paper_openBankCode: '',
                    paper_businessCode: '',
                    paper_settleCycle: '',
                    paper_stage_feeRate: 0,
                    paper_stage_feePer: 0,
                    paper_stage_amountFrom: 0,
                    paper_stage_feeType:0,
                },
                lawyerCertTypes: [
                    {value: 0, label: '身份证'},
                    {value: 1, label: '居住证'},
                    {value: 2, label: '签证'},
                    {value: 3, label: '护照'},
                    {value: 4, label: '户口本'},
                    {value: 5, label: '军人证'},
                    {value: 6, label: '团员证'},
                    {value: 7, label: '党员证'},
                    {value: 8, label: '港澳通行证'},
                    {value: 9, label: '台胞证'},
                    {value: 11, label: '临时身份证'},
                    {value: 12, label: '回乡证'},
                    {value: 13, label: '营业执照'},
                    {value: 14, label: '组织机构代码证'},
                    {value: 15, label: '驾驶证'},
                    {value: 99, label: '其他'}
                ],
                btnLoading: false,
                tableLoading: false,
                cardLoading: false,
                commonCats: [],
                district: [],
                props: {
                    value: 'id',
                    label: 'name',
                    children: 'list'
                },
                dialogUsersVisible: false,
                users: [],
                nickname: '',//用户展示的用户名
                is_review: 0,
                is_audit: 0,//审核状态是否显示,添加商户时不显示
                navigateToUrl: 'plugin/mch/mall/mch/index',
                isNewEdit: 1,
                dialogImg: false,
                click_img: '',
                activeName: 'basic',
                review_status_activeName: 'tab_review_status',

                paperProvinceCityValue:[],
                paperProvinceCityOptions: [],

                paperMerchantMccValue: [],
                paperMerchantMccOptions: []
            };
        },
        watch: {
            'review.register_type'(val, oldVal){
                if(val == "separate_account"){ //分账商户
                    this.review.acceptOrder = '0';
                    this.review.openAccount = '1';
                    this.review.paper_merchantType = '3';
                    this.review.paper_settleAccountType = '2';
                    this.review.paper_businessCode = 'WITHDRAW_TO_SETTMENT_DEBIT';
                }
            }
        },
        methods: {
            getDetail() {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        this.review = e.data.data.review;
                        this.ruleForm = e.data.data.detail;
                        this.nickname = this.ruleForm.user.nickname;

                        this.paperProvinceCityValue = [];
                        this.paperProvinceCityValue.push(this.review.paper_province);
                        this.paperProvinceCityValue.push(this.review.paper_city);

                        this.paperMerchantMccValue = [];
                        this.paperMerchantMccValue.push(this.review['paper_mcc_obj'].type);
                        this.paperMerchantMccValue.push(this.review['paper_mcc_obj'].code);

                    }
                }).catch(e => {
                });
            },
            store(formName) {
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/mch/mall/mch/edit'
                            },
                            method: 'post',
                            data: {
                                form        : self.ruleForm,
                                is_review   : self.is_review,
                                review_info : self.review
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: self.navigateToUrl
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
            // 获取类目列表
            getCommonCatList() {
                request({
                    params: {
                        r: 'plugin/mch/mall/common-cat/all-list',
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.commonCats = e.data.data.list;
                    }
                }).catch(e => {
                });
            },
            addShareTitle() {
                let self = this;
                if (self.shareTitle) {
                    if (self.ruleForm.share_title.indexOf(self.shareTitle) === -1) {
                        self.ruleForm.share_title.push(self.shareTitle);
                        self.shareTitle = '';
                    }
                }
            },
            deleteShareTitle(index) {
                this.ruleForm.share_title.splice(index);
            },
            itemChecked(type) {
                if (type === 1) {
                    this.ruleForm.sponsor_num = this.isSponsorNum ? -1 : 0
                } else if (type === 2) {
                    this.ruleForm.help_num = this.isHelpNum ? -1 : 0
                } else if (type === 3) {
                    this.ruleForm.sponsor_count = this.isSponsorCount ? -1 : 0
                } else {
                }
            },
            getUsers() {
                let self = this;
                self.btnLoading = true;
                if (!self.ruleForm.is_all) {
                    self.dialogUsersVisible = true;
                    self.tableLoading = true;
                }
                request({
                    params: {
                        r: 'plugin/mch/mall/mch/search-user',
                        keyword: self.ruleForm.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.btnLoading = false;
                    self.tableLoading = false;
                    if (e.data.code == 0) {
                        self.users = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.$message.error(e.data.msg);
                    self.btnLoading = false;
                    self.tableLoading = false;
                });
            },
            selectUser(row) {
                this.ruleForm.user_id = row.id;
                this.nickname = row.nickname;
                this.dialogUsersVisible = false;
            },
            // 店铺背景图
            picUrl(e) {
                if (e.length) {
                    let self = this;
                    self.ruleForm.bg_pic_url = [];
                    e.forEach(function (item, index) {
                        self.ruleForm.bg_pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 获取省市区列表
            getDistrict() {
                request({
                    params: {
                        r: 'district/index',
                        level: 3
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        this.district = e.data.data.district;
                    }
                }).catch(e => {
                });
            },
            dialogImgShow(imgUrl) {
                this.dialogImg = true;
                this.click_img = imgUrl;
            },
            paperProvinceCityChange(value){
                this.review.paper_province = value[0];
                this.review.paper_city = value[1];

            },
            getPaperProvinceCityOptions(){
                if(this.paperProvinceCityOptions.length <= 0){
                    request({
                        params: {
                            r: 'efps-region/index'
                        },
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.paperProvinceCityOptions = e.data.data.regions;
                        }
                    }).catch(e => {

                    });
                }
            },
            paperMerchantMccChange(value){
                this.review.paper_mcc = value[1];
            },
            getPaperMerchantMccOptions(){
                if(this.paperMerchantMccOptions.length <= 0){
                    request({
                        params: {
                            r: 'efps-merchant-mcc/index'
                        },
                    }).then(e => {
                        if (e.data.code == 0) {
                            this.paperMerchantMccOptions = e.data.data.mcc;
                        }
                    }).catch(e => {

                    });
                }
            }
        },
        mounted: function () {
            if (getQuery('is_review')) {
                this.is_review = getQuery('is_review');
                this.navigateToUrl = 'plugin/mch/mall/mch/review';
            }
            if (getQuery('id')) {
                this.getDetail();
                this.isNewEdit = 0;
            }
            this.is_audit = getQuery('id') ? 1 : 0;
            this.getCommonCatList();
            this.getDistrict();
            this.getPaperProvinceCityOptions();
            this.getPaperMerchantMccOptions();
        }
    });
</script>
