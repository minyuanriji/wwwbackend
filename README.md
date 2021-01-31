### 开发必备技能

php、yii2、vue.js、uniapp、sql

### api 数据返回格式
````
 return [
 'code'=>ApiCode::CODE_SUCEESSS,
 'msg'=>'此处作为提示',
 'data'=>[
     'list'=>'列表'，
     'user_info'=>'对象或者单数组'
         ]
 ] 
 ````
### 系统上传用到四种上传方式
本地、阿里云对象存储、腾讯云对象存储、七牛云，所有的存储都是走统一的接口，所有的文件上传操作，先提交到统一的接口，之后再推送到目标位置。

### 微信支付
目前使用的是easywechat，示例对象：\Yii::$app->wechat->payment 后续扩展alipay

### 微信sdk
目前使用的是easywechat，示例对象：\Yii::$app->wechat->app

### 系统佣金字段表示方式
````
total_price 总佣金
price 当前佣金
frozen_price 冻结的佣金
 ````
 
 
 ### 系统所有记录的表后缀_log
 ````
例如：distribution_log 
 ````

  
 ### 系统所有的列表数据用list
 ````
例如：goods_list
控制器中   actionList、actionGoodsList都是合理的

 
 ````
 ### 系统所有的获取信息
 ````
例如：info、goods_info
控制器中   actionInfo、actionGoodsInfo都是合理的

 
 ````

 ### 系统所有的返回结果统一用下划线返回方式
 ````
例如：goods_info=>$goods 不能使用goodsInfo
同时前端开发所有的字段应该以下划线的方式，函数用驼峰
例如前端在获取接口的格式为：
api.控制器.方法名
例如：api.goods_info.info(而不应该api.goodsInfo.info)
注意：如果控制器是两个单词组成的那么就用下划线

 ````


### 前端在写api.js文件中

 ````
    控制器名称：{
         方法名称 ：接口根+'控制器/方法'
    }

   例如  
   
    goods：{
     info:api_root+'goods/info'
    }
    goods:{
     comment_list:api_root+'goods/comment-list'
    }
    
 
 ````

### 前端api.js插件接口的写法

 ````
  plugin：{
    插件名称：{
             控制器名称：{
                  方法名称 ：接口根+插件路径+'控制器/方法'
             }      
      }
  }
    例如:为了拼凑出以下接口
    plugin/distribution/api/distribution/log-list
    plugin：{
      distribution：{  //分销插件
               distribution：{
                    log_list ：api_root+'plugin/distribution/api/distribution/log-list'
               }      
        },  
       group：{  //拼团插件
                       distribution：{
                            log_list ：api_root+'plugin/group/api/group/log-list'
                       }      
                }
        
    }
    
 ````
 ### 公共订单事件
 
````

CommonOrderDetailHandler

两个事件 


common_order_detail_created 公共订单创建,该事件已经在afterSave里面自动调用
需要维护的是以下事件：
状态改变
common_order_detail_status_changed




````
 
 
 
 
 
 
