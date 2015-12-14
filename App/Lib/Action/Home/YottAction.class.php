<?php
// 本类由系统自动生成，仅供测试用途
class YottAction extends HCommonAction {

    protected $sign;

    public function _initialize(){
        import('App.Api.yott');
        $this->getSign();
    }
    public function index(){

        $this->isLogin();
        $this->getUserName();
        $this->display();
    }

    private function getUserName()
    {
        $user = $this->sign['name'];
        if(empty($user)) return false;
        if(!M('members')->where(array('user_name'=>$user))->count()){
            $this->assign('nicename',$user);
        }else if(!M('members')->where(array('user_name'=>'yott_'.$user))->count()){
            $this->assign('nicename','yott_'.$user);
        }else{
            $i = 1;
            while(!M('members')->where(array('user_name'=>'yott_'.$user.$i))->count()){
                $i++;
            }
            $this->assign('nicename','yott_'.$user.$i);
        }
    }

    public function register()
    {
        $arr = $this->sign;
        if(!self::isMobile($arr['phone'])){
            ajaxmsg(json_encode($arr),0);
        };
        $username = text($_POST['txtUser']);
        if($_POST['txtPwd'] != $_POST['txtRepwd']){
            ajaxmsg('两次密码输入的不一致！',0);
        }
        if(M('members')->where(array('user_name'=>array('like',$username)))->count()){
            ajaxmsg($username.'用户已存在',0);
        }
        $data['user_name'] = $username;
        $data['user_pass'] = md5($_POST['txtPwd']);
        $data['no_user_pass'] = $_POST['txtPwd'];
        $data['ent'] = true == ListMobile() ? 1 : 0;
        $data['reg_time'] = time();
        $data['reg_ip'] = get_client_ip();
        $data['user_phone'] = $arr['phone'];
        $data['last_log_time'] = time();
        $data['last_log_ip'] = get_client_ip();
        //注册奖励
        $get_data = M('global')->field("text")->where("code = 'is_reward'")->find();
        $is_new = $get_data['text'];
        if($is_new == '1'){
            $data['is_new'] = 1;
        }
        $mid = M('members')->add($data);
        if($mid){
            M('member_yott')->add(
                array(
                    'uid'=>$mid,
                    'startdate'=>time(),
                    'startip'=>sprintf("%u", ip2long(get_client_ip())),
                    'status'=>1,//这里如果是一存在的手机号码就返回2
                    'yott_name'=>$arr['name']
                )
            );
        }
        $this->updateUserInfo($mid);
        setMemberStatus($mid, 'phone', 1, 10, '手机');

        $yott = new yott();
        $yott->bindUser($username,$arr['phone']);

        $this->yottLogin($mid);
        ajaxmsg(array('message'=>'绑定成功','redirect'=>'/invest/'.$arr['identify'].'.html'));
    }

    protected function updateUserInfo($newid){
        if($newid){
            $arr = $this->sign;
            $temp = M('members_status') -> where("uid={$newid}") -> find();
            if(is_array($temp)){
                $cid['phone_status'] = 1;
                M('members_status') -> where("uid={$newid}") -> save($cid);
            }else{
                $dt['uid'] = $newid;
                $dt['phone_status'] = 1;
                M('members_status') -> add($dt);
            }
            $updata['cell_phone'] = $arr['phone'];
            $b = M('member_info')->where("uid = {$newid}")->count('uid');
            if ($b == 1){
                M("member_info")->where("uid = {$newid}")->save($updata);
            }else{
                $updata['uid'] = $newid;
                $updata['cell_phone'] = $arr['phone'];
                M('member_info')->add($updata);
            }
            return $newid;
        }
    }


    //验证是否是手机号
    protected static function isMobile($m){
        return preg_match("/^1[0-9]{10}$/",$m);
    }

    private function isLogin(){

        $uid = M('member_yott')->where(array('yott_name'=>$this->sign['name']))->getField('id');
        if($uid){
            $this->yottLogin($uid) &&  self::redirect('/invest/'.$this->sign['identify']);
            return '';
        }
        //判断手机号是否存在
        $user = M('members')->where('user_phone="'.$this->sign['phone'].'"')->Field('id,user_name')->find();
        if($user){
            M('member_yott')->add(
                array(
                    'uid'=>$user['id'],
                    'startdate'=>time(),
                    'startip'=>sprintf("%u", ip2long(get_client_ip())),
                    'status'=>2,//这里如果是一存在的手机号码就返回2
                    'yott_name'=>$this->sign['name']
                )
            );
            if(import('App.Api.yott')) {
                $yott = new yott();
                $yott->bindUser($user['user_name'],$this->sign['phone'],2);
            }
            $this->yottLogin($user['id']) &&  self::redirect('/invest/'.$this->sign['identify']);
            return '';
        }
        return $this->sign;
    }

    //验证sign是否合法
    protected function getSign()
    {
        $sign = ($_REQUEST['sign']);

        session('sign',$sign);
        $yott = new yott();
        $decode = $yott->decodeSign($sign);
        $result = $this->VerifiStr($decode);
        return $result;
    }
    private  function VerifiStr($str)
    {
        parse_str($str, $arr);
        if (!self::isMobile($arr['phone'])) {
            die('sign error');
        };
        $this->sign = $arr;

        $this->assign('arr',$arr);
        return true;
    }

    //处理登录
    private function yottLogin($uid)
    {
        M('member_yott')->where('uid='.$uid)->save(array(
            'lastip'=>sprintf("%u", ip2long(get_client_ip())),
            'lastdate'=>time()
        ));
        $this->_memberlogin($uid);
        return true;
    }
    protected function _memberlogin($uid){
        $vo = M('members')->field("id,user_name")->find($uid);
        if(is_array($vo)){
            foreach($vo as $key=>$v){
                session("u_{$key}",$v);
            }
            $up['uid'] = $vo['id'];
            $up['add_time'] = time();
            $up['ip'] = get_client_ip();
            M('member_login')->add($up);

            if(intval($_POST['Keep'])>0){
                $time = intval($_POST['Keep'])*3600*24;
                $loginconfig = FS("Webconfig/loginconfig");
                $cookie_key = substr(md5($loginconfig['cookie']['key'].$uid),14,10);
                $cookie_val = $this->_authcode($uid,'ENCODE',$loginconfig['cookie']['key']);
                cookie("UKey",$cookie_val,$time);
                cookie("Ukey2",$cookie_key,$time);
            }
        }
    }
}