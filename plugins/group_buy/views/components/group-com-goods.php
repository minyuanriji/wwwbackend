<?php
Yii::$app->loadComponentView('com-rich-text');
Yii::$app->loadComponentView('goods/com-dialog-select');
// Yii::$app->loadComponentView('goods/com-attr');

Yii::$app->loadPluginComponentView('goods/com-attr');

Yii::$app->loadComponentView('goods/com-attr-select');
Yii::$app->loadComponentView('goods/com-add-cat');
Yii::$app->loadComponentView('goods/com-select-goods');
Yii::$app->loadComponentView('goods/com-area-limit');
Yii::$app->loadComponentView('goods/com-preview');
Yii::$app->loadComponentView('goods/com-attr-group');

Yii::$app->loadComponentView('com-goods-form', Yii::$app->BasePath . '/views/components/goods');
Yii::$app->loadComponentView('com-goods-distribution', Yii::$app->BasePath . '/views/components/goods');
Yii::$app->loadComponentView('com-goods-area', Yii::$app->BasePath . '/views/components/goods');
Yii::$app->loadComponentView('com-goods-agent', Yii::$app->BasePath . '/views/components/goods');
?>
<style>
    .mt-24 {
        margin-bottom: 24px;
    }

    .com-goods .el-form-item__label {
        padding: 0 20px 0 0;
    }

    .com-goods .el-dialog__body h3 {
        font-weight: normal;
        color: #999999;
    }

    .com-goods .form-body {
        padding: 10px 20px 20px;
        background-color: #fff;
        margin-bottom: 30px;
    }

    .com-goods .button-item {
        padding: 9px 25px;
        margin-bottom: 10px;
    }

    .com-goods .sortable-chosen {
        /* border: 2px solid #3399ff; */
    }

    .del-btn {
        position: absolute;
        right: -8px;
        top: -8px;
        padding: 4px 4px;
    }

    .com-goods .app-share {
        padding-top: 12px;
        border-top: 1px solid #e2e2e2;
        margin-top: -20px;
    }

    .com-goods .app-share .app-share-bg {
        position: relative;
        width: 310px;
        height: 360px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center
    }

    .com-goods .app-share .app-share-bg .title {
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

    .com-goods .app-share .app-share-bg .pic-image {
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

    .com-goods .add-image-btn {
        width: 100px;
        height: 100px;
        color: #419EFB;
        border: 1px solid #e2e2e2;
        cursor: pointer;
    }

    .com-goods .pic-url-remark {
        font-size: 13px;
        color: #c9c9c9;
        margin-bottom: 12px;
    }

    .com-goods .customize-share-title {
        margin-top: 10px;
        width: 80px;
        height: 80px;
        position: relative;
        cursor: move;
    }

    .com-goods .share-title {
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
<template id="abc">
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" class="com-goods"
                 v-loading="cardLoading">
            <div class='form-body'>
                <el-form :model="cForm" :rules="cRule" ref="ruleForm" label-width="180px" size="small"
                         class="demo-ruleForm">
                    <el-tabs v-model="activeName" @tab-click="handleClick" >
                        <el-tab-pane label="基础设置" name="first" v-if="is_basic == 1">
                            <!-- 选择分类 -->
                            <slot name="before_cats"></slot>
                            <el-card v-if="is_cats == 1" shadow="never" class="mt-24">
                                <div slot="header">
                                    <span>选择分类</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
                                        <el-form-item label="商品分类" prop="cats">
                                            <el-tag style="margin-right: 5px;margin-bottom:5px" v-for="(item,index) in cats"
                                                    :key="index" type="warning" closable disable-transitions
                                                    @close="destroyCat(index)"
                                            >{{item.label}}
                                            </el-tag>
                                            <el-button type="primary" @click="$refs.cats.openDialog()">选择分类</el-button>
                                            <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                            </el-button>
                                            <com-add-cat ref="cats" :new-cats="ruleForm.cats"
                                                         @select="selectCat"></com-add-cat>
                                        </el-form-item>
                                        <!-- mch -->
                                        <el-form-item v-if="is_mch" label="多商户分类" prop="mchCats">
                                            <el-tag style="margin-right: 5px" v-for="(item,index) in mchCats"
                                                    :key="item.value" v-model="ruleForm.mchCats" type="warning" closable
                                                    disable-transitions @close="destroyCat_2(item.value,index)"
                                            >{{item.label}}
                                            </el-tag>
                                            <el-button type="primary" @click="$refs.mchCats.openDialog()">选择分类</el-button>
                                            <el-button type="text" @click="$navigate({r:'mall/cat/edit'}, true)">添加分类
                                            </el-button>
                                            <com-add-cat ref="mchCats" :new-cats="ruleForm.mchCats" :mch_id="mch_id"
                                                         @select="selectMchCat"></com-add-cat>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-card>
    
                            <!-- 基本信息 -->
                            <slot name="before_info"></slot>
                            <el-card shadow="never" class="mt-24">
                                <div slot="header">
                                    <span>基本信息</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
                                        <template v-if="is_info == 1">
                                            <el-form-item label="淘宝采集" hidden>
                                                <el-input v-model="copyUrl">
                                                    <template slot="append">
                                                        <el-button @click="copyGoods" :loading="copyLoading">获取
                                                        </el-button>
                                                    </template>
                                                </el-input>
                                            </el-form-item>
                                            <el-form-item prop="number">
                                                <template slot="label">
                                                    <span>商城商品编码</span>
                                                    <el-tooltip effect="dark" placement="top"
                                                                content="只能从商城中获取商品信息，且基本信息与商城商品保持一致">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </template>
                                                <el-input v-model="copyId" type="number" min="0"
                                                          oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                          placeholder="请输入商城商品id">
                                                    <template slot="append">
                                                        <el-button @click="getDetail(copyId)" :loading="copyLoading">获取
                                                        </el-button>
                                                    </template>
                                                </el-input>
                                            </el-form-item>
                                            <el-form-item label="商品名称" prop="name">
                                                <el-input v-model="ruleForm.name"
                                                          maxlength="100"
                                                          show-word-limit
                                                ></el-input>
                                            </el-form-item>
    
                                            <el-form-item label="商品标签">
                                                <el-select v-model="ruleForm.labels" multiple placeholder="请选择标签">
                                                    <el-option
                                                            v-for="item in label_list"
                                                            :key="item.title"
                                                            :label="item.title"
                                                            :value="item.title">
                                                    </el-option>
                                                </el-select>
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
                                                    <template v-if="ruleForm.pic_url.length">
                                                        <draggable v-model="ruleForm.pic_url" flex="dif:left">
                                                            <div v-for="(item,index) in ruleForm.pic_url" :key="index"
                                                                 style="margin-right: 20px;position: relative;cursor: move;">
                                                                <com-attachment @selected="updatePicUrl"
                                                                                :params="{'currentIndex': index}">
                                                                    <com-image mode="aspectFill" width="100px"
                                                                               height='100px' :src="item.pic_url">
                                                                    </com-image>
                                                                </com-attachment>
                                                                <el-button class="del-btn" size="mini" type="danger"
                                                                           icon="el-icon-close" circle
                                                                           @click="delPic(index)"></el-button>
                                                            </div>
                                                        </draggable>
                                                    </template>
                                                    <template v-if="ruleForm.pic_url.length < 9">
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
    
                                            <el-form-item label="商品视频" prop="video_url">
                                                <el-input v-model="ruleForm.video_url" placeholder="请输入视频原地址或选择上传视频">
                                                    <template slot="append">
                                                        <com-attachment :multiple="false" :max="1" @selected="videoUrl"
                                                                        type="video">
                                                            <el-tooltip class="item"
                                                                        effect="dark"
                                                                        content="支持格式mp4;支持编码H.264;视频大小不能超过50 MB"
                                                                        placement="top">
                                                                <el-button size="mini">添加视频</el-button>
                                                            </el-tooltip>
                                                        </com-attachment>
                                                    </template>
                                                </el-input>
                                                <el-link class="box-grow-0" type="primary" style="font-size:12px"
                                                         v-if='ruleForm.video_url' :underline="false" target="_blank"
                                                         :href="ruleForm.video_url">视频链接
                                                </el-link>
                                            </el-form-item>
                                        </template>
                                        <template v-else>
                                            <!-- plugins -->
                                            <el-form-item label="商品信息获取" width="120">
                                                <label slot="label">
                                                    商品信息获取
                                                    <el-tooltip class="item" effect="dark"
                                                                content="只能从商城中获取商品信息，且基本信息与商城商品保持一致" placement="top">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </label>
                                                <div>
                                                    <el-row type="flex">
                                                        <!-- <el-button type="text" size="medium" style="max-width: 100%;"
                                                                   @click="$navigate({r:'mall/goods/edit', id: goods_warehouse.goods_id}, true)"
                                                                   v-if="goods_warehouse.goods_id">
                                                            <com-ellipsis :line="1">
                                                                ({{goods_warehouse.goods_id}}){{goods_warehouse.name}}
                                                            </com-ellipsis>
                                                        </el-button> -->
                                                        <com-select-goods :multiple="false" :url="goodsIndexUrl"
                                                                          @selected="selectGoodsWarehouse">
                                                            <el-button>选择商品</el-button>
                                                        </com-select-goods>
                                                    </el-row>
                                                    <!-- <el-button type="text" @click="$navigate({r:'mall/goods/edit'}, true)">
                                                        商城还未添加商品？点击前往
                                                    </el-button> -->
                                                </div>
                                            </el-form-item>
                                            <el-form-item v-if="formGoodsList.length>0" label="拼团商品">
                                                <!-- <el-input :value="goods_warehouse.name" :disabled="true"></el-input> -->
												
												<div class="table-body">
													<template>
														<el-table
																:data="formGoodsList"
																border
																style="width: 100%">
															<el-table-column
																	fixed
																	type="index" 
																	label="编号"
																	show-overflow-tooltip 
																	width="100">
															</el-table-column>
															<el-table-column
																	prop="name,cover_pic"
																	label="商品名称"
																	width="350">
																	<template slot-scope="scope">
																		<div flex="box:first">
																			<div style="padding-right: 10px;">
																				<com-image mode="aspectFill" :src="scope.row.cover_pic"></com-image>
																			</div>
																			<div flex="cross:top cross:center">
																				{{scope.row.name}}
																			</div>
																		</div>
																	</template>
															</el-table-column>
															<el-table-column
																	fixed="right"
																	label="操作"
																	width="150">
																<template slot-scope="scope">
																	<el-button @click="delGoods(scope.$index)" type="text" size="small">删除</el-button>
																</template>
															</el-table-column>
														</el-table>
														
													</template>
												</div>
                                            </el-form-item>
											
                                            <el-form-item v-show="false">
                                                <template slot="label">
                                                    <span>原价</span>
                                                    <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                                        <i class="el-icon-info"></i>
                                                    </el-tooltip>
                                                </template>
                                                <el-input :value="goods_warehouse.original_price"
                                                          :disabled="true"></el-input>
                                            </el-form-item>
                                            <el-form-item label="规格" v-if="is_attr == 0">
                                                <el-row type="flex">
                                                    <com-ellipsis :line="1">
                                                        <template v-for="item in ruleForm.select_attr_groups">
                                                            <span style="margin: 0 5px;">
                                                            {{item.attr_group_name}}:{{item.attr_name}}
                                                        </span>
                                                        </template>
                                                    </com-ellipsis>
                                                    <com-attr-select :attr-groups="goods_warehouse.attr_groups"
                                                                     v-model="ruleForm.select_attr_groups">
                                                        <el-button>选择</el-button>
                                                    </com-attr-select>
                                                </el-row>
                                            </el-form-item>
                                        </template>
                                        <el-form-item v-if="is_goods == 1" prop="app_share_title" v-show="false">
                                            <label slot="label">
                                                <span>自定义分享标题</span>
                                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为商品名称"
                                                            placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <el-input placeholder="请输入分享标题"
                                                      v-model="ruleForm.app_share_title"></el-input>
                                            <el-button @click="app_share.dialog = true;app_share.type = 'name_bg'"
                                                       type="text">查看图例
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item v-if="is_goods == 1" prop="app_share_pic" v-show="false">
                                            <label slot="label">
                                                <span>自定义分享图片</span>
                                                <el-tooltip class="item" effect="dark" content="分享给好友时，作为分享图片"
                                                            placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </label>
                                            <com-attachment v-model="ruleForm.app_share_pic" :multiple="false" :max="1">
                                                <el-tooltip class="item" effect="dark" content="建议尺寸:420 * 336"
                                                            placement="top">
                                                    <el-button size="mini">选择图片</el-button>
                                                </el-tooltip>
                                            </com-attachment>
                                            <div class="customize-share-title">
                                                <com-image mode="aspectFill" width='80px' height='80px'
                                                           :src="ruleForm.app_share_pic ? ruleForm.app_share_pic : ''"></com-image>
                                                <el-button v-if="ruleForm.app_share_pic" class="del-btn" size="mini"
                                                           type="danger" icon="el-icon-close" circle
                                                           @click="ruleForm.app_share_pic = ''"></el-button>
                                            </div>
                                            <el-button @click="app_share.dialog = true;app_share.type = 'pic_bg'"
                                                       type="text">查看图例
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item v-if="!is_mch && is_goods == 1" label="上架状态" prop="status" v-show="false">
                                            <el-switch @change="statusChange" :active-value="1" :inactive-value="0"
                                                       v-model="ruleForm.status">
                                            </el-switch>
                                        </el-form-item>
                                        <!-- 自定义 -->
                                        <el-dialog :title="app_share['type'] == 'pic_bg' ? `查看自定义分享图片图例`:`查看自定义分享标题图例`"
                                                   :visible.sync="app_share.dialog" width="30%">
                                            <div flex="dir:left main:center" class="app-share">
                                                <div class="app-share-bg"
                                                     :style="{backgroundImage: 'url('+app_share[app_share.type]+')'}"></div>
                                            </div>
                                            <div slot="footer" class="dialog-footer">
                                                <el-button @click="app_share.dialog = false" type="primary">我知道了</el-button>
                                            </div>
                                        </el-dialog>
                                    </el-col>
                                </el-row>
                            </el-card>
    
                            <!-- 价格库存 -->
                            <slot name="before_attr"></slot>
                            <el-card shadow="never" class="mt-24">
                                <div slot="header">
                                    <span>价格库存</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
                                        <template v-if="is_attr == 1">
                                            <el-form-item prop="goods_num">
												<label slot="label">
												    <span>拼团商品总库存</span>
												    <el-tooltip class="item" effect="dark" content="拼团商品总库存与普通商品总库存相互独立"
												                placement="top">
												        <i class="el-icon-info"></i>
												    </el-tooltip>
												</label>
                                                <el-input type="number" min="0"
														oninput="this.value = this.value.replace(/[^0-9\.]/, '');"
														:disabled="moreAttrs"
														v-model="goods_stock">
											    </el-input>
									
                                  </el-form-item>
                                  <el-form-item label="默认规格名" prop="attr_default_name" v-show="false">
                                      <el-input :disabled="ruleForm.use_attr == 1"
                                                v-model="ruleForm.attr_default_name">
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item prop="goods_num" v-show="ruleForm.use_attr==1">
                                      <label slot="label">
                                          <span>商品规格</span>
                                          <el-tooltip class="item" effect="dark" content="如有颜色、尺码等多种规格，请添加商品规格"
                                                      placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </label>
    
                                      <div v-show="ruleForm.use_attr" style="width:130%;margin-top: 24px;">
                                          <com-attr v-model="ruleForm.attr" :attr-groups="attrGroups"
                                                    :extra="cForm.extra ? cForm.extra : {}"></com-attr>
                                      </div>
                                  </el-form-item>
                                  <el-form-item prop="sort" v-show="false">
                                      <template slot="label">
                                          <span>排序</span>
                                          <el-tooltip effect="dark" content="排序值越小排序越靠前" placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </template>
                                      <el-input type="number" placeholder="请输入排序" min="0"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                v-model.number="ruleForm.sort">
                                      </el-input>
                                  </el-form-item>
    
                                  <el-form-item  label="售价" prop="price" v-if="is_price !=3">
                                      <el-input :disabled="true" type="number"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
                                                v-model="ruleForm.price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
    
                                  <el-form-item label="拼团价" prop="group_buy_price" v-if="ruleForm.use_attr==0">
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" :max="ruleForm.price"
                                                v-model="ruleForm.group_buy_price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
    							  
                                  <el-form-item v-if="cForm.extra" v-for="(item, key, index) in cForm.extra"
                                                :key="item.id">
                                      <label slot="label">{{item}}</label>
                                      <el-input v-model="ruleForm[key]"></el-input>
                                  </el-form-item>
                                  <el-form-item v-if="is_show == 1" prop="original_price"  v-show="false">
                                      <template slot="label">
                                          <span>原价</span>
                                          <el-tooltip effect="dark" content="以划线形式显示" placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </template>
    
                                      <el-input type="number" min="0"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');"
                                                v-model="ruleForm.original_price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item v-if="is_show == 1" label="单位" prop="unit">
                                      <el-input v-model="ruleForm.unit"></el-input>
                                  </el-form-item>
                                  <el-form-item v-if="is_show == 1" label="成本价" prop="cost_price">
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
                                                v-model="ruleForm.cost_price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
<!--                                  <el-form-item v-if="is_show == 1 && !is_mch" prop="is_negotiable">-->
<!--                                      <template slot='label'>-->
<!--                                          <span>商品面议</span>-->
<!--                                          <el-tooltip effect="dark" content="如果开启面议，则商品无法在线支付" placement="top">-->
<!--                                              <i class="el-icon-info"></i>-->
<!--                                          </el-tooltip>-->
<!--                                      </template>-->
<!--                                      <el-switch :active-value="1"-->
<!--                                                 :inactive-value="0"-->
<!--                                                 v-model="ruleForm.is_negotiable">-->
<!--                                      </el-switch>-->
<!--                                  </el-form-item>-->
    
    
                                  <el-form-item label="是否显示销量" prop="is_show_sales"  v-show="false">
                                      <el-switch
                                              v-model="ruleForm.is_show_sales"
                                              :active-value="1"
                                              :inactive-value="0">
                                      </el-switch>
                                  </el-form-item>
    
                                  <el-form-item label="是否启用虚拟销量" prop="use_virtual_sales"  v-show="false">
                                      <el-switch
                                              v-model="ruleForm.use_virtual_sales"
                                              :active-value="1"
                                              :inactive-value="0">
                                      </el-switch>
                                  </el-form-item>
    
                                  <el-form-item prop="virtual_sales"  v-show="false">
    
                                      <template slot='label'>
                                          <span>已出售量</span>
                                          <el-tooltip effect="dark" content="前端展示的销量=实际销量+已出售量" placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </template>
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9]/, '')" min="0"
                                                v-model="ruleForm.virtual_sales">
                                          <template slot="append">{{ruleForm.unit}}</template>
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item label="商品货号"  v-show="false">
                                      <el-input :disabled="ruleForm.use_attr == 1 ? true : false"
                                                v-model="ruleForm.goods_no">
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item label="商品重量"  v-show="false">
                                      <el-input oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                :disabled="ruleForm.use_attr == 1 ? true : false"
                                                v-model="ruleForm.goods_weight">
                                          <template slot="append">克</template>
                                      </el-input>
                                  </el-form-item>
                              </template>
                              <template v-else-if="is_price == 1">
                                  <el-form-item label="售价" prop="price">
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
                                                v-model="ruleForm.price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
                              </template>
                              <template v-else>
                                  <el-form-item label="售价" prop="price">
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
                                                v-model="ruleForm.price">
                                          <template slot="append">元</template>
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item prop="sort" v-show="false">
                                      <template slot="label">
                                          <span>排序</span>
                                          <el-tooltip effect="dark" content="排序值越小排序越靠前" placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </template>
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0"
                                                placeholder="请输入排序" v-model.number="ruleForm.sort">
                                      </el-input>
                                  </el-form-item>
                                  <el-form-item prop="virtual_sales">
                                      <template slot='label'>
                                          <span>已出售量</span>
                                          <el-tooltip effect="dark" content="前端展示的销量=实际销量+已出售量" placement="top">
                                              <i class="el-icon-info"></i>
                                          </el-tooltip>
                                      </template>
                                      <el-input type="number"
                                                oninput="this.value = this.value.replace(/[^0-9]/, '');" min="0"
                                                v-model="ruleForm.virtual_sales">
                                          <template slot="append">{{ruleForm.unit}}</template>
                                      </el-input>
                                  </el-form-item>
                              </template>
                          </el-col>
                      </el-row>
                  </el-card>
    			  
    			  <el-card shadow="never" class="mt-24">
    					<div slot="header">
    						<span>拼团信息</span>
    					</div>
    					<el-row>
    						<el-col :xl="12" :lg="16">
    							<template v-if="is_attr == 1">
    								 <el-form-item label="拼团开始时间" prop="goods_num">
    <!-- 			  							 <el-form-item label="拼团开始时间" prop="group_buy_man" v-if="ruleForm.use_attr==0"> -->
    									  <el-date-picker
    										   v-model="valueDay"
    										   type="date"
    										   value-format="yyyy-MM-dd"
    										   placeholder="选择日期">
    										</el-date-picker>
    										<el-time-picker style="margin-left: 20px;"
    											v-model="valueTime"
    											value-format="HH:mm:ss"
    											:picker-options="{
    											  selectableRange: '00:00:00 - 23:59:59'
    											}"
    											placeholder="选择具体时间">
    										</el-time-picker>
    								  </el-form-item>
    								  <el-form-item label="拼团有效时间" prop="goods_num">
    									  <el-input style="width:220px;" type="number"
    												oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
    												v-model="continuedHouse">
    										  <template slot="append">小时</template>
    									  </el-input>
    									  <el-input style="width:220px;margin-left: 20px;" type="number"
    												oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" max="59"
    												v-model="continuedMinute">
    										  <template slot="append">分钟</template>
    									  </el-input>
    									  
    								  </el-form-item>
    								  <!-- 加入校验规则rules显示* -->
    								  <el-form-item label="成团人数" prop="goods_num">
    									  <el-input type="number"
    												oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
    												v-model="groupPeople">
    										  <template slot="append">人</template>
    									  </el-input>
    								  </el-form-item>
										
									  <!-- 高级设置 -->	
									  <el-form-item  label="高级设置" prop="status">
									      <el-switch v-model="senior_setting" :active-value="1" :inactive-value="0"
									                 >
									      </el-switch>
									  </el-form-item>
									  
									  <el-form-item v-if="senior_setting" label="团长福利" prop="goods_num">
										   <el-radio style="display: block;" v-model="sendStatus" label="1">
												   团长送积分
												   <el-input type="number" style="width:200px;margin-left:10px;margin-bottom: 10px;"
														oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0" 
														:disabled="sendStatus==1?false:true" v-model="send_score"
														>
												   </el-input>
												   分
										   </el-radio>
										   <el-radio style="display: block;" v-model="sendStatus" label="2">
												   团长送余额
												   <el-input type="number"	style="width:200px;margin-left:10px;"
														oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="0"
														:disabled="sendStatus==2?false:true" v-model="send_balance"
														>
												   </el-input>
												   元
										   </el-radio>
									  </el-form-item>
									  <!-- 是否开启虚拟成团 -->
									  <el-form-item v-if="senior_setting" label="是否开启虚拟成团" prop="status">
									      <el-switch v-model="is_virtual" :active-value="1" :inactive-value="0"
									                 >
									      </el-switch>
									  </el-form-item>
									  <el-form-item v-if="is_virtual&&senior_setting" label="虚拟成团人数" prop="goods_num">
										  <el-input type="number"
													oninput="this.value = this.value.replace(/[^0-9\.]/, '');" min="2" :max="groupPeople"
													v-model="virtual_people">
											  <template slot="append">人</template>
										  </el-input>
									  </el-form-item>
    							  
    							</template>
    						  
    						  
    			          </el-col>
    			      </el-row>
    			  </el-card>
    			  
    			  
    
                  <!-- 商品服务 -->
                            <slot name="before_goods"></slot>
    
    
                            <!-- 显示设置 todo -->
                            <el-card shadow="never" class="mt-24" v-if="false && is_show == 1">
                                <div slot="header">
                                    <span>显示设置</span>
                                </div>
                            </el-card>
    
                            <!-- 营销设置 -->
                            <slot name="before_marketing"></slot>
                            <el-card shadow="never" class="mt-24" v-if="is_marketing == 1 && !is_mch">
                                <div slot="header">
                                    <span>营销设置1</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
                                        <el-form-item>
                                            <template slot='label'>
                                                <span>积分赠送</span>
                                                <el-tooltip effect="dark" placement="top">
                                                    <div slot="content">用户购物赠送的积分, 如果不填写或填写0，则默认为不赠送积分，
                                                        如果为百分比则为按成交价格的比例计算积分"<br/>
                                                        如: 购买2件，设置10 积分, 不管成交价格是多少， 则购买后获得20积分</br>
                                                        如: 购买2件，设置10%积分, 成交价格2 * 200= 400， 则购买后获得 40 积分（400*10%）
                                                    </div>
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input type="number" min="0"
                                                      oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                      placeholder="请输入赠送积分数量" v-model="ruleForm.give_score">
                                                <template slot="append">
                                                    分
                                                    <el-radio v-model="ruleForm.give_score_type" :label="1">固定值
                                                    </el-radio>
                                                    <el-radio v-model="ruleForm.give_score_type" :label="2">百分比
                                                    </el-radio>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <el-form-item>
                                            <template slot='label'>
                                                <span>积分抵扣</span>
                                                <el-tooltip effect="dark" content="如果设置0，则不支持积分抵扣 如果带%则为按成交价格的比例计算抵扣多少元"
                                                            placement="top">
                                                    <i class="el-icon-info"></i>
                                                </el-tooltip>
                                            </template>
                                            <el-input :disabled="ruleForm.full_forehead_score==1" type="number" min="0"
                                                      oninput="this.value = this.value.replace(/[^0-9\.]/g, '');"
                                                      placeholder="请输最高抵扣金额"
                                                      v-model="ruleForm.forehead_score">
                                                <template slot="prepend">最多抵扣</template>
                                                <template slot="append">
                                                    元
                                                    <el-radio v-model="ruleForm.forehead_score_type" :label="1" :disabled="ruleForm.full_forehead_score==1">固定值
                                                    </el-radio>
                                                    <el-radio v-model="ruleForm.forehead_score_type" :label="2">百分比
                                                    </el-radio>
                                                </template>
                                            </el-input>
                                            <el-checkbox :true-label="1" :false-label="0" :disabled="ruleForm.full_forehead_score==1"
                                                         v-model="ruleForm.accumulative">
                                                允许多件累计抵扣
                                            </el-checkbox>
    
                                            <el-checkbox :true-label="1" :false-label="0" @change="fullForeheadScore"
                                                         v-model="ruleForm.full_forehead_score">
                                                允许全额抵扣
                                            </el-checkbox>
                                        </el-form-item>
                                    </el-col>
                                </el-row>
                            </el-card>
    
                            <!-- 商品详情 -->
                            <slot name="before_detail"></slot>
                            <el-card shadow="never" class="mt-24" v-if="is_detail == 1">
                                <div slot="header">
                                    <span>商品详情</span>
                                </div>
                                <el-row>
                                    <el-col :xl="12" :lg="16">
    
                                        <el-form-item label="商品详情">
                                            <com-rich-text style="width: 750px" v-model="ruleForm.detail"></com-rich-text>
                                        </el-form-item>
    
                                    </el-col>
                                </el-row>
                            </el-card>
                            <slot name="after_detail"></slot>
                        </el-tab-pane>
                        <el-tab-pane label="分销设置"  name="third"  v-if="is_show_distribution">
                            <com-goods-distribution v-model="ruleForm"
                                                    :is_mch="is_mch"
                                                    :goods_type="goods_type"
                                                    :goods_id="goods_id"
                                                    v-if="activeName == 'third'">
                            </com-goods-distribution>
                        </el-tab-pane>
                        <el-tab-pane label="经销设置"  name="fourth"  v-if="is_show_agent">
                            <com-goods-agent v-model="ruleForm"
                                                    :is_mch="is_mch"
                                                    :goods_type="goods_type"
                                                    :goods_id="goods_id"
                                                    v-if="activeName == 'fourth'">
                            </com-goods-agent>
                        </el-tab-pane>
                        <el-tab-pane label="区域设置" name="fifth" v-if="is_show_area">
                            <com-goods-agent v-model="ruleForm"
                                             :is_mch="is_mch"
                                             :goods_type="goods_type"
                                             :goods_id="goods_id"
                                             v-if="activeName == 'fifth'">
                            </com-goods-agent>
                        </el-tab-pane>
                        <slot name="tab_pane"></slot>
                    </el-tabs>
                </el-form>
                <div class="bottom-div" flex="cross:center" v-if="is_save_btn == 1">
                    <el-button class="button-item" :loading="btnLoading" type="primary" size="small"
                               @click="store('ruleForm')">保存
                    </el-button>
                </div>
            </div>
            <com-preview ref="preview" :rule-form="ruleForm" @submit="store('ruleForm')" :preview-info="previewInfo">
                <template slot="preview">
    				<!-- 这个是自定义的插槽 -->
                    <slot name="preview"></slot>
                </template>
                <template slot="preview_end">
                    <slot name="preview_end"></slot>
                </template>
            </com-preview>
    
        </el-card>
</template>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/unpkg/vuedraggable@2.18.1/dist/vuedraggable.umd.min.js"></script>

<script>
    Vue.component('group-com-goods', {
        template: '#abc',
	
        props: {	//接收父组件传值
            // 选择分类  0--不显示 1--显示可编辑(type类型和默认值)
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
            // 显示设置
            is_show: {
                type: Number,
                default: 1
            },
            // 营销设置
            is_marketing: {
                type: Number,
                default: 0
            },
            // 商品详情
            is_detail: {
                type: Number,
                default: 0
            },
            // 分销设置
            is_distribution: {
                type: Number,
                default: 1
            },
            // 会员设置
            is_member: {
                type: Number,
                default: 1
            },

            //todo 仅显示售价（抽奖） 秒杀3显示
            is_price: {
                type: Number,
                default: 0
            },

            // 添加数据接口
            store_url: {
                type: String,
                default: ''
            },
            // 请求数据地址--获取详情
            get_goods_detail_url: {
                type: String,
                default: 'mall/goods/edit'
            },
            // 保存之后返回地址
            referrer: {
                default: 'mall/goods/index'
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

            is_pieces:{
                type: Number,
                default: 0
            }
            ,
            is_freight:{
                type: Number,
                default: 0
            }
            ,
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
                labels: [],
                attr: [],
                cats: [],
                mchCats: [], //多商户系统分类
                cards: [],
                services: [],
                pic_url: [],
                use_attr: 0,
                goods_num: 0,
                status: 0,	//高级设置
                unit: '件',
                virtual_sales: 0,
                cover_pic: '',
                sort: 100,
                accumulative: 0,
                full_forehead_score: 0,
                confine_count: 1,
                confine_order_count: 1,
                forehead: 0,
                forehead_score: 0,
                forehead_score_type: 1,
                freight_id: 0,
                freight: null,
                give_score: 0,
                give_score_type: 1,
                individual_share: 0,
                is_level: 1,
                is_level_alone: 0,
                pieces: 0,
                share_type: 0,
                attr_setting_type: 0,
                video_url: '',
                is_sell_well: 0,
                is_negotiable: 0,
                name: '',
                price: 0,
                group_buy_price:0,
                original_price: 0,
                cost_price: 0,
                detail: '',
                extra: '',
                app_share_title: '', //自定义分享标题,
                app_share_pic: '', //自定义分享图片
                is_default_services: 1,
                member_price: {},
                goods_no: '',
                goods_weight: '',
                select_attr_groups: [], // 已选择的规格
                goodsWarehouse_attrGroups: [], // 商品库商品所有的规格
                share_level_type: 0,
                distributionLevelList: [],
                form: null,
                is_show_sales: 0,
                use_virtual_sales: 0,
                form_id: 0,
                attr_default_name: '',
                is_area_limit: 0,
                area_limit: [{list: []}],
                full_relief_price:0,
                fulfil_price:0,
            };
            let rules = {	//表单校验规则
                cats: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (this.ruleForm.cats instanceof Array && this.ruleForm.cats.length > 0) {
                                callback();
                            }
                            callback('请选择分类');
                        }
                    }
                ],
                mchCats: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (this.ruleForm.mchCats instanceof Array && this.ruleForm.mchCats.length > 0) {
                                callback();
                            }
                            callback('请选择系统分类');
                        }
                    }
                ],
                name: [		//这些应该都是非空判断
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
                area_limit: [
                    {
                        required: true, type: 'array', validator: (rule, value, callback) => {
                            if (value instanceof Array && value[0]['list'].length === 0) {
                                callback('允许购买区域不能为空');
                            }
                            callback();
                        }
                    }
                ],
                pic_url: [
                    {required: true, message: '请上传商品轮播图', trigger: 'change'},
                ],
				
				
				// group_buy_price: [
				//     {
				//         required: true, type: 'number', validator: (rule, value, callback) => {
				//             if (value > this.ruleForm.price) {
				//                 callback();
				//             }
				//             callback('拼团价不可大于售价');
				//         }
				//     }
				// ],
            };
            return {
				goodsIndexUrl : 'plugin/group_buy/mall/goods/index',
				formGoodsList : [],	//选中的商品数组（最多只能选一个）
				moreAttrs:true,		//默认选中多规格商品
				goods_stock : 0,	//拼团商品总库存
				groupGoodsId : '',	//拼团商品id
				valueDay: '',		//添加拼团商品开始日期
				valueTime: '',		//添加拼团具体时间	
				groupStartDate : '',//拼团活动开始时刻
				continuedHouse : '',//拼团小时数
				continuedMinute : '',//拼团分钟数
				continuedTime : '',	//拼团活动持续时间
				groupPeople : '',	//成团所需人数
				
				senior_setting:0,	//默认不开启虚拟成团
				virtual_people: 0, //虚拟成团人数
				is_virtual: 0,  	//是否虚拟成团
				sendStatus : 0,
				send_score:0,	//团长送积分
				send_balance:0,	//团长送余额
				
                goods_id: 0,
                keyword: '',
                goods_type: 'MALL_GOODS',
                label_list: [],
                cardLoading: false,
                btnLoading: false,
                dialogLoading: false,
                activeName: 'first',
                ruleForm: ruleForm,
                // 分销层级
                shareLevel: [],
                // 会员等级
                memberLevel: [],
                rules: rules,
                options: [], // 商品分类列表
                mchOptions: [], //多商户商品编辑时使用
                newOptions: [],
                cats: [], //用于前端已选的分类展示
                mchCats: [], //用于前端已选的分类展示 多商户
                cards: [], // 优惠券
                attrGroups: [], //规格组

                attrGroupName: '',
                attrName: [],
                // 批量设置
                batch: {},
                dialogVisible: false, //分类选择弹框
                mchDialogVisible: false,
                is_vip_card_goods: 0,
                is_svip: false,
                goods_warehouse: {},
                copyUrl: '',
                copyLoading: false,
                defaultServiceChecked: false,
                service: {
                    dialog: false,
                    list: [],
                    services: [], // 商品服务列表
                    loading: false
                },
                freight: {
                    dialog: false,
                    list: [],
                    checked: {},
                    loading: false
                },
                copyId: '',
                app_share: {
                    dialog: false,
                    type: '',
                    bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share.png",
                    name_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-name.png",
                    pic_bg: "<?= \Yii::$app->request->baseUrl?>/statics/img/mall/app-share-pic.png",
                },
                is_show_distribution: 0,
                is_show_agent: 0,
                is_show_area: 0,
                video_type: 1,
                cardDialogVisible: false,
            };
        },
        created() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.goods_id = getQuery('id');
            }
            if (this.is_distribution == 1) {
                this.getPermissions();
            } else {
                this.is_show_distribution = 0
            }
            this.getSvip();
            this.getLabels();
        },
        watch: {
            'ruleForm.detail'(newVal, oldVal) {
                this.cForm.detail = newVal
            },
            'attrGroups'(newVal, oldVal) {
                this.ruleForm.use_attr = newVal.length === 0 ? 0 : 1;
            },
            'ruleForm.is_level'(newVal, oldVal) {
                if (newVal === 0) {
                    this.ruleForm.is_level_alone = 0;
                }
            },
        },
		// 这里有用到计算属性
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
			// 0.0.1 删除选中商品
			delGoods(){
				this.formGoodsList = [];
				this.goods_warehouse = {};
				location.reload();	//刷新当前页
			},
			
			
			
			// 0.1 获取拼团开始时间 2020-08-07 00:00:00
			getGroupStartDate(valueDay,valueTime){
				let self = this;
				self.groupStartDate = valueDay+' '+valueTime;
				console.log(self.groupStartDate);
				// var date = new Date(valueDay);
				// var y = date.getFullYear();
				// var m = date.getMonth() + 1;
				// m = m < 10 ? ('0' + m) : m;
				// var d = date.getDate();
				// d = d < 10 ? ('0' + d) : d;
				// let timeSelf = y + '-' + m + '-' + d;
				// var date1 = new Date(valueTime);
				// var hh = date1.getHours();
				// var minute = date1.getMinutes();
				// minute = minute < 10 ? ('0' + minute) : minute;
				// var seconds =  date1.getSeconds();
				// seconds = seconds < 10 ? ('0' + seconds) : seconds;
				// let timeSelf1 = hh+':'+minute+':'+seconds;
				// console.log(timeSelf+' '+timeSelf1);
				// self.groupStartDate = timeSelf+' '+timeSelf1;
			},
			// 0.2 获取拼团持续分钟数 -''做乘法运算返回0
			getContinuedTime(continuedHouse,continuedMinute){
				let self = this;
				self.continuedTime = continuedHouse*60+continuedMinute*1;
				console.log(self.continuedTime);
			},
			
            getLabels() {
                request({
                    params: {
                        r: 'mall/goods/label',
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.label_list = e.data.data.list;
                    } else {

                    }
                })
            },
            showPreview() {
                this.$refs.preview.previewGoods();
                this.$emit('handle-preview', this.ruleForm);
            },
            selectCat(cats) {
                this.cats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                })
                this.ruleForm.cats = arr;
                this.$refs.ruleForm.validateField('cats');
            },

            selectMchCat(cats) {
                this.mchCats = cats;
                let arr = [];
                cats.map(v => {
                    arr.push(v.value);
                })
                this.ruleForm.mchCats = arr;
                this.$refs.ruleForm.validateField('mchCats');
            },
            getSvip() {
                request({
                    params: {
                        r: 'mall/member-level/vip-card-permission',
                        plugin: this.sign
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.is_svip = true;
                    } else {
                        this.is_svip = false;
                    }
                })
            },
            delPic(index) {
                this.ruleForm.pic_url.splice(index, 1)
            },

            catDialogCancel() {
                let that = this;
                that.mchDialogVisible = false;
                that.dialogVisible = false;
                that.ruleForm.cats = [];
                that.ruleForm.mchCats = [];
                if (that.cats.length > 0) {
                    that.cats.forEach(function (row) {
                        that.ruleForm.cats.push(row.value.toString());
                    })
                }
                if (that.mchCats.length > 0) {
                    that.mchCats.forEach(function (row) {
                        that.ruleForm.mchCats.push(row.value.toString());
                    })
                }
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
                                //self.is_show_distribution = 1;
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

                //单规格拼团价赋值
                if(self.cForm.use_attr==0){
					if(!self.groupGoodsId){
						self.$message.error('请选择拼团商品');
						return false;
					}
                    self.cForm.attr[0].group_buy_price = self.cForm.group_buy_price;
                }

                try {
                    self.cForm.attr.map(item => {
                        if (item.price < 0 || item.price === '') {
                            throw new Error('规格价格不能为空');
                        }
                        if (item.stock < 0 || item.stock === '') {
                            throw new Error('库存不能为空');
                        }
                        if (!item.group_buy_price ||item.group_buy_price < 0 || item.group_buy_price === '') {
                            throw new Error('拼团价不能为空');
                        }
						
						if(self.moreAttrs){
							if (parseFloat(item.group_buy_price)>parseFloat(item.price)) {
								console.log('item.group_buy_price+'+item.group_buy_price)
								console.log('item.price+'+item.price)
								throw new Error('拼团价不能大于售价');
							}
						}
						
                    })
                } catch (error) {
                    self.$message.error(error.message);
                    return;
                }
				
				
                
                self.$refs[formName].validate((valid) => {
                    if (valid) {
						if(!self.groupGoodsId){
							self.$message.error('请选择拼团商品');
							return false;
						}
						if(!self.valueDay || !self.valueTime || (!self.continuedHouse && !self.continuedMinute) || !self.groupPeople){
							self.$message.error('请设置拼团相关信息');
							return false;
						}
						if(parseInt(self.cForm.group_buy_price)>parseInt(self.cForm.price)&&(!self.moreAttrs)){
							self.$message.error('拼团价不能高于售价');
							return false;
						}
						// 获取拼团持续分钟数
						self.getContinuedTime(self.continuedHouse,self.continuedMinute);
						// 转换时间格式-拼接（value-format这个可以定义时间格式）
						self.getGroupStartDate(self.valueDay,self.valueTime);
						
						let dateTime = new Date(self.groupStartDate); //时间对象 continuedTime
						console.log(dateTime)
						let timeStr = dateTime.getTime(); //转换成时间戳  
						console.log(timeStr)
						// 获取当前时间戳
						let nowTime = new Date();
						let nowTimeStr = nowTime.getTime();
						console.log(nowTimeStr);
						console.log(timeStr-nowTimeStr);
						
						if(self.goods_stock==0){
							self.$message.error('总库存不能为0');
							return false;
						}
						
						if(timeStr<nowTimeStr){
							self.$message.error('开团时间不能早于当前时间');
							return false;
						}
						if(self.continuedTime<15){
							self.$message.error('拼团有效时间不能低于15分钟');
							return false;
						}
						if(self.groupPeople<2 || self.groupPeople>10){
							self.$message.error('拼团人数需为2-10人');
							return false;
						}
						if(self.is_virtual==1&&self.virtual_people<2){
							self.$message.error('虚拟成团人数不能少于2人');
							return false;
						}
						
						
						
						let group_buy_goods={
						    "start_at": self.groupStartDate, //开始时间
						    "vaild_time": self.continuedTime, //有效时间分钟数
						    "people": self.groupPeople,   //成团人数
						    "virtual_people": self.virtual_people, //虚拟成团人数
						    "is_virtual": self.is_virtual,  	//是否虚拟成团
						    "goods_id": self.groupGoodsId,   	//商品id
							"goods_stock" : self.goods_stock,	//拼团商品总库存
						};
						// 添加团长送积分/余额字段
						if(self.sendStatus!=0){
							self.sendStatus==1?group_buy_goods['send_score']=self.send_score:group_buy_goods['send_balance']=self.send_balance;
						}
						
                        self.btnLoading = true;
                        if (self.is_svip) {
                            self.cForm.is_vip_card_goods = self.is_vip_card_goods
                        } else {
                            delete self.cForm['is_vip_card_goods']
                        }
						
						
						// 如果是单规格商品
						if(!self.moreAttrs){
							self.$set(self.cForm.attr[0], `stock`, group_buy_goods.goods_stock);
						}
						// console.log(self.cForm)
						// console.log(group_buy_goods)
						// return
                        request({
                            params: {
                                r: this.store_url
                            },
                            method: 'post',
                            data: {
                                form: JSON.stringify(self.cForm),
                                //attrGroups: JSON.stringify(self.attrGroups),
                                group_buy_goods:JSON.stringify(group_buy_goods)
                            }
                        }).then(e => {
                            self.btnLoading = false;
							self.goods_stock = 0;
                            if (e.data.code === 0) {
                                //保存成功
                                self.$message.success(e.data.msg);
                                navigateTo({r: this.referrer,})
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            console.log(e);
                        });
                    } else {
						self.goods_stock = 0; //
                        self.$message.error('请填写必填参数');
                        return false;
                    }
                });
            },
			
			// 1.0 设置获取到的拼团商品信息
			setGroupBuyGoodsData(groupBuyGoods){
				let self = this;
				let timeArr = groupBuyGoods.start_at.trim().split(" ");
				self.valueDay = timeArr[0];
				self.valueTime = timeArr[1];
				// console.log(valueDay+'111'+valueTime);
				self.continuedHouse = parseInt(groupBuyGoods.vaild_time/60);
				self.continuedMinute = groupBuyGoods.vaild_time%60;
				self.groupPeople = groupBuyGoods.people;
				self.is_virtual = groupBuyGoods.is_virtual;
				self.virtual_people = groupBuyGoods.virtual_people;
			},
			
			// 2.0 获取详情数据
            getDetail(id, url = '') {
                console.log(id);
                let self = this;
				// 存一下商品的id
				self.groupGoodsId = id;
                self.cardLoading = true;
                request({
                    params: {
                        r: url ? url : this.get_goods_detail_url,
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {		//状态码判断是否请求成功
                        let detail = e.data.data.detail;
						detail.attr.length>1?self.moreAttrs = true:self.moreAttrs = false;	//是否选中多规格商品
						// console.log('self.moreAttrs:'+self.moreAttrs);
						if(self.formGoodsList.length>0){
							this.goods_warehouse = {};
						}
						// 存一下商品的列表显示信息
						let formGoods = {};
						formGoods['name'] = detail.name;
						formGoods['cover_pic'] = detail.cover_pic;
						let formGoodsArr = [];
						formGoodsArr.push(formGoods);
						self.formGoodsList = formGoodsArr;
					
						if(e.data.data.group_buy_goods){
							let groupBuyGoods = e.data.data.group_buy_goods;
							console.log(groupBuyGoods);		//获取到拼团信息--并设置这些信息
							self.setGroupBuyGoodsData(groupBuyGoods);	
						}
						
                        if (detail['use_attr'] === 0) {		//单规格
                            detail['attr_groups'] = [];
                        }
                        if (detail.is_vip_card_goods) {		//会员商品
                            self.is_vip_card_goods = detail.is_vip_card_goods
                        }
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(self.ruleForm, i, 0);
                            }
                        }
                        self.cats = detail.cats;
                        if (detail.cats) {
                            let cats = [];
                            for (let i in detail.cats) {
                                cats.push(detail.cats[i].value.toString());
                            }
                            detail.cats = cats;
                        }

                        self.mchCats = detail.mchCats;
                        if (detail.mchCats) {
                            let mchCats = [];
                            for (let i in detail.mchCats) {
                                mchCats.push(detail.mchCats[i].value.toString());
                            }
                            detail.mchCats = mchCats;
                        }
						
						if(detail.attr.length>1){
							detail.attr.forEach(function (item, index) {
								item.stock = 0;
							});
						}
                        self.ruleForm = Object.assign(self.ruleForm, detail);
						
						
						
						// 初始化一下规格
                        self.attrGroups = e.data.data.detail.attr_groups;
                        self.goods_warehouse = e.data.data.detail.goods_warehouse;

                        self.defaultServiceChecked = !!parseInt(self.ruleForm.is_default_services);
                        self.$emit('goods-success', self.ruleForm);

                        self.ruleForm.group_buy_price = detail.attr[0].group_buy_price;

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
            // 获取商品服务
            getServices() {
                let self = this;
                this.service.loading = true;
                request({
                    params: {
                        r: 'mall/service/options'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.service.loading = false;
                    if (e.data.code == 0) {
                        self.service.services = e.data.data.list;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 设置默认服务
            defaultService() {
                if (this.defaultServiceChecked) {
                    this.ruleForm.is_default_services = 1;
                    this.ruleForm.services = [];
                } else {
                    this.ruleForm.is_default_services = 0;
                }
            },
            // 获取会员列表
            getMembers() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/member-level/all-member'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.memberLevel = e.data.data.list;
                        let defaultMemberPrice = [];
                        // 以下数据用于默认规格情况下的 会员价设置
                        self.memberLevel.forEach(function (item, index) {
                            let obj = {};
                            obj['id'] = index;
                            obj['name'] = item.name;
                            obj['level'] = 'level' + parseInt(item.level);
                            defaultMemberPrice.push(obj);
                        });
                        self.defaultMemberPrice = defaultMemberPrice;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取运费规则选项
            getFreight() {
                let self = this;
                this.freight.loading = true;
                request({
                    params: {
                        r: 'mall/postage-rules/all-list'
                    },
                    method: 'get',
                    data: {}
                }).then(e => {
                    this.freight.loading = false;
                    if (e.data.code == 0) {
                        self.freight.list = e.data.data.list;
                        // 添加商品时使用默认运费
                        self.freight.list.unshift({
                            id: 0,
                            name: '默认运费',
                            status: 1
                        })
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },

            // 规格组合
            makeAttrGroup(e) {
                let self = this;
                let array = [];
                self.attrGroups.forEach(function (attrGroupItem, attrGroupIndex) {
                    attrGroupItem.attr_list.forEach(function (attrListItem, attrListIndex) {
                        let object = {
                            attr_group_id: attrGroupItem.attr_group_id,
                            attr_group_name: attrGroupItem.attr_group_name,
                            attr_id: attrListItem.attr_id,
                            attr_name: attrListItem.attr_name,
                        };

                        if (!array[attrGroupIndex]) {
                            array[attrGroupIndex] = [];
                        }
                        array[attrGroupIndex].push(object)
                    });
                });

                // 2.属性排列组合
                const res = array.reduce((osResult, options) => {
                    return options.reduce((oResult, option) => {
                        if (!osResult.length) {
                            return oResult.concat(option)
                        } else {
                            return oResult.concat(osResult.map(o => [].concat(o, option)))
                        }
                    }, [])
                }, []);

                // 3.组合结果赋值
                for (let i in res) {
                    const options = Array.isArray(res[i]) ? res[i] : [res[i]];
                    const row = {
                        attr_list: options,
                        stock: 0,
                        price: 0,
                        group_buy_price: 0,
                        no: '',
                        weight: 0,
                        pic_url: '',
                        distributionLevelList: [],
                    };
                    let extra = {};
                    if (this.form && this.form.extra) {
                        extra = JSON.parse(JSON.stringify(this.form.extra));
                        for (let i in extra) {
                            row[i] = 0;
                        }
                    }
                    // 动态绑定多规格会员价
                    let obj = {};
                    self.memberLevel.forEach(function (memberLevelItem, memberLevelIndex) {
                        let key = 'level' + memberLevelItem.level;
                        obj[key] = 0;
                    });
                    row['member_price'] = obj;
                    // 3-1.已设置数据的优先使用原数据
                    if (self.ruleForm.attr.length) {
                        for (let j in self.ruleForm.attr) {
                            const oldOptions = [];
                            for (let k in self.ruleForm.attr[j].attr_list) {
                                oldOptions.push(self.ruleForm.attr[j].attr_list[k].attr_name)
                            }
                            const newOptions = [];
                            for (let k in options) {
                                newOptions.push(options[k].attr_name)
                            }
                            if (oldOptions.toString() === newOptions.toString()) {
                                row['price'] = self.ruleForm.attr[j].price;
                                row['group_buy_price'] = self.ruleForm.attr[j].group_buy_price;
                                row['stock'] = self.ruleForm.attr[j].stock;
                                row['no'] = self.ruleForm.attr[j].no;
                                row['weight'] = self.ruleForm.attr[j].weight;
                                row['pic_url'] = self.ruleForm.attr[j].pic_url;
                                break
                            }
                        }
                    }
                    res[i] = row;
                }
                self.ruleForm.attr = res;
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
                    this.ruleForm.video_url = e[0].url;
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
                        self.ruleForm.pic_url.push({
                            id: item.id,
                            pic_url: item.url
                        });
                    });
                }
            },
            // 是否开启规格
            checkedAttr(e) {
                if (e == 1) {
                    this.attrGroups = [];
                    this.ruleForm.goods_num = this.ruleForm.goods_num ? this.ruleForm.goods_num : 0;
                }
            },
            itemChecked(type) {
                this.ruleForm.confine_count = type ? -1 : 0;
            },
            itemOrderChecked(type) {
                this.ruleForm.confine_order_count = type ? -1 : 0;
            },
			// 选择商品（清空弹窗已有商品）
            selectGoodsWarehouse(goods_warehouse) {
                this.ruleForm.select_attr_groups = [];
                this.getDetail(goods_warehouse.id, 'mall/goods/edit')
            },
            copyGoods() {
                this.copyLoading = true;
                request({
                    params: {
                        r: 'mall/goods/collect',
                        url: this.copyUrl
                    },
                    method: 'get'
                }).then(e => {
                    this.copyLoading = false;
                    if (e.data.code === 0) {
                        let detail = e.data.data.detail;
                        if (this.form && this.form.extra) {
                            for (let i in this.form.extra) {
                                if (detail.use_attr == 1) {
                                    for (let j in detail.attr) {
                                        if (!detail.attr[j][i]) {
                                            detail.attr[j][i] = 0;
                                        }
                                    }
                                }
                                Vue.set(this.ruleForm, i, 0);
                            }
                        }

                        this.ruleForm = Object.assign(this.ruleForm, detail);
                        this.attrGroups = e.data.data.detail.attr_groups;
                        this.$message.success(e.data.msg);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.copyLoading = false;
                    console.log(e);
                });
            },
            updatePicUrl(e, params) {
                this.ruleForm.pic_url[params.currentIndex].id = e[0].id;
                this.ruleForm.pic_url[params.currentIndex].pic_url = e[0].url;
            },
            getOptionName(name) {
                let newName = name;
                let num = 12;
                if (newName.length > num) {
                    newName = newName.substring(0, num) + '...';
                }
                return newName;
            },

            /*---商品服务----*/
            serviceOpen() {
                this.service.dialog = true;
                this.getServices();
            },
            serviceCancel() {
                this.service.dialog = false;
                this.service.list = [];
            },
            serviceConfirm() {
                let self = this;
                let newServices = JSON.parse(JSON.stringify(this.service.list));
                let addServices = [];
                newServices.forEach(function (item, index) {
                    let sign = true;
                    self.ruleForm.services.forEach(function (item2, index2) {
                        if (item.id == item2.id) {
                            sign = false;
                        }
                    })
                    if (sign) {
                        addServices.push(item)
                    }
                });
                this.ruleForm.services = this.ruleForm.services.concat(addServices);
                this.serviceCancel();
            },
            serviceDelete(index) {
                this.ruleForm.services.splice(index, 1);
            },
            /*---运费----*/
            freightOpen() {
                this.freight.dialog = true;
                this.getFreight();
            },
            freightCancel() {
                this.freight.checked = {};
                this.freight.dialog = false;
            },
            freightConfirm() {
                this.ruleForm.freight = JSON.parse(JSON.stringify(this.freight.checked));
                this.ruleForm.freight_id = this.ruleForm.freight.id;
                this.freightCancel();
            },
            freightDelete() {
                this.ruleForm.freight = null;
                this.ruleForm.freight_id = 0;
            },
            cardDelete(index) {
                this.ruleForm.cards.splice(index, 1);
            },

            // 上架状态开关，弹框文字提示
            statusChange(res) {
                if (res && this.status_change_text) {
                    this.$alert(this.status_change_text, '提示', {
                        confirmButtonText: '确定',
                        callback: action => {
                        }
                    });
                }
            },
            selectForm(data) {
                this.ruleForm.form = data;
                this.ruleForm.form_id = data ? data.id : -1;
            }
        },
        updated(){
            // console.log(this.ruleForm.attr);
			if(this.moreAttrs){
				// 这里可以计算一下多规格的总库存
				let goods_stock = 0;
				this.ruleForm.attr.forEach(function (item) {
					goods_stock += parseInt(item.stock);
				})
				this.goods_stock = goods_stock;
			}
        }
    });
</script>
