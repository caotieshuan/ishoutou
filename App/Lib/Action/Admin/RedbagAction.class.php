<?php
// 全局设置
class RedbagAction extends ACommonAction
{
    protected $num=1;
    public function index(){
        import("ORG.Util.Page");
        $count = M('redbag')->count();
        $p = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        $list=M('redbag')->field('*')->order('id desc')->limit($Lsql)->select();

        foreach($list as &$val){
            $lastnum = M('redbag_list')->where('uid=0 and pid='.$val['id'])->count();
            $val['lastnum'] = (int)$lastnum;
            $val['usenum'] = (int)($val['bonus_count']-$lastnum);
            $val['pusenum'] = M('redbag_list')->where('pid='.$val['id'].' and redtype=1')->count();
            $val['plastnum'] =(int)($val['prize_num']-$val['pusenum']);
        }
        $this->assign('page',$page);
        $this->assign('isadd',M('redbag')->where('status=0')->count());
        $this->assign('list',$list);
        $this->display();
    }

public function lists(){
        $id = (int)$_GET['id'];
        $redbag = M('redbag')->find($id);
        if(empty($redbag)){
            $this->error('红包活动不存在，请刷新后重试');
        }
        $map=array();
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['r.usetime'] = array("between",$timespan);
            $search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));  
            $search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));  
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['r.usetime'] = array("gt",$xtime);
            $search['start_time'] = $xtime; 
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['r.usetime'] = array("lt",$xtime);
            $search['end_time'] = $xtime;   
        }
        if(!empty($_REQUEST['username'])){
            $map['m.user_name'] = $_REQUEST['username'];
            $search['username'] = $_REQUEST['username'];   
        }
		$map['r.pid'] = $id;
        $search['pid'] = $id; 
        import("ORG.Util.Page");
       // $count = M('redbag_list')->where($map)->count();
         $count=M('redbag_list r')->join('lzh_members m ON r.uid=m.id')->where($map)->count();
        $p = new Page($count, 50);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";


        //$totals = M('redbag_list')->field(' money,count(id) as ss ')->order('money asc')->where('pid='.$id)->group('money')->select();
        $lasts = M('redbag_list')->field(' money,count(id) as ss ')->order('money asc')->where('pid='.$id.' and status=2')->group('money')->select();


        $total = 0;
        $newarr = array();
        foreach($lasts as &$val){
            if(0 < $val['money']){
                $total += $val['ss'];
                $newarr[]=array('money'=>$val['money'],'num'=>$val['ss']);
            }
        }

        
        $list=M('redbag_list r')->join('lzh_members m ON r.uid=m.id')->where($map)->field('r.*,m.user_name')->order('r.id asc')->limit($Lsql)->select();
        $this->assign('pagebar',$page);
        $this->assign('total',$total);
        $this->assign('newarr',$newarr);
        $this->assign('redbag',$redbag);
        $this->assign('list',$list);
        $this->assign('search',$search);
		$this->assign("query", http_build_query($search));
        $this->display();
    }
public function export(){
	ini_set("memory_limit","-1");
	set_time_limit (0);
		import("ORG.Io.Excel");
		 $id = (int)$_REQUEST['pid'];
		$this->pre = C('DB_PREFIX');
		 $map=array();
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = (urldecode($_REQUEST['start_time'])).",".(urldecode($_REQUEST['end_time']));
            $map['r.usetime'] = array("between",$timespan);
            $search['start_time'] = strtotime(urldecode($_REQUEST['start_time']));  
            $search['end_time'] = strtotime(urldecode($_REQUEST['end_time']));  
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = (urldecode($_REQUEST['start_time']));
            $map['r.usetime'] = array("gt",$xtime);
            $search['start_time'] = $xtime; 
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = (urldecode($_REQUEST['end_time']));
            $map['r.usetime'] = array("lt",$xtime);
            $search['end_time'] = $xtime;   
        }
        if(!empty($_REQUEST['username'])){
            $map['m.user_name'] = $_REQUEST['username'];
            $search['username'] = $_REQUEST['username'];   
        }
		$map['r.pid'] = $id;
        $search['pid'] = $id; 
		import("ORG.Util.Page");
		$count=M('redbag_list r')->join('lzh_members m ON r.uid=m.id')->where($map)->count();

        $p = new Page($count, 50);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";


       
        $list=M('redbag_list r')->join('lzh_members m ON r.uid=m.id')->where($map)->field('r.*,m.user_name')->order('r.id asc')->select();

		$row=array();
		$row[0]=array('序号','红包金额	','创建时间','是否被领取','领取人用户','领取时间');
		$i=1;
		foreach($list as $v){
				$row[$i]['i'] = $i;
				$row[$i]['money'] = $v['money'];
				$row[$i]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
				if(!empty($v['uid'])){
					$row[$i]['ifreceive'] = '已领取';	
				}else{
					$row[$i]['ifreceive'] = '未领取';
				}
				$row[$i]['user_name'] = $v['user_name'];
				if(!empty($v['usetime'])){
					$row[$i]['usetime'] = date('Y-m-d H:i:s',$v['usetime']);
				}else{
					$row[$i]['usetime'] = '';
				}
				$i++;
		}
		
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("redbag");
	}


    public function redChange(){
        $id = (int)$_GET['id'];
        if(!$id){
            $this->error('参数错误');
        }

        $row = M('redbag')->find($id);

        if(empty($row)){
            $this->error('数据不存在，请刷新后重试');
        }

        if(2 == $row['status']){
            $this->error('本次红包活动一结束');
        }


        $status = 1 == $row['status'] ? 2 : 1;

        if(1 == $status){
            if(M('redbag')->where('status=1')->count()){
                $this->error('另外一个活动正在执行。不能开启本活动');
            }
        }

        $res = M('redbag_list')->where('pid='.$id)->save(array('status'=>$status));
        //if($res){
            $rr = M('redbag')->where('id='.$id)->save(array('status'=>$status));
        //}
        //if($rr){//成功提示
            $this->assign('jumpUrl', __URL__."/index");
            $this->success(L('修改成功'));
        //}else{
         //   $this->error('请勿重复更新');
        //}
    }

    public function setredbag(){
        $this->display();
    }


    public function preview(){
        ini_set('memory_limit','512M');
        $type = $_GET['type'];
        if('detailed' == $type){


        }else{

            $json = json_decode($_GET['json']);


            $bonus_total = floatval($json->total);
            $bonus_count = floatval($json->count);
            $bonus_max   = floatval($json->maxval);
            $bonus_min   = floatval($json->minval);
            $prize_max   = floatval($json->maxprize);
            $prize_num   = floatval($json->numprize);



            if($prize_max && $prize_num){
                $bonus_total -= ($prize_max*$prize_num);
            }



            $result_bonus = $this->getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min);

            $total = 0;
            $arr = array();
            foreach ($result_bonus as $key => $value) {
                $total  +=$value;
                if(isset($arr[$value])){
                    $arr[$value] += 1;
                }else{
                    $arr[$value] = 1;
                }
            }
            ksort($arr);
            $_SESSION['redbag'] = array(
                'total'=>$total,
                'bonus_total'=>$bonus_total,
                'bonus_count'=>$bonus_count,
                'bonus_max'=>$bonus_max,
                'bonus_min'=>$bonus_min,
                'prize_max'=>$prize_max,
                'prize_num'=>$prize_num,
                'arr' => $arr,
                'list'=>$result_bonus
            );
            $this->assign('bonus_count',$bonus_count);
            $this->assign('prize_max',$prize_max);
            $this->assign('prize_num',$prize_num);
            $this->assign('total',$total);
            $this->assign('list',$arr);
            $this->assign('blist',$result_bonus);

            $this->display();
        }
    }

    public function savepreview(){
        $redbag = M('redbag');
        $dataname = C('DB_NAME');
        $db_host = C('DB_HOST');
        $db_user = C('DB_USER');
        $db_pwd = C('DB_PWD');
        if($_SESSION['redbag']['total'] != $_SESSION['redbag']['bonus_total']){
            ajaxmsg('生成总数和预期总数不相等，请刷新方案重试',0);
        }
        $redbag->where('status=0')->delete();
        $pid = $redbag->add(
            array(
                'bonus_total'=>$_SESSION['redbag']['bonus_total'],
                'bonus_count'=>$_SESSION['redbag']['bonus_count'],
                'bonus_max'=>$_SESSION['redbag']['bonus_max'],
                'bonus_min'=>$_SESSION['redbag']['bonus_min'],
                'prize_max'=>$_SESSION['redbag']['prize_max'],
                'prize_num'=>$_SESSION['redbag']['prize_num'],
                'arr'=>json_encode($_SESSION['redbag']['arr']),
                'lists'=>json_encode($_SESSION['redbag']['list']),
                'status'=>0
            )
        );
        if($pid){
            $dbh = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
            try{
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->beginTransaction();//开启事务
                $dbh->exec('delete from '.C('DB_PREFIX').'redbag_list where status=0');
                foreach($_SESSION['redbag']['list'] as $val){
                    $dbh->exec('insert '.C("DB_PREFIX")."redbag_list (money,uid,pid,status,addtime) values ('".$val."',0,'".$pid."',0,".time().")");
                }
                if($dbh->commit()){//提交事务
                    ajaxmsg('添加成功');
                }else{
                    ajaxmsg('添加失败');
                }
            }catch(Exception$e){
                $dbh->rollBack();//错误回滚
                ajaxmsg("Failed:".$e->getMessage(),0);
            }
        }else{
            ajaxmsg('创建失败',0);
        }
    }

    public function getprizetemp(){
        $prize = array(
            1=>array(
                'minval'  => floatval($_POST['prize_1_minval']),
                'maxval' => floatval($_POST['prize_1_maxval']),
                'chance' => floatval($_POST['prize_1_chance'])
            ),
            2=>array(
                'minval'  => floatval($_POST['prize_2_minval']),
                'maxval' => floatval($_POST['prize_2_maxval']),
                'chance' => floatval($_POST['prize_2_chance'])
            ),
            3=>array(
                'minval'  => floatval($_POST['prize_3_minval']),
                'maxval' => floatval($_POST['prize_3_maxval']),
                'chance' => floatval($_POST['prize_3_chance'])
            ),
        );
        return $prize;
    }
    public function test(){

        $bonus_total = 200000;
        $bonus_count = 40000;
        $bonus_max = 100;//此算法要求设置的最大值要大于平均值
        $bonus_min = 3;
        $result_bonus = $this->getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min);
        $total_money = 0;
        $arr = array();
        foreach ($result_bonus as $key => $value) {
            $total_money += $value;
            if(isset($arr[$value])){
                $arr[$value] += 1;
            }else{
                $arr[$value] = 1;
            }
        }
        echo '<pre>';
//输出总钱数，查看是否与设置的总数相同
        echo $total_money;
//输出所有随机红包值
        print_r($result_bonus);
//统计每个钱数的红包数量，检查是否接近正态分布
        ksort($arr);
        print_r($arr);
        exit;
    }

    /**
     * 求一个数的平方
     * @param $n
     */
    function sqr($n){
        return $n*$n;
    }

    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     */
    function xRandom($bonus_min,$bonus_max){
        $sqr = intval($this->sqr($bonus_max-$bonus_min));
        $rand_num = rand(0, ($sqr+rand(1,10)));
        return intval(sqrt($rand_num));
    }


    /**
     *
     * @param $bonus_total 红包总额
     * @param $bonus_count 红包个数
     * @param $bonus_max 每个小红包的最大额
     * @param $bonus_min 每个小红包的最小额
     * @return 存放生成的每个小红包的值的一维数组
     */
    function getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min) {
        $result = array();

        $average = $bonus_total / $bonus_count;

        for ($i = 0; $i < $bonus_count; $i++) {
            //因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            //当随机数>平均值，则产生小红包
            //当随机数<平均值，则产生大红包
            if (rand($bonus_min, $bonus_max) > $average) {
                // 在平均线上减钱
                if(rand(1,1000)){
                    $temp = $bonus_min + $this->xRandom($bonus_min, $average);
                }else{
                    $temp = $bonus_min + $this->xRandom($bonus_min, $average);
                }

                if($temp == 4 || $temp == 13 || $temp == 14 ){
                    if($temp == 13){
                        $temp++ ;
                        $temp++ ;
                    }else{
                        $temp ++ ;
                    }
                }

                $result[$i] = $temp;
                $bonus_total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = $bonus_max - $this->xRandom($average, $bonus_max);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            }
        }

        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($bonus_total > 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                    $result[$i]++;
                    $bonus_total--;

                }
                if($result[$i] == 4){
                    $result[$i]++;
                    $bonus_total--;
                }else if($result[$i] == 13){
                    $result[$i] = $result[$i] + 2;
                    $bonus_total = $bonus_total -2;
                }else if($result[$i] == 14){
                    $result[$i]++;
                    $bonus_total--;
                }
            }
        }
        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($bonus_total < 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                    $result[$i]--;
                    $bonus_total++;
                }
                if($result[$i] == 4){
                    $result[$i]--;
                    $bonus_total++;
                }else if($result[$i] == 13){
                    $result[$i] = 8;
                    $bonus_total = $bonus_total + 5;
                }else if($result[$i] == 14){
                    $result[$i] = 8;
                    $bonus_total = $bonus_total + 6;
                }
            }
        }
        $this->num++;
        return $result;
    }
}