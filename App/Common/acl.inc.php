<?php
/*array(菜单名，菜单url参数，是否显示)*/
//error_reporting(E_ALL);
/*
$acl_inc[$i]['low_leve']['global']  global是model
每个action前必须添加eqaction_前缀'eqaction_websetting'  => 'at1','at1'表示唯一标志,可独自命名,eqaction_后面跟的action必须统一小写


*/
$acl_inc =  array();
$i=0;
$acl_inc[$i]['low_title'][] = '全局设置';
$acl_inc[$i]['low_leve']['global']= array( "网站设置" =>array(
												 "列表" 		=> 'at1',
												 "增加" 		=> 'at2',
												 "删除" 		=> 'at3',
												 "修改" 		=> 'at4',
												),
											"友情链接" =>array(
												 "列表" 		=> 'at5',
												 "增加" 		=> 'at6',
												 "删除" 		=> 'at7',
												 "修改" 		=> 'at8',
												 "搜索" 		=> 'att8',
											),
											"媒体报道" =>array(
												 "列表" 		=> 'at9',
												 "增加" 		=> 'at9',
												 "删除" 		=> 'at10',
												 "修改" 		=> 'at11',
												 "搜索" 		=> 'att11',
											),
											"i主播" =>array(
												 "列表" 		=> 'at19',
												 "增加" 		=> 'at19',
												 "删除" 		=> 'at110',
												 "修改" 		=> 'at111',
												 "搜索" 		=> 'att111',
											),
											"所有缓存" =>array(
												 "清除" 		=> 'at22',
											),
											"后台操作日志" =>array(
												 "列表" 		=> 'at23',
												 "删除"			=>'at24',
												 "删除一月前操作日志"=>'at25',
											),
										   "data" => array(
										   		//网站设置
												'eqaction_websetting'  => 'at1',
												'eqaction_doadd'    => 'at2',
												'eqaction_dodelweb'    => 'at3',
												'eqaction_doedit'   => 'at4',
												//友情链接
												'eqaction_friend'  	   => 'at5',
												'eqaction_dodeletefriend'    => 'at7',
												'eqaction_searchfriend'    => 'att8',
												'eqaction_addfriend'   => array(
																'at6'=>array(
																	'POST'=>array(
																		"fid"=>'G_NOTSET',
																	),
																 ),	
																'at8'=>array(
																	'POST'=>array(
																		"fid"=>'G_ISSET',
																	),
																),
													),
													//媒体报道
												'eqaction_media'  	   => 'at9',
												'eqaction_dodeletemedia'    => 'at10',
												'eqaction_searchmedia'    => 'att11',
												'eqaction_addmedia'   => array(
																'at9'=>array(
																	'POST'=>array(
																		"fid"=>'G_NOTSET',
																	),
																 ),	
																'at11'=>array(
																	'POST'=>array(
																		"fid"=>'G_ISSET',
																	),
																),
													),
												//i主播
												'eqaction_izhubo'  	   => 'at19',
												'eqaction_dodeleteizhubo'    => 'at110',
												'eqaction_searchizhubo'    => 'att111',
												'eqaction_addizhubo'   => array(
																'at19'=>array(
																	'POST'=>array(
																		"fid"=>'G_NOTSET',
																	),
																 ),	
																'at111'=>array(
																	'POST'=>array(
																		"fid"=>'G_ISSET',
																	),
																),
													),
										   		//清除缓存
												'eqaction_cleanall'  => 'at22',
												'eqaction_adminlog'  => 'at23',
												'eqaction_dodeletelog'=>'at24',
												'eqaction_dodellogone'=>'at25',//删除近期一个月内的后台操作日志
											)
							);
$acl_inc[$i]['low_leve']['ad']= array( "广告管理" =>array(
												 "列表" 		=> 'ad1',
												 "增加" 		=> 'ad2',
												 "删除" 		=> 'ad4',
												 "修改" 		=> 'ad3',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'ad1',
												'eqaction_add'    => 'ad2',
												'eqaction_doadd'    => 'ad2',
												'eqaction_edit'    => 'ad3',
												'eqaction_doedit'    => 'ad3',
												'eqaction_swfupload'    => 'ad3',
												'eqaction_dodel'    => 'ad4',
											)
							);

$acl_inc[$i]['low_leve']['loginonline']= array( "登录接口管理" =>array(
												 "查看" 		=> 'dl1',
												 "修改" 		=> 'dl2',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'dl1',
												'eqaction_save'    => 'dl2',
												'eqaction_save'    => 'dl2',
											)
							);
$acl_inc[$i]['low_leve']['auto'] = array("自动执行参数" => array( 
												"查看" => "atjb1", 
												"修改" => "atjb2", 
												"开启程序" => "atjb3", 
												"关闭程序" => "atjb4", 
												"开启服务" => "atjb5", 
												"卸载服务" => "atjb7", 
												"当前运行状态" => "atjb6",
												),
												"data" => array( 
												"eqaction_index" => "atjb1", 
												"eqaction_save" => "atjb2", 
												"eqaction_start" => "atjb3", 
												"eqaction_close" => "atjb4", 
												"eqaction_startserver" => "atjb5", 
												"eqaction_stopserver" => "atjb7", 
												"eqaction_showstatus" => "atjb6",
												)
							);

$i++;
$acl_inc[$i]['low_leve']['capital']= array( "融资管理" =>array(
	"列表" 		=> 'czz1',
	"查看" 		=> 'czz2',
	"删除"     => 'czz3',
),

	"data" => array(
		'eqaction_index'  => 'czz1',
		'eqaction_view'  => 'czz2',
		"eqaction_dodel" => "czz3",

	)
);

$i++;
$acl_inc[$i]['low_title'][] = '借款管理';
$acl_inc[$i]['low_leve']['borrow']= array( "初审待审核借款" =>array(
												 "列表" 		=> 'br1',
												 "审核" 		=> 'br2',
												),
										   "复审待审核借款" =>array(
												 "列表" 		=> 'br3',
												 "审核" 		=> 'br4',
											),
										   "招标中的借款" =>array(
												 "列表" 		=> 'br5',
												 "审核" 		=> 'br6',
												 "人工处理" 	=> 'br8',
											),
										   "还款中的借款" =>array(
												 "列表" 		=> 'br7',
												 "一周内到期标" =>'br7',
												 "投资记录" =>'br15',
												 "导出" =>'br116',
												 "补图" => 'br66',
												 "保存"=> 'br67',
											),
										   "已完成的借款" =>array(
												 "列表" 		=> 'br9',
											),
										   "已流标借款" =>array(
												 "列表" 		=> 'br11',
											),
										   "初审未通过的借款" =>array(
												 "列表" 		=> 'br13',
											),
										   "复审未通过的借款" =>array(
												 "列表" 		=> 'br14',
											),
											"异常未满的借款" =>array(
												 "列表" 		=> 'br16',
												 "人工处理" 		=> 'br17',
											),
									   "data" => array(
										   		//网站设置
												'eqaction_waitverify'  => 'br1',
												'eqaction_edit' =>'br2',
												'eqaction_add_ajax' =>'br2',
												'eqaction_edit' =>'br4',	
												'eqaction_edit' =>'br6',	
												'eqaction_doeditwaitverify' =>'br2',	
												'eqaction_waitverify2'  => 'br3',
												'eqaction_doeditwaitverify2'  => 'br4',
												'eqaction_waitmoney'  => 'br5',
												'eqaction_doeditwaitmoney'  => 'br6',
												'eqaction_repaymenting'    => 'br7',
												'eqaction_doweek'    => 'br7',
												'eqaction_done'    => 'br9',
												'eqaction_unfinish'    => 'br11',
												'eqaction_fail'    => 'br13',
												'eqaction_fail2'    => 'br14',
												'eqaction_swfupload'  => 'br2',
												'eqaction_dowaitmoneycomplete'  => 'br8',
												'eqaction_doinvest'  => 'br15',
												'eqaction_borrowfull'  => 'br16',
												'eqaction_domoneycomplete'  => 'br17',
												'eqaction_export'  => 'br116',
												'eqaction_edit_repay'  => 'br66',
												'eqaction_doeditwaitrepay'  => 'br67',
											)
							);
$acl_inc[$i]['low_leve']['debt'] = array("债权转让" => array(
                                       '查看' => 'debt1',
                                       '审核' => 'debt2',
                                    ),
                                    "data" => array(
                                        'eqaction_index' => 'debt1',
                                        'eqaction_audit' => 'debt2',
                                    ),

);

$acl_inc[$i]['low_leve']['current'] = array("活期理财" => array(
                                       '查看' => 'hq1',
                                       '审核' => 'hq2',
                                    ),
                                    "data" => array(
                                        'eqaction_index' => 'hq1',
                                        'eqaction_add' => 'hq1',
                                        'eqaction_doadd' => 'hq2',
                                        'eqaction_complete' => 'hq1',
                                        'eqaction_record' => 'hq1',
                                        'eqaction_extraction' => 'hq1',
                                        'eqaction_doextraction' => 'hq2',
                                        'eqaction_examiney' => 'hq2',
                                        'eqaction_yextraction' => 'hq1',
                                    ),

);

$acl_inc[$i]['low_leve']['promote'] = array(
		"推广管理" => array(
		   '查看' => 'pj1',
		   '添加' => 'pj2',
		   '保存' => 'pj3',
		),
		"data" => array(
			'eqaction_index' => 'pj1',
			'eqaction_add' => 'pj2',
			'eqaction_lists' => 'pj1',
			'eqaction_export' => 'pj1',
			'eqaction_dosave' => 'pj3',
		),
);
$acl_inc[$i]['low_leve']['redbag'] = array(
		"wap红包管理" => array(
		   '设置' => 'pj1',
		   '查看' => 'pj2',
		),
		"data" => array(
			'eqaction_index' => 'pj1',
			'eqaction_setredbag' => 'pj2',
			'eqaction_lists' => 'pj1',
			'eqaction_export' => 'pj1',
			'eqaction_preview' => 'pj2',
			'eqaction_savepreview' => 'pj2',
			'eqaction_redchange' => 'pj2',
		),
);

$acl_inc[$i]['low_leve']['reward'] = array(
		"回款续投奖励" => array(
		   '查看' => 'pj1',
		),
		"data" => array(
			'eqaction_index' => 'pj1',
			'eqaction_Today' => 'pj1',
		),
);
$acl_inc[$i]['low_leve']['activity'] = array(
			"自定义活动奖励" => array(
			   '查看' => 'pj1',
			   '添加／编辑' => 'pj2',
			   '发奖' => 'pj3',
			   '9月活动页' => 'pj4',
			   '删除' => 'pj5',
			   '导出' => 'pj6',
			),
			"data" => array(
				'eqaction_diy' => 'pj1',	//查看
				'eqaction_adddiy' => 'pj2',	//添加
				'eqaction_doadddiy' => 'pj2',	//添加
				'eqaction_editdiy' => 'pj2',	//编辑
				'eqaction_doeditdiy' => 'pj2',	//编辑
				'eqaction_uploadxls' => 'pj2',	//上传
				'eqaction_openXls' => 'pj2',	//上传
				'eqaction_format_excel2array' => 'pj2',	//上传
				'eqaction_sendPrize' => 'pj2',	//发奖
				'eqaction_prizes' => 'pj3',//发奖
				'eqaction_prizes' => 'pj3',//发奖
				'eqaction_doprizes' => 'pj3',//发奖
				'eqaction_month' => 'pj4',//发奖
				'eqaction_doaddday' => 'pj4',//发奖
				'eqaction_addday' => 'pj4',//发奖
				'eqaction_delday' => 'pj4',//发奖
				'eqaction_filllog' => 'pj4',//发奖
				'eqaction_deldiy' => 'pj5',//删除
				'eqaction_export' => 'pj6',
			),
	);

$i++;
$acl_inc[$i]['low_leve']['rongzi']= array( "融资申请管理" =>array(
	"列表" 		=> 'rz1',
	"查看" 		=> 'rz2',
	"删除"     => 'rz3',
),

	"data" => array(
		'eqaction_index'  => 'rz1',
		'eqaction_edit'  => 'rz2',
		"eqaction_dodel" => "rz3",

	)
);
$acl_inc[$i]['low_leve']['expired']= array( "逾期借款管理" =>array(
												 "查看" 		=> 'yq1',
												 "代还" 		=> 'yq2',
												),
										   "逾期会员列表" =>array(
												 "列表" 		=> 'yq3',
											),
									   "data" => array(
												'eqaction_index'  => 'yq1',
												'eqaction_doexpired'  => 'yq2',
												'eqaction_member'  => 'yq3',
											)
							);
$i++;
$acl_inc[$i]['low_title'][] = '企业直投管理';
$acl_inc[$i]['low_leve']['tborrow'] = array("企业直投管理" => array( 
												"列表" => "tb1", 
												"添加" => "tb2", 
												"修改" => "tb3", 
												"删除" => "tb6",
												"投资记录" =>'tb4',
												"流标" =>'tb7',),
										"data" => array( 
										"eqaction_endtran" => "tb1", 
										"eqaction_index" => "tb1", 
										"eqaction_repayment" => "tb1",
										"eqaction_liubiaolist" => "tb1",
										"eqaction_getusername" => "tb2", 
										"eqaction_swfupload" => "tb2", 
										"eqaction_add" => "tb2", 
										"eqaction_doadd" => "tb2", 
										"eqaction_add_ajax" => "tb2", 
										"eqaction_getusername" => "tb3", 
										"eqaction_swfupload" => "tb3", 
										"eqaction_edit" => "tb3", 
										"eqaction_doedit" => "tb3", 
										"eqaction_dodel" => "tb6",
										'eqaction_doinvest'  => 'tb4',
										'eqaction_liubiao'  => 'tb7',
										)
);
$i++;
$acl_inc[$i]['low_title'][] = '股票配资';
$acl_inc[$i]['low_leve']['daystock']= array( "待审核" =>array(
												 "列表" 		=> 'tt1',
												 "审核" 	=> 'tt2',
												),
										   "交易中" =>array(
												 "列表" 		=> 'tt3',
												 "平仓" 		=> 'tt4',
											),
										   "已平仓" =>array(
												 "列表" 		=> 'tt5',
												 "审核" 		=> 'tt6',
											),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'tt1',
												'eqaction_transaction'  => 'tt1',
												'eqaction_closed'  => 'tt1',
												'eqaction_postexamine'  => 'tt2',
												'eqaction_examiney'  => 'tt2',
												'eqaction_postedit'  => 'tt2',
												'eqaction_doedit'  => 'tt2',
												'eqaction_openedit'  => 'tt2',
												'eqaction_opendoedit'  => 'tt2',
												'eqaction_notexamine'  => 'tt1',
												'eqaction_additional'  => 'tt1',
												'eqaction_addexamine'  => 'tt2',
												'eqaction_doaddexamine'  => 'tt2',
												'eqaction_reduce'  => 'tt1',
												'eqaction_reduceexamine'  => 'tt2',
												'eqaction_doreduceexamine'  => 'tt2',
												'eqaction_extraction'  => 'tt1',
												'eqaction_postextraction'  => 'tt2',
												'eqaction_extrationdoedit'  => 'tt2',
												'eqaction_supply'  => 'tt1',
												'eqaction_dosupply'  => 'tt1',
												'eqaction_supplyexamine'  => 'tt2',
												'eqaction_opens'  => 'tt1',

											)
							);
$acl_inc[$i]['low_leve']['freestock']= array( "免费体验" =>array(
					 "列表" 		=> 'mf1',
					 "审核" 	=> 'mf2',
					),
		   "data" => array(
					//网站设置
					'eqaction_index'  => 'mf1',
					'eqaction_transaction'  => 'mf1',
					'eqaction_closed'  => 'mf1',
					'eqaction_postexamine'  => 'mf2',
					'eqaction_examiney'  => 'mf2',
					'eqaction_postedit'  => 'mf2',
					'eqaction_doedit'  => 'mf2',
					'eqaction_openedit'  => 'mf2',
					'eqaction_opendoedit'  => 'mf2',
					'eqaction_notexamine'  => 'mf1',

				)
);
							
$acl_inc[$i]['low_leve']['holiday']= array( "节假日" =>array(
												 "列表" 		=> 'jj1',
												 "添加" 	=> 'jj2',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'jj1',
												'eqaction_postdate'  => 'jj2',
												'eqaction_delete'  => 'jj2',

											)
							);	
$acl_inc[$i]['low_leve']['stockglobal']= array( "homs帐号管理" =>array(
												 "添加" 	=> 'tts2',
												 "删除" 	=> 'tts3',
												 "修改" 	=> 'tts4',
												 
												 
												 
												),
												"天天盈配置"=>array(
													"列表" => 'tty1',
													"添加" => 'tty2',
													"修改" => 'tty3',
													"删除" => 'tty4',
												
												),
									   "data" => array(
												'eqaction_doadd'  => 'tty2',
												'eqaction_dodelweb'  => 'tty3',
												'eqaction_doedit'  => 'tty4',
												'eqaction_homsuser'  => 'tts2',  
												'eqaction_dohomsuser'  => 'tts2',  
												'eqaction_echohtml'  => 'tts2',  
												'eqaction_doedits'  => 'tts2',  
												'eqaction_websetting'  => 'tty1',  

											)
							);	
$acl_inc[$i]['low_leve']['trader']= array( "我是操盘手" =>array(
												 "列表" 	=> 'cp1',
												 "添加" 	=> 'cp2',
												 "删除" 	=> 'cp3',
												 "修改" 	=> 'cp4',
												 
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'cp1',
												'eqaction_doadd'  => 'cp2',
												'eqaction_dodelweb'  => 'cp3',
												'eqaction_doedit'  => 'cp4',

											)
							);
$acl_inc[$i]['low_leve']['stoperation']= array( "待审核" =>array(
												 "列表" 	=> 'cp1',
												 "审核" 	=> 'cp2',
												),
										   "交易中" =>array(
												 "列表" 		=> 'cp3',
												 "平仓" 		=> 'cp4',
											),
										   "已平仓" =>array(
												 "列表" 		=> 'cp5',
												 "审核" 		=> 'cp6',
											),
									     "data" => array(
										   		//网站设置
												'eqaction_index'  => 'cp1',
												'eqaction_addlever'  => 'cp1',
												'eqaction_dealing'  => 'cp1',
												'eqaction_closehouse' => 'cp1',
												'eqaction_check' => 'cp2',
												'eqaction_postexamine'  => 'cp2',
												'eqaction_examiney'  => 'cp2',
												'eqaction_sendaccount'  => 'cp2',
												'eqaction_closedeal'  => 'cp4',
												'eqaction_closeaction'  => 'cp2',
												'eqaction_sendaccpass'  => 'cp2',
												'eqaction_additional'  => 'cp1',
												'eqaction_doaddexamine'  => 'cp2',
												'eqaction_addexamine'  => 'cp1',
												'eqaction_supply'  => 'cp4',
												'eqaction_dosupply'  => 'cp4',
												'eqaction_supplyexamine'  => 'cp4',
												"eqaction_applyeven"  => 'cp5',
												"eqaction_doapplyeven"  => 'cp4',
												"eqaction_doexapplyeven"  => 'cp4',
												'eqaction_cutsupply'  => 'cp1',
												'eqaction_cutdosupply'  => 'cp1',
												'eqaction_cutsupplyexamine'  => 'cp2',
											)
							);
							
$i++;
$acl_inc[$i]['low_title'][] = '配资专员佣金';
$acl_inc[$i]['low_leve']['stockuser'] = array(

	"配资专员佣金管理" => array(
		"列表" => 'code1',
		"修改" => 'code2',
	),
	"data" => array(
		'eqaction_index'  => 'code1',
		'eqaction_doupdate'  => 'code2',
		'eqaction_code'  => 'code2',
		'eqaction_addadmin'  => 'code2',
	)
);

$i++;
$acl_inc[$i]['low_title'][] = '月月盈';
$acl_inc[$i]['low_leve']['monthstock']= array(
	"待审核" =>array(
		"列表" 		=> 'yy1',
		"审核" 	=> 'yy2',
	),
	"交易中" =>array(
		"列表" 		=> 'yy3',
		"平仓" 		=> 'yy4',
	),
	"追加申请" =>array(
		"列表" 		=> 'yy11',
		"审核" 		=> 'yy12',
	),
	"补充实盘申请" =>array(
		"列表" 		=> 'yy13',
		"审核" 		=> 'yy14',
	),
	"平仓申请" =>array(
		"列表" 		=> 'yy15',
		"审核" 		=> 'yy16',
	),
	"提取盈利申请" =>array(
		"列表" 		=> 'yy17',
		"审核" 		=> 'yy18',
	),
	"已平仓" =>array(
		"列表" 		=> 'yy5',
	),
	"杠杆配置" =>array(
		"列表" 		=> 'yy6',
		"编辑" 		=> 'yy9',
		"新增" 		=> 'yy10',
	),
	"月数配置" =>array(
		"列表" 		=> 'yy7',
	),
	"利率配置" =>array(
		"列表" 		=> 'yy8',
	),
	"data" => array(
		"eqaction_configindex"  => 'yy6',
		"eqaction_configmonth"  => 'yy7',
		"eqaction_configrate"  => 'yy8',
		"eqaction_editlever"  => 'yy9',
		"eqaction_addlever"  => 'yy10',
		"eqaction_waitexamine"  => 'yy1',
		"eqaction_transaction"  => 'yy3',
		"eqaction_postedit"  => 'yy3',
		"eqaction_doedit"  => 'yy3',
		"eqaction_openedit"  => 'yy4',
		"eqaction_opendoedit"  => 'yy4',
		"eqaction_alreadyopen"  => 'yy5',
		"eqaction_examinenop"  => 'yy5',
		"eqaction_examine"  => 'yy2',
		"eqaction_doexamine"  => 'yy2',
		"eqaction_additional"  => 'yy11',
		"eqaction_addexamine"  => 'yy12',
		"eqaction_doaddexamine"  => 'yy12',
		"eqaction_supply"  => 'yy13',
		"eqaction_supplyexamine"  => 'yy14',
		"eqaction_dosupply"  => 'yy14',
		"eqaction_applyeven"  => 'yy15',
		"eqaction_doapplyeven"  => 'yy16',
		"eqaction_doexapplyeven"  => 'yy16',
		"eqaction_extraction"  => 'yy17',
		"eqaction_postextraction"  => 'yy18',
		"eqaction_extrationdoedit"  => 'yy18',
	)
);
							
							
$i++;
$acl_inc[$i]['low_title'][] = '会员管理';
$acl_inc[$i]['low_leve']['members']= array( "会员列表" =>array(
												 "列表" 		=> 'me1',
												 "调整余额" 	=> 'mx2',
												 "调整授信" 	=> 'mx3',
												 "删除会员" 	=> 'mxw',
												 "修改客户类型" 	=> 'xmxw',
												 "会员投资列表" 	=> 'mxw8',
												 "导出"          	=> 'mxw4'
												),
										   "会员资料" =>array(
												 "列表" 		=> 'me3',
												 "查看" 		=> 'me4',
											),
										   "额度申请待审核" =>array(
												 "列表" 		=> 'me7',
												 "审核" 		=> 'me6',
											),
										   "银行卡管理" =>array(
												 "列表" 		=> 'me9',
												 "删除" 		=> 'me10',
											),"登录日志管理" =>array(
												 "列表" 		=> 'me11',
											),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'me1',
												'eqaction_info' =>'me3',	
												'eqaction_viewinfom'  => 'me4',
												'eqaction_infowait'  => 'me7',
												'eqaction_viewinfo'  => 'me6',
												'eqaction_doeditcredit'  => 'me6',
												'eqaction_domoneyedit'  => 'mx2',
												'eqaction_moneyedit'  => 'mx2',
												'eqaction_creditedit'  => 'mx3',
												'eqaction_dodel'    => 'mxw',
												'eqaction_edit'    => 'xmxw',
												'eqaction_doedit'    => 'xmxw',
												'eqaction_docreditedit'  => 'mx3',
												'eqaction_idcardedit'    => 'xmxw',
												'eqaction_doidcardedit'    => 'xmxw',
												'eqaction_export'  => 'mxw4',
												'eqaction_exportloan'  => 'mxw4',
												'eqaction_loanlist'=> 'mxw8',
												'eqaction_cards'=> 'me9',
												'eqaction_delcards'=> 'me10',
												'eqaction_userlog'=> 'me11',
												'eqaction_userlogexport'=> 'mxw4'
											)
							);
$acl_inc[$i]['low_leve']['common']= array( "会员详细资料" =>array(
												 "查询" 		=> 'mex5',
												 "账户通讯" 		=> 'sms1',
												 "具体通讯" 		=> 'sms2',
												 "节日通讯" 		=> 'sms3',
												 "通讯记录" 		=> 'sms4',
												),
									   "data" => array(
												'eqaction_member'  => 'mex5',
												'eqaction_communication_system'  => 'sms1',
												'eqaction_msgbyaccount'  => 'sms1',
												'eqaction_msgbyaddress'  => 'sms2',
												'eqaction_msgbygroup'  => 'sms3',
												'eqaction_msgetdata'  => 'sms4',
											)
							);
$acl_inc[$i]['low_leve']['refereedetail']= array("推荐人管理" =>array(
												 "列表" 		=> 'referee_1',
												 "导出" 		=> 'referee_2',
												),
											   "data" => array(
													//网站设置
													'eqaction_index'  => 'referee_1',
													'eqaction_export'  => 'referee_2',
												)
							);

$acl_inc[$i]['low_leve']['stockdetail']= array(
	"配资记录管理" =>array(
		"列表" 		=> 'sd1',
		"财务统计" 		=> 'sd2',
	),
	"data" => array(
		'eqaction_index'  => 'sd1',
		'eqaction_financedetail'  => 'sd2',
		'eqaction_detailgrant'  => 'sd2',
	)
);						

$acl_inc[$i]['low_leve']['jubao']= array( "举报信息" =>array(
												 "列表" 		=> 'me5',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'me5',
											)
							);

$i++;
$acl_inc[$i]['low_title'][] = '认证及申请管理';
$acl_inc[$i]['low_leve']['vipapply']= array( "VIP申请列表" =>array(
												 "列表" 		=> 'vip1',
												 "审核" 		=> 'vip2',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'vip1',
													'eqaction_edit' =>'vip2',	
													'eqaction_doedit'  => 'vip2',
												)
							);
$acl_inc[$i]['low_leve']['memberid']= array( "会员实名认证管理" =>array(
												 "列表" 		=> 'me10',
												 "审核" 		=> 'me9',
												 "添加手动认证" 		=> 'me10',
												  "导出" 		=> 'me8',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'me10',
												'eqaction_edit'  => 'me9',
												'eqaction_doedit'  => 'me9',
												'eqaction_add'  => 'me10',
												'eqaction_doadd'  => 'me10',
												'eqaction_export'  => 'me8',
											)
							);
$acl_inc[$i]['low_leve']['memberdata']= array( "会员上传资料管理" =>array(
												 "列表" 		=> 'dat1',
												 "审核" 		=> 'dat3',
												 "上传资料" 	=> 'dat4',
												 "上传展示资料" => 'dat5',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'dat1',
												'eqaction_swfupload'  => 'dat1',
												'eqaction_edit'   => 'dat3',
												'eqaction_doedit'  => 'dat3',
												
												'eqaction_upload'  => 'dat4',
												'eqaction_doupload'  => 'dat4',
												'eqaction_uploadshow'  => 'dat5',
												'eqaction_douploadshow'  => 'dat5',
											)
							);
$acl_inc[$i]['low_leve']['verifyphone']= array( "手机认证会员" =>array(
												 "列表" 		=> 'vphone1',
												 "导出" 		=> 'vphone2',
												 "审核" 		=> 'vphone3',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'   => 'vphone1',
												'eqaction_export'  => 'vphone2',
												'eqaction_edit'    => 'vphone3',	
												'eqaction_doedit'  => 'vphone3',
											)
							);
$i++;
$acl_inc[$i]['low_title'][] = '积分管理';
$acl_inc[$i]['low_leve']['market']= array( "投资积分管理" =>array(
												 "投资积分操作记录" => 'mk0',
												 "获取列表" 		=> 'mk1',
												 "获取操作" 		=> 'mk2',
												 "商城商品列表" 	=> 'mk3',
												 "商品操作" 		=> 'mk4',
												 "上传商品图片" 	=> 'mk5',
												),
											"抽奖管理" =>array(
												 "列表" 		=> 'mk6',
												 "编辑" 		=> 'mk7',
												 "删除" 		=> 'mk8',
												),
											"评论管理" =>array(
												 "列表" 		=> 'mkcom1',
												 "查看" 		=> 'mkcom2',
												 "删除" 		=> 'mkcom3',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'mk0',
													'eqaction_getlog'  => 'mk1',
													'eqaction_getlog_edit'  => 'mk2',
													'eqaction_dologedit'  => 'mk2',
													'eqaction_goods'  => 'mk3',
													'eqaction_good_edit'  => 'mk4',
													'eqaction_dogoodedit'  => 'mk4',
													'eqaction_good_del'  => 'mk4',
													'eqaction_lottery'  => 'mk6',
													'eqaction_lottery_edit'  => 'mk7',
													'eqaction_dolotteryedit'  => 'mk7',
													'eqaction_lottery_del'  => 'mk8',
													'eqaction_upload_shop_pic'  => 'mk5',
													'eqaction_comment'  => 'mkcom1',
													'eqaction_dodel'  => 'mkcom3',
													'eqaction_edit'  => 'mkcom2',
													'eqaction_doedit'  => 'mkcom2',
												)
							);

$i++;
$acl_inc[$i]['low_title'][] = '充值提现';
$acl_inc[$i]['low_leve']['paylog']= array( "充值记录" =>array(
												 "列表" 		=> 'cz',
												 "充值处理" 		=> 'czgl'
											),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'cz',
													'eqaction_paylogonline'  => 'cz',
													'eqaction_paylogoffline'  => 'cz',
													'eqaction_paylogalipay'  => 'cz',
													'eqaction_paycard'  => 'cz',
													'eqaction_cardedit'  => 'cz',
													'eqaction_edit'  => 'czgl',
													'eqaction_doedit'  => 'czgl',
													'eqaction_editalipay'  => 'czgl',
													   
												)
							);
$acl_inc[$i]['low_leve']['withdrawlog']= array("提现管理" =>array(
												 "列表" 		=> 'cg2',
												 "审核" 		=> 'cg3',
											),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'cg2',
													'eqaction_edit' =>'cg3',	
													'eqaction_doedit'  => 'cg3',
													'eqaction_withdraw0'  => 'cg2',//待提现      新增加2012-12-02 fanyelei
													'eqaction_withdraw1'  => 'cg2',//提现处理中	新增加2012-12-02 fanyelei
													'eqaction_withdraw2'  => 'cg2',//提现成功		新增加2012-12-02 fanyelei
													'eqaction_withdraw3'  => 'cg2',//提现失败		新增加2012-12-02 fanyelei
											   'eqaction_export'=>'cg2'
													
												)
							);
$acl_inc[$i]['low_title'][] = '待提现列表';
$acl_inc[$i]['low_leve']['withdrawlogwait']= array( "待提现列表" =>array(
												 "列表" 		=> 'cg4',
												 "审核" 		=> 'cg5',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'cg4',
													'eqaction_edit' =>'cg5',	
													'eqaction_doedit'  => 'cg5',
											)
							);
$acl_inc[$i]['low_title'][] = '提现处理中列表';					
$acl_inc[$i]['low_leve']['withdrawloging']= array( "提现处理中列表" =>array(
												 "列表" 		=> 'cg6',
												 "审核" 		=> 'cg7',
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'cg6',
													'eqaction_edit' =>'cg7',	
													'eqaction_doedit'  => 'cg7',
													'eqaction_export'  => 'cg7',
											)
							);
							
$i++;
$acl_inc[$i]['low_title'][] = '文章管理';
$acl_inc[$i]['low_leve']['article']= array( "文章管理" =>array(
												 "列表" 		=> 'at1',
												 "添加" 		=> 'at2',
												 "删除" 		=> 'at3',
												 "修改" 		=> 'at4',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'at1',
													'eqaction_add'  => 'at2',
													'eqaction_doadd'  => 'at2',
													'eqaction_dodel'  => 'at3',
													'eqaction_edit'  => 'at4',
													'eqaction_doedit'  => 'at4',
												)
							);
$acl_inc[$i]['low_leve']['acategory']= array("文章分类" =>array(
												 "列表" 		=> 'act1',
												 "添加" 		=> 'act2',
												 "批量添加" 	=> 'act5',
												 "删除" 		=> 'act3',
												 "修改" 		=> 'act4',
											),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'act1',
													'eqaction_listtype'  => 'act1',
													'eqaction_add'  => 'act2',
													'eqaction_doadd'  => 'act2',
													'eqaction_dodel'  => 'act3',
													'eqaction_edit'  => 'act4',
													'eqaction_doedit'  => 'act4',
													'eqaction_addmultiple'  => 'act5',
													'eqaction_doaddmul'  => 'act5',
												)
							);


/*
$i++;
$acl_inc[$i]['low_title'][] = '金融合作';
$acl_inc[$i]['low_leve']['enterprise']= array( "金融合作管理" =>array(

												 "列表" 		=> 'ent1',
												 "添加" 		=> 'ent2',
												 "删除" 		=> 'ent3',
												 "修改" 		=> 'ent4',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'ent1',
													'eqaction_select'  => 'ent1',
													'eqaction_hlist'  => 'ent1',
													'eqaction_add'  => 'ent2',
													'eqaction_doadd'  => 'ent2',
													'eqaction_del'  => 'ent3',
													'eqaction_hdel'  => 'ent3',
													'eqaction_edit'  => 'ent4',
													'eqaction_doedit'  => 'ent4',
												)
							);

$acl_inc[$i]['low_leve']['loan']= array("贷款申请管理" =>array(
												
												 "列表" 		=> 'loan1',
												 "添加" 		=> 'loan2',
												 "删除" 		=> 'loan3',
												 "修改" 		=> 'loan4',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'loan1',
													'eqaction_lists'  => 'loan1',
													'eqaction_invest'  => 'loan1',
													'eqaction_add'  => 'loan2',
													'eqaction_doadd'  => 'loan2',
													'eqaction_del'  => 'loan3',
													'eqaction_kdel'  => 'loan3',
													'eqaction_idel'  => 'loan3',
													'eqaction_edit'  => 'loan4',
													'eqaction_doedit'  => 'loan4',
												)
							);

*/
$i++;
$acl_inc[$i]['low_title'][] = '导航菜单管理';
$acl_inc[$i]['low_leve']['navigation']= array("导航菜单" =>array(
												 "列表"      => 'nav1',
												 "添加" 		=> 'nav2',
												 "批量添加" 	=> 'nav5',
												 "删除" 		=> 'nav3',
												 "修改" 		=> 'nav4',
											),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'nav1',
													'eqaction_listtype'  => 'nav1',
													'eqaction_add'  => 'nav2',
													'eqaction_doadd'  => 'nav2',
													'eqaction_dodel'  => 'nav3',
													'eqaction_edit'  => 'nav4',
													'eqaction_doedit'  => 'nav4',
													'eqaction_addmultiple'  => 'nav5',
													'eqaction_doaddmul'  => 'nav5',
												)
							);
$i++;
$acl_inc[$i]['low_title'][] = '快捷借款管理';
$acl_inc[$i]['low_leve']['feedback']= array( "快捷借款管理" =>array(
												 "列表" 		=> 'msg1',
												 "查看" 		=> 'msg2',
												 "删除" 		=> 'msg3',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'msg1',
													'eqaction_dodel'  => 'msg3',
													'eqaction_edit'  => 'msg2',
												)
							);
$i++;
$acl_inc[$i]['low_title'][] = '资金统计';
$acl_inc[$i]['low_leve']['capitalaccount']= array( "会员帐户" =>array(
												 "列表" 		=> 'capital_1',
												 "导出" 		=> 'capital_2',
												),"优投网统计" =>array(
												 "列表" 		=> 'capital_3',
												 "导出" 		=> 'capital_4',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'capital_1',
													'eqaction_export'  => 'capital_2',
													'eqaction_yott'  => 'capital_3',
													'eqaction_exportyott'  => 'capital_4',
												)
							);
$acl_inc[$i]['low_leve']['capitalonline']= array("充值记录" =>array(
												 "列表" 		=> 'capital_3',
												 "导出" 		=> 'capital_4',
												),
											   "提现记录" =>array(
													 "列表" 		=> 'capital_5',
													 "导出" 		=> 'capital_6',
												),
											   "data" => array(
													//网站设置
													'eqaction_charge'  => 'capital_3',
													'eqaction_withdraw'  => 'capital_5',
													'eqaction_chargeexport'  => 'capital_4',
													'eqaction_withdrawexport'  => 'capital_6',
												)
							);
$acl_inc[$i]['low_leve']['remark']= array( "备注信息" =>array(
												 "列表" 		=> 'rm1',
												 "增加" 		=> 'rm2',
												 "修改" 		=> 'rm3',
												),
									   "data" => array(
												'eqaction_index'  => 'rm1',
												'eqaction_add'    => 'rm2',
												'eqaction_doadd'    => 'rm2',
												'eqaction_edit'    => 'rm3',
												'eqaction_doedit'    => 'rm3',
											)
							);
$acl_inc[$i]['low_leve']['capitaldetail']= array("会员资金记录" =>array(
												 "列表" 		=> 'capital_7',
												 "导出" 		=> 'capital_8',
												),
											   "data" => array(
													//网站设置
													'eqaction_index'  => 'capital_7',
													'eqaction_export'  => 'capital_8',
												)
							);
$acl_inc[$i]['low_leve']['capitalall']= array("网站资金统计" =>array(
												 "查看" 		=> 'capital_9',
												),
											   "data" => array(
													//网站设置
													'eqaction_index'  => 'capital_9',
												)
							);
//权限管理
$i++;
$acl_inc[$i]['low_title'][] = '权限管理';
$acl_inc[$i]['low_leve']['acl']= array( "权限管理" =>array(
												 "列表" 		=> 'at73',
												 "增加" 		=> 'at74',
												 "删除" 		=> 'at75',
												 "修改" 		=> 'at76',
												),
										   "data" => array(
										   		//权限管理
												'eqaction_index'  => 'at73',
												'eqaction_doadd'    => 'at74',
												'eqaction_add'    => 'at74',
												'eqaction_dodelete'    => 'at75',
												'eqaction_doedit'   => 'at76',
												'eqaction_edit'   	=> 'at76',
											)
							);
//管理员管理
$i++;
$acl_inc[$i]['low_title'][] = '管理员管理';
$acl_inc[$i]['low_leve']['adminuser']= array( "管理员管理" =>array(
												 "列表" 		=> 'at77',
												 "增加" 		=> 'at78',
												 "删除" 		=> 'at79',
												 "上传头像"	=> 'at99',
												 "修改" 		=> 'at80',
												),
										   	  "data" => array(
										   		//权限管理
												'eqaction_index'  => 'at77',
												'eqaction_dodelete'    => 'at79',
												'eqaction_header'    => 'at99',
												'eqaction_memberheaderuplad'    => 'at99',
												'eqaction_addadmin' =>array(
																'at78'=>array(//增加
																	'POST'=>array(
																		"uid"=>'G_NOTSET',
																	),
																 ),	
																'at80'=>array(//修改
																	'POST'=>array(
																		"uid"=>'G_ISSET',
																	),
																 ),	
												),
											)
							);
//权限管理
$i++;
$acl_inc[$i]['low_title'][] = '数据库管理';
$acl_inc[$i]['low_leve']['db']= array( "数据库信息" =>array(
												 "查看" 		=> 'db1',
												 "备份" 		=> 'db2',
												 "查看表结构" => 'db3',
												),
									   "数据库备份管理" =>array(
											 "备份列表" 		=> 'db4',
											 "删除备份" 		=> 'db5',
											 "恢复备份" 		=> 'db6',
											 "打包下载" 		=> 'db7',
										),
									   "清空数据" =>array(
											 "清空数据" 		=> 'db8',
										),
										   "data" => array(
										   		//权限管理
												'eqaction_index'  => 'db1',
												'eqaction_set'  => 'db2',
												'eqaction_backup'  => 'db2',
												'eqaction_showtable'  => 'db3',
												'eqaction_baklist'  => 'db4',
												'eqaction_delbak'  => 'db5',
												'eqaction_restore'  => 'db6',
												'eqaction_dozip'  => 'db7',
												'eqaction_downzip'  => 'db7',
												'eqaction_truncate'  => 'db8',
											)
							);
$i++;
$acl_inc[$i]['low_title'][] = '图片上传';
$acl_inc[$i]['low_leve']['kissy']= array( "图片上传" =>array(
												 "图片上传" 		=> 'at81',
												),
										   	  "data" => array(
										   		//权限管理
												'eqaction_index'  => 'at81',
											  )
							);


$i++;
$acl_inc[$i]['low_title'][] = '扩展管理';
$acl_inc[$i]['low_leve']['scan']= array( "安全检测" =>array(
                                                 "安全检测"         => 'scan1',
                                                ),
                                                 "data" => array(
                                                   //权限管理
                                                'eqaction_index'  => 'scan1',
                                                'eqaction_scancom'=>'scan1',
                                                'eqaction_updateconfig'=>'scan1',
                                                'eqaction_filefilter'  => 'scan1',
                                                'eqaction_filefunc' =>'scan1',
                                                'eqaction_filecode' =>'scan1',
                                                'eqaction_scanreport'=>'scan1',
                                                'eqaction_view'=>'scan1',
                                              )
                            );
$acl_inc[$i]['low_leve']['mfields']= array( "文件管理" =>array(
												 "文件管理" 		=> 'at82',
												 "空间检查"					=>'at83',
												),
										   	  "data" => array(
										   		//文件管理
												'eqaction_index'  => 'at82',
												'eqaction_checksize'  => 'at83',
											  )
							);

$acl_inc[$i]['low_leve']['bconfig']= array( "业务参数管理" =>array(
												 "查看" 		=> 'fb1',
												 "修改" 		=> 'fb2',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'fb1',
												'eqaction_save'    => 'fb2',
											)
							);
$acl_inc[$i]['low_leve']['leve']= array( "信用级别管理" =>array(
												 "查看" 		=> 'jb1',
												 "修改" 		=> 'jb2',
												),
										 "投资级别管理" =>array(
												 "查看" 		=> 'jb3',
												 "修改" 		=> 'jb4',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'jb1',
												'eqaction_save'    => 'jb2',
												'eqaction_invest'    => 'jb3',
												'eqaction_investsave'  => 'jb4',
											)
							);
$acl_inc[$i]['low_leve']['age']= array( "会员年龄别称" =>array(
												 "查看" 		=> 'bc1',
												 "修改" 		=> 'bc2',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'bc1',
												'eqaction_save'    => 'bc2',
											)
							);

$acl_inc[$i]['low_leve']['id5']= array( "ID5身份认证" =>array(
												 "ID认证" 		=> 'id5',
					
												),
										   "data" => array(
												'eqaction_index'=>'id5', 
												'eqaction_save'=>'id5',
											)
							);
$acl_inc[$i]['low_leve']['hetong']= array( "合同居间方资料上传管理" =>array(
												 "查看" 		=> 'ht1',
												 "上传"			=>	'ht2',
												),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'ht1',
												'eqaction_upload'  =>'ht2',
											)
							);
$acl_inc[$i]['low_title'][] = '在线客服管理';
$acl_inc[$i]['low_leve']['qq']= array("QQ客服管理" =>array(
												 "列表" 		=> 'qq5',
												 "增加" 		=> 'qq6',
												 "删除" 		=> 'qq7',
												 
												),
									  "QQ群管理" =>array(
												 "列表" 		=> 'qun5',
												 "增加" 		=> 'qun6',
												 "删除" 		=> 'qun7',
												 
												),
									  "客服电话管理" =>array(
												 "列表" 		=> 'tel5',
												 "增加" 		=> 'tel6',
												 "删除" 		=> 'tel7',
												 
												),
									   "data" => array(
										   		//网站设置
												'eqaction_index'   => 'qq5',
												'eqaction_addqq'   => 'qq6',
												'eqaction_dodeleteqq'    => 'qq7',
												'eqaction_qun'   => 'qun5',
												'eqaction_addqun'   => 'qun6',
												'eqaction_dodeletequn'    => 'qun7',
												'eqaction_tel'   => 'tel5',
												'eqaction_addtel'   => 'tel6',
												'eqaction_dodeletetel'    => 'tel7',
											
											)
							);

//$acl_inc[$i]['low_title'][] = '在线通知管理';
$acl_inc[$i]['low_leve']['payonline']= array( "PC支付接口管理" =>array(
												 "查看" 		=> 'jk1',
												 "修改" 		=> 'jk2',
												),
										"wap支付接口管理" =>array(
											"查看" 		=> 'jk3',
											"修改" 		=> 'jk4',
										),
										"data" => array(
											//网站设置
											//网站设置
											'eqaction_index'  => 'jk1',
											'eqaction_save'    => 'jk2',
											'eqaction_wap'  => 'jk3',
											'eqaction_wapsave'    => 'jk4',
											'eqaction_banklist'    => 'jk4',
											'eqaction_savebank'    => 'jk4',
											'eqaction_addbank'    => 'jk4',
											'eqaction_delbank'    => 'jk4',
											'eqaction_uploadxls'    => 'jk4',
										)
							);
$acl_inc[$i]['low_leve']['payoffline']= array( "线下充值银行管理" =>array(
												 "查看" 		=> 'offline1',
												 "修改" 		=> 'offline2',
												),
										   "data" => array(
													//网站设置
													'eqaction_index'  => 'offline1',
													'eqaction_saveconfig' => 'offline2',    
												)
							);
$acl_inc[$i]['low_leve']['msgonline']= array( "通知信息接口管理" =>array(
												 "查看" 		=> 'jk3',
												 "修改" 		=> 'jk4',
												),
											 "通知信息模板管理" =>array(
												 "查看" 		=> 'jk5',
												 "修改" 		=> 'jk6',
											),
									   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'jk3',
												'eqaction_save'    => 'jk4',
												'eqaction_templet'  => 'jk5',
												'eqaction_templetsave'    => 'jk6',
											)
							);
							
$acl_inc[$i]['low_leve']['baidupush']= array( "百度云推送" =>array(
												 "首页" 		=> 'bd27',
												 "消息推送"     => 'bd26',
											 ),
										   "data" => array(
										   		//网站设置
												'eqaction_index'  => 'bd27',
												'eqaction_push_message_android'=>'bd26',
											)
							);
?>