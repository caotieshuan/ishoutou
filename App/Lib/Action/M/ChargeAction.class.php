<?php
/**
* 托管第三方充值控制器类
*/
class ChargeAction extends MCommonAction {

    public function index(){
        $this->display();
    }

    public function allcharge(){
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

    public function charge(){
        $map['uid'] = $this->uid;
        $account_money = M('member_money')->where($map)->find();
        $balance = $account_money['account_money']+$account_money['back_money'];
        $this->assign('balance', $balance);
        $this->assign("account_money",$account_money);
        $this->assign("payConfig",FS("Webconfig/payconfig"));
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }
      public function hkcharge(){
        $map['uid'] = $this->uid;
        $account_money = M('member_money')->where($map)->find();
        $balance = $account_money['account_money']+$account_money['back_money'];
        $this->assign('balance', $balance);
        $this->assign("account_money",$account_money);
        $this->assign("payConfig",FS("Webconfig/payconfig"));
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }
    
    public function moneymmcharge(){
        import("ORG.Loan.Escrow");
        $charge = new Escrow();
        $data = M('escrow_account')->field('*')->where('uid='.$this->uid)->find();
        if(!$data){
            $this->error('请先绑定托管账户', '/member/bank#fragment-2');
        }
        $add = array(    //同时添加到 member_payonline 一条临时数据 状态 issuccess = 0
            'uid'=> $this->uid,
            'add_time'=>time(),
            'money' => $_POST['money'],
        );
        $id = M("member_payonline")->add($add);//利用返回id 查找表自动生成的 orderno(平台充值单号)
        if(!$id){
            $this->error('订单生成出错！');
        }
        $orders = date("YmdHi").$id;
        $Amount  = floatval($_POST['money']);
        $ReturnURL = 'http://'.$_SERVER['HTTP_HOST'].U('/member/charge/wapchargeReturn'); 
        $NotifyURL = 'http://'.$_SERVER['HTTP_HOST'].U("/member/Notify/charge");
        $RechargeType ='2';
        $FeeType='1';
        //dump($data['platform_marked']);die;
        $array = $charge->Moneymoremorecharge($data['qdd_marked'], $data['platform_marked'], $orders, $Amount, $ReturnURL, $NotifyURL,$RechargeType,$FeeType);
        
       

        file_put_contents("C:\www\tsx\App\Runtime\a.txt",json_encode($array));
        $form  = $charge->setForm($array, 'charge');     
        echo $form;
        exit; 
    }
    //汇款充值
    // public function moneymmcharge1(){
    //     import("ORG.Loan.Escrow");
    //     $charge = new Escrow();
    //     $data = M('escrow_account')->field('*')->where('uid='.$this->uid)->find();
    //     if(!$data){
    //         $this->error('请先绑定托管账户', '/member/bank#fragment-1');
    //     }
    //     $add = array(    //同时添加到 member_payonline 一条临时数据 状态 issuccess = 0
    //         'uid'=> $this->uid,
    //         'add_time'=>time(),
    //         'money' => $_POST['money'],
    //     );
    //     $id = M("member_payonline")->add($add);//利用返回id 查找表自动生成的 orderno(平台充值单号)
    //     if(!$id){
    //         $this->error('订单生成出错！');
    //     }
    //     $orders = date("YmdHi").$id;
    //     //dump($_POST);die;
    //     $Amount  = floatval($_POST['money']);
    //     $ReturnURL = 'http://'.$_SERVER['HTTP_HOST'].U('member/charge/chargeReturn'); 
    //     $NotifyURL = 'http://'.$_SERVER['HTTP_HOST'].U("member/Notify/charges");
        
    //     //dump($data['platform_marked']);die;
    //     $RechargeType = 3;
    //     $FeeType = 1;
    //     $array = $charge->Moneymoremorecharge($data['qdd_marked'], $data['platform_marked'], $orders, $Amount, $ReturnURL, $NotifyURL,$RechargeType,$FeeType);
        
        
    //     $form  = $charge->setForm($array, 'charge');     
    //     echo $form;
    //     exit; 
    // }
    // public function chargeReturn()
    // {   
    //     $msg = L($_REQUEST['ResultCode']);
    //     if($_REQUEST['ResultCode']==88){
    //         $msg = "充值成功";
    //     }
    //    // $this->success($msg,"/member/Capital/index");
    //     $this->success($msg,"/M/Capital/index");
    //     echo "chengong";
       
    // }
    
    public function chargelog(){
        $map['uid'] = $this->uid;
        
        if($_GET['start_time']&&$_GET['end_time']){
            $_GET['start_time'] = strtotime($_GET['start_time']." 00:00:00");
            $_GET['end_time'] = strtotime($_GET['end_time']." 23:59:59");
            
            if($_GET['start_time']<$_GET['end_time']){
                $map['add_time']=array("between","{$_GET['start_time']},{$_GET['end_time']}");
                $search['start_time'] = $_GET['start_time'];
                $search['end_time'] = $_GET['end_time'];
            }
        }
        $list = getChargeLog($map,10);
        $this->assign('search',$search);
        $this->assign("list",$list['list']);
        $this->assign("pagebar",$list['page']);
        $this->assign("success_money",$list['success_money']);
        $this->assign("fail_money",$list['fail_money']);
        
        $data['html'] = $this->fetch();
        exit(json_encode($data));
    }

}