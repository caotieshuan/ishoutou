<?php
/*array(菜单名，菜单url参数，是否显示)*/
$i=0;
$j=0;
$menu_left =  array();
$menu_left[$i]=array('全局','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('全局设置','#',1);
$menu_left[$i][$i."-".$j][] = array('欢迎页',U('/admin/welcome/index'),1);
$menu_left[$i][$i."-".$j][] = array('网站设置',U('/admin/global/websetting'),1);
$menu_left[$i][$i."-".$j][] = array('友情链接',U('/admin/global/friend'),1);
$menu_left[$i][$i."-".$j][] = array('媒体报道',U('/admin/global/media'),1);
$menu_left[$i][$i."-".$j][] = array('i主播',U('/admin/global/izhubo'),1);
$menu_left[$i][$i."-".$j][] = array('广告管理',U('/admin/ad/'),1);

$menu_left[$i][$i."-".$j][] = array('登录接口管理',U('/admin/loginonline/'),1);
$menu_left[$i][$i."-".$j][] = array("自动执行参数",U("/admin/auto/"),1);
$menu_left[$i][$i."-".$j][] = array("后台操作日志",U("/admin/global/adminlog"),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('缓存管理','#',1);
$menu_left[$i][$i."-".$j][] = array('所有缓存',U('/admin/global/cleanall'),1);

$i++;
$menu_left[$i]= array('借款管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('借款列表','#',1);
$menu_left[$i][$i."-".$j][] = array('初审待审核借款',U('/admin/borrow/waitverify'),1);
$menu_left[$i][$i."-".$j][] = array('复审待审核借款',U('/admin/borrow/waitverify2'),1);
$menu_left[$i][$i."-".$j][] = array('招标中借款',U('/admin/borrow/waitmoney'),1);
$menu_left[$i][$i."-".$j][] = array('还款中借款',U('/admin/borrow/repaymenting'),1);
$menu_left[$i][$i."-".$j][] = array('已完成的借款',U('/admin/borrow/done'),1);
$menu_left[$i][$i."-".$j][] = array('已流标借款',U('/admin/borrow/unfinish'),1);
$menu_left[$i][$i."-".$j][] = array('初审未通过的借款',U('/admin/borrow/fail'),1);
$menu_left[$i][$i."-".$j][] = array('复审未通过的借款',U('/admin/borrow/fail2'),1);
$menu_left[$i][$i."-".$j][] = array('异常未满的借款',U('/admin/borrow/borrowfull'),1);

/* $j++;
$menu_left[$i]['low_title'][$i."-".$j] = array("企业直投管理","#",1);
$menu_left[$i][$i."-".$j][] = array('添加企业直投',U('/admin/tborrow/add'),1);
$menu_left[$i][$i."-".$j][] = array("投资中的借款标",U("/admin/tborrow/index"),1);
$menu_left[$i][$i."-".$j][] = array("还款中的借款标",U("/admin/tborrow/repayment"),1);
$menu_left[$i][$i."-".$j][] = array("已还完的借款标",U("/admin/tborrow/endtran"),1);
$menu_left[$i][$i."-".$j][] = array("已流标的借款标",U("/admin/tborrow/liubiaolist"),1); */

/*$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array("活期理财管理","#",1);
$menu_left[$i][$i."-".$j][] = array('添加活期理财',U('/admin/current/add'),1);
$menu_left[$i][$i."-".$j][] = array('发布中活期理财',U('/admin/current/index'),1);
$menu_left[$i][$i."-".$j][] = array('已完成活期理财',U('/admin/current/complete'),1);
$menu_left[$i][$i."-".$j][] = array('待审核活期提取',U('/admin/current/extraction'),1);
$menu_left[$i][$i."-".$j][] = array('核活期已提取',U('/admin/current/yextraction'),1);*/


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array("散标投资管理","#",1);
$menu_left[$i][$i."-".$j][] = array('添加散标投资',U('borrow/post/normal'),1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array("债权转让管理","#",1);
$menu_left[$i][$i."-".$j][] = array('债权转让',U('/admin/debt/index'),1);

/* $j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('融资申请管理','#',1);
$menu_left[$i][$i."-".$j][] = array('融资申请',U('/admin/rongzi/index'),1);
 */
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('逾期借款管理','#',1);
$menu_left[$i][$i."-".$j][] = array('逾期统计',U('/admin/expired/detail'),0);
$menu_left[$i][$i."-".$j][] = array('已逾期借款',U('/admin/expired/index'),1);
$menu_left[$i][$i."-".$j][] = array('逾期会员列表',U('/admin/expired/member'),1);

$i++;
$menu_left[$i]= array('股票配资','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('融资管理','#',1);
$menu_left[$i][$i."-".$j][] = array('融资列表',U('/admin/capital'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('按天配资','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核',U('/admin/daystock/index'),1);
$menu_left[$i][$i."-".$j][] = array('交易中',U('/admin/daystock/transaction'),1);
$menu_left[$i][$i."-".$j][] = array('已平仓',U('/admin/daystock/closed'),1);
$menu_left[$i][$i."-".$j][] = array('审核未通过',U('/admin/daystock/notexamine'),1);
$menu_left[$i][$i."-".$j][] = array('追加待审核',U('/admin/daystock/additional'),1);
$menu_left[$i][$i."-".$j][] = array('减少待审核',U('/admin/daystock/reduce'),1);
$menu_left[$i][$i."-".$j][] = array('提取盈利申请',U('/admin/daystock/extraction'),1);
$menu_left[$i][$i."-".$j][] = array('资金补充申请',U('/admin/daystock/supply'),1);
$menu_left[$i][$i."-".$j][] = array('平仓申请',U('/admin/daystock/opens'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('按月配资','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核',U('/admin/Monthstock/waitexamine'),1);
$menu_left[$i][$i."-".$j][] = array('交易中',U('/admin/Monthstock/transaction'),1);
$menu_left[$i][$i."-".$j][] = array('已平仓',U('/admin/Monthstock/alreadyopen'),1);
$menu_left[$i][$i."-".$j][] = array('审核未通过',U('/admin/Monthstock/examinenop'),1);
$menu_left[$i][$i."-".$j][] = array('追加申请',U('/admin/Monthstock/additional'),1);
$menu_left[$i][$i."-".$j][] = array('补充实盘申请',U('/admin/Monthstock/supply'),1);
$menu_left[$i][$i."-".$j][] = array('平仓申请',U('/admin/Monthstock/applyeven'),1);
$menu_left[$i][$i."-".$j][] = array('提取盈利申请',U('/admin/Monthstock/extraction'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('我是操盘手','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核',U('/admin/stoperation/index'),1);
$menu_left[$i][$i."-".$j][] = array('交易中',U('/admin/stoperation/dealing'),1);
$menu_left[$i][$i."-".$j][] = array('已平仓',U('/admin/stoperation/closehouse'),1);
$menu_left[$i][$i."-".$j][] = array('追加申请',U('/admin/stoperation/additional'),1);
$menu_left[$i][$i."-".$j][] = array('补充实盘申请',U('/admin/stoperation/supply'),1);
$menu_left[$i][$i."-".$j][] = array('平仓申请',U('/admin/stoperation/applyeven'),1);
$menu_left[$i][$i."-".$j][] = array('减少实盘申请',U('/admin/stoperation/cutsupply'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('免费体验','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核',U('/admin/Freestock/index'),1);
$menu_left[$i][$i."-".$j][] = array('交易中',U('/admin/Freestock/transaction'),1);
$menu_left[$i][$i."-".$j][] = array('已平仓',U('/admin/Freestock/closed'),1);
$menu_left[$i][$i."-".$j][] = array('审核未通过',U('/admin/Freestock/notexamine'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('节假日管理','#',1);
$menu_left[$i][$i."-".$j][] = array('节假日',U('/admin/Holiday/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('股票配资设置','#',1);
$menu_left[$i][$i."-".$j][] = array('按天配资',U('/admin/stockglobal/websetting'),1);
$menu_left[$i][$i."-".$j][] = array('按月配资杠杆配置',U('/admin/Monthstock/configindex'),1);
$menu_left[$i][$i."-".$j][] = array('按月配资月数配置',U('/admin/Monthstock/configmonth'),1);
$menu_left[$i][$i."-".$j][] = array('按月配资利率配置',U('/admin/Monthstock/configrate'),1);
$menu_left[$i][$i."-".$j][] = array('我是操盘手',U('/admin/Trader/index'),1);
$menu_left[$i][$i."-".$j][] = array('HOMS账号管理',U('/admin/stockglobal/homsuser'),1); 
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('配资专员管理','#',1);
$menu_left[$i][$i."-".$j][] = array('专员管理',U('/admin/stockuser/index'),1);


$i++;
$menu_left[$i]= array('会员管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('会员管理','#',1);
$menu_left[$i][$i."-".$j][] = array('会员列表',U('/admin/members/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员资料列表',U('/admin/members/info'),1);
$menu_left[$i][$i."-".$j][] = array('会员投资纪录',U('/admin/members/loanlist'),1);
$menu_left[$i][$i."-".$j][] = array('举报信息',U('/admin/jubao/index'),1);
$menu_left[$i][$i."-".$j][] = array("会员登录日志",U("/admin/members/userlog"),1);
$j++;

$menu_left[$i]['low_title'][$i."-".$j] = array('配资专员','#',1);
$menu_left[$i][$i."-".$j][] = array('配资记录',U('/admin/stockDetail/index'),1);
$menu_left[$i][$i."-".$j][] = array('财务统计',U('/admin/stockDetail/financeDetail'),1);
$j++;

$menu_left[$i]['low_title'][$i."-".$j] = array('认证及申请管理','#',1);
$menu_left[$i][$i."-".$j][] = array('手机认证会员',U('/admin/verifyphone/index'),1);
//$menu_left[$i][$i."-".$j][] = array('视频认证申请',U('/admin/verifyvideo/index'),1);
//$menu_left[$i][$i."-".$j][] = array('现场认证申请',U('/admin/verifyface/index'),1);
$menu_left[$i][$i."-".$j][] = array('VIP申请管理',U('/admin/vipapply/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员实名认证申请',U('/admin/memberid/index'),1);
$menu_left[$i][$i."-".$j][] = array('额度申请待审核',U('/admin/members/infowait'),1);
$menu_left[$i][$i."-".$j][] = array('上传资料管理',U('/admin/memberdata/index'),1);
/**
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('快捷借款管理','#',1);
$menu_left[$i][$i."-".$j][] = array('快捷借款列表',U('/admin/feedback/index'),1);
*/
/* 
$i++;
$menu_left[$i]= array('积分管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('投资积分管理','#',1);
$menu_left[$i][$i."-".$j][] = array('投资积分操作记录',U('/admin/market/index'),1);
$menu_left[$i][$i."-".$j][] = array('商品兑换管理',U('/admin/market/getlog'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('积分商城管理','#',1);
$menu_left[$i][$i."-".$j][] = array('商城商品列表',U('/admin/market/goods'),1);
$menu_left[$i][$i."-".$j][] = array('抽奖商品列表',U('/admin/market/lottery'),1);
$menu_left[$i][$i."-".$j][] = array('评论列表',U('/admin/market/comment'),1);
 */
$i++;
$menu_left[$i]= array('充值提现','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('充值管理','#',1);
$menu_left[$i][$i."-".$j][] = array('在线充值',U('/admin/Paylog/paylogonline'),1);
$menu_left[$i][$i."-".$j][] = array('快捷充值银行卡',U('/admin/members/cards'),1);
//$menu_left[$i][$i."-".$j][] = array('ATM机转账',U('/admin/Paylog/paylogoffline'),1);
//$menu_left[$i][$i."-".$j][] = array('支付宝充值',U('/admin/Paylog/paylogalipay'),1);
$menu_left[$i][$i."-".$j][] = array('充值记录总列表',U('/admin/Paylog/index'),1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('提现管理','#',1);
$menu_left[$i][$i."-".$j][] = array('待审核提现',U('/admin/Withdrawlogwait/index'),1);
$menu_left[$i][$i."-".$j][] = array('审核通过,处理中',U('/admin/Withdrawloging/index'),1);
$menu_left[$i][$i."-".$j][] = array('已提现 ',U('/admin/Withdrawlog/withdraw2'),1);
$menu_left[$i][$i."-".$j][] = array('审核未通过',U('/admin/Withdrawlog/withdraw3'),1);
$menu_left[$i][$i."-".$j][] = array('提现申请总列表',U('/admin/Withdrawlog/index'),1);

$i++;
$menu_left[$i]= array('文章管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('文章管理','#',1);
$menu_left[$i][$i."-".$j][] = array('文章列表',U('/admin/article/'),1);
$menu_left[$i][$i."-".$j][] = array('文章分类',U('/admin/acategory/'),1);
/* $i++;
$menu_left[$i]= array('菜单管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('菜单管理','#',1);
$menu_left[$i][$i."-".$j][] = array('导航菜单',U('/admin/navigation/index'),1); */

$i++;
$menu_left[$i]= array('活动管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('活动管理','#',1);
$menu_left[$i][$i."-".$j][] = array('回款续投奖励',U('/admin/Reward/Today'),1);
$menu_left[$i][$i."-".$j][] = array('自定义活动',U('/admin/Activity/Diy'),1);
$menu_left[$i][$i."-".$j][] = array('推荐投资奖励',U('/admin/refereeDetail/index'),1);
$menu_left[$i][$i."-".$j][] = array('推广管理',U('/admin/promote/index'),1);
$menu_left[$i][$i."-".$j][] = array('红包管理',U('/admin/redbag/index'),1);
$menu_left[$i][$i."-".$j][] = array('9月活动页设置',U('/admin/Activity/month'),1);

$i++;
$menu_left[$i]= array('资金统计','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('会员帐户','#',1);
$menu_left[$i][$i."-".$j][] = array('会员帐户',U('/admin/Capitalaccount/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('推广统计','#',1);
$menu_left[$i][$i."-".$j][] = array('优投网统计',U('/admin/Capitalaccount/yott'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('充值提现','#',1);
$menu_left[$i][$i."-".$j][] = array('充值记录',U('/admin/capitalOnline/charge'),1);
$menu_left[$i][$i."-".$j][] = array('提现记录',U('/admin/capitalOnline/withdraw'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('会员资金变动记录','#',1);
$menu_left[$i][$i."-".$j][] = array('资金记录',U('/admin/capitalDetail/index'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('网站资金统计','#',1);
$menu_left[$i][$i."-".$j][] = array('网站资金统计',U('/admin/capitalAll/index'),1);

$i++;
$menu_left[$i]= array('权限','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('用户权限管理',"#",1);
$menu_left[$i][$i."-".$j][] = array('管理员管理',U('/admin/Adminuser/'),1);
$menu_left[$i][$i."-".$j][] = array('用户组权限管理',U('/admin/acl/'),1);

/*
$i++;
$menu_left[$i]= array('金融合作','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('金融合作管理',"#",1);
$menu_left[$i][$i."-".$j][] = array('金融合作列表',U('/admin/Enterprise/index'),1);
$menu_left[$i][$i."-".$j][] = array('经理人入驻',U('/admin/Enterprise/hlist'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('贷款申请','#',1);
$menu_left[$i][$i."-".$j][] = array('贷款申请列表',U('/admin/Loan/index'),1);
$menu_left[$i][$i."-".$j][] = array('快速申请列表',U('/admin/Loan/lists'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('快速投资申请','#',1);
$menu_left[$i][$i."-".$j][] = array('快速投资申请列表',U('/admin/Loan/invest'),1);
*/

$i++;
$menu_left[$i]= array('数据库','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('数据库管理','#',1);
$menu_left[$i][$i."-".$j][] = array('数据库信息',U('/admin/db/'),1);
$menu_left[$i][$i."-".$j][] = array('备份管理',U('/admin/db/baklist'),1);
$menu_left[$i][$i."-".$j][] = array('清空数据',U('/admin/db/truncate'),1);

$i++;
$menu_left[$i]= array('扩展管理','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('参数管理','#',1);
$menu_left[$i][$i."-".$j][] = array('业务参数管理',U('/admin/bconfig/index'),1);
$menu_left[$i][$i."-".$j][] = array('合同居间方资料上传',U('/admin/hetong/index'),1);
$menu_left[$i][$i."-".$j][] = array('信用级别管理',U('/admin/leve/index'),1);
$menu_left[$i][$i."-".$j][] = array('投资级别管理',U('/admin/leve/invest'),1);
$menu_left[$i][$i."-".$j][] = array('会员年龄别称',U('/admin/age/index'),1);
$menu_left[$i][$i."-".$j][] = array('实名认证设置',U('/admin/id5/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('充值银行管理','#',1);
//$menu_left[$i][$i."-".$j][] = array('线下充值银行管理',U('/admin/payoffline/'),1);
$menu_left[$i][$i."-".$j][] = array('PC支付接口管理',U('/admin/payonline/'),1);
$menu_left[$i][$i."-".$j][] = array('WAP支付接口管理',U('/admin/payonline/wap'),1);
$menu_left[$i][$i."-".$j][] = array('银行列表管理',U('/admin/payonline/banklist'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('在线客服管理','#',1);
$menu_left[$i][$i."-".$j][] = array('QQ客服管理',U('/admin/QQ/index'),1);
$menu_left[$i][$i."-".$j][] = array('QQ群管理',U('/admin/QQ/qun'),1);
$menu_left[$i][$i."-".$j][] = array('客服电话管理',U('/admin/QQ/tel/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('在线通知管理','#',1);
$menu_left[$i][$i."-".$j][] = array('通知信息接口管理',U('/admin/msgonline/'),1);
$menu_left[$i][$i."-".$j][] = array('通知信息模板管理',U('/admin/msgonline/templet/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('百度云推送管理','#',0);
$menu_left[$i][$i."-".$j][] = array('手机客户端云推送',U('/admin/baidupush/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('安全检测','#',1);
$menu_left[$i][$i."-".$j][] = array('文件管理',U('/admin/mfields/'),1); 
$menu_left[$i][$i."-".$j][] = array('木马查杀',U('/admin/scan/'),1);

?>

