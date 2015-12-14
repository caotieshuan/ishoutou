<?php
// 本类由系统自动生成，仅供测试用途
class BankAction extends MCommonAction {
    public function checkloan()
    {
        $msg = '';
        $url = '';
        $status = M('members_status')->field('*')->where("uid={$this->uid}")->find();
        /*if($status['email_status']!=1){
            $msg = "请点击确定完成邮箱验证";
            $url = '/member/verify#fragment-1';    
        }else*/
        if($status['phone_status']!=1){
            $msg = "请点击确定完成手机验证";
            $url = '/M/verify/cellphone';
        }elseif($status['id_status']!=1){
            $msg = "请点击确定完成实名认证";
            $url = '/M/verify/idcard';
        }
        
        if(!$msg){
            $escrow = M('escrow_account')->field('qdd_marked')->where("uid={$this->uid}")->find();
            if(!$escrow['qdd_marked']){
                $url = U('/M/bank/bindingAccount'); 
                $msg = "点击确定完成绑定托管";
            }else{
                $msg = 'ok';
            }
        }
        echo json_encode(array('msg'=>$msg, 'url'=>$url));   
    }

    /**
    * 绑定乾多多账号
    * 
    */
    public function  bindingAccount()
    {
        header("Content-type:text/html;charset=utf-8");
        $status = M('members_status')->field('*')->where("uid={$this->uid}")->find();
        //$status['email_status']!=1 &&  $this->error('请先认证邮箱再来绑定托管账户', '/member/verify#fragment-1');
        $status['phone_status']!=1 &&  $this->error('请先认证手机号再来绑定托管账户', '/M/verify/cellphone');
        $status['id_status']!=1 &&  $this->error('请先实名认证再来绑定托管账户', '/M/verify/idcard');
        
        if(M('escrow_account')->where("uid={$this->uid}")->count('uid')){
             $this->error('您已经绑定了托管账户，无需重复绑定', '/member.html');   
        }
        
        $user_info = M('members')->field('user_name, user_email, user_phone')->where("id={$this->uid}")->find();
        $id_info = M("member_info")->field('idcard, real_name')->where("uid={$this->uid}")->find();
        import("ORG.Loan.Escrow");
        $loan = new Escrow();

        $data =  $loan->wapregisterAccount($user_info['user_phone'], $user_info['user_email'], $id_info['real_name'], $id_info['idcard'],$user_info['user_name']);
        $form =  $loan->setForm($data, 'register');
        echo $form;
        exit; 

    }
     /**
    * 绑定乾多多返回地址
    * 
    */
    public function bindReturn()
    {
        //dump($_POST);die;
        $lang = L('Binding');
        $msg = $lang[$_POST['ResultCode']];
        $_POST['ResultCode']==88 && $msg = "成功绑定托管账户！";
        $this->success($msg,"/M/index");
        // $this->assign('msg', $msg);
        // $this->display();
        
    }
        public function bindbank(){
        $area=$_POST['cityName'];
        $map['cityname'] = array('LIKE',"%".$area."%"); 
        $map['id']=array('between',"1001,1345");
        $city=M('cityinfo')->field('id')->where($map)->find();
        
        $pr=$_POST['province'];
        $map1['cityname'] = array('LIKE',"%".$pr."%"); 
        $map1['id']=array('between',"1,34");
        $province=M('cityinfo')->field('id')->where($map1)->find();
        
        
        $bank_info = M('member_banks')->field("uid, bank_num")->where("uid=".$this->uid)->find();
        
        !$bank_info['uid'] && $data['uid'] = $this->uid;
        $data['bank_num'] = text($_POST['account']);
        $data['bank_name'] = text($_POST['bankname']);
        $data['bank_address'] = text($_POST['bankaddress']);
        $data['bank_province'] =$province['id'];
        
        $data['bank_city'] =$city['id'];
        
        $data['add_ip'] = get_client_ip();
        $data['add_time'] = time();
        
        if($bank_info['uid']){
            /////////////////////新增银行卡修改锁定开关 开始 20130510 fans///////////////////////////
            if(intval($this->glo['edit_bank'])!= 1 && $bank_info['bank_num']){
                ajaxmsg("为了您的帐户资金安全，银行卡已锁定，如需修改，请联系客服", 0 );
            }
            /////////////////////新增银行卡修改锁定开关 结束 20130510 fans///////////////////////////
            $old = text($_POST['oldaccount']);
            if($bank_info['bank_num'] && $old <> $bank_info['bank_num']) ajaxmsg('原银卡号不对',0);
            $newid = M('member_banks')->where("uid=".$this->uid)->save($data);
        }else{
            $newid = M('member_banks')->add($data);
        }
        if($newid){
            MTip('chk2',$this->uid);
            ajaxmsg();
        }
        else ajaxmsg('操作失败，请重试',0);
    }
}