<?php
namespace Admin\Controller;
use Think\Controller;
class CommonController extends Controller {
   //登陆拦截器
   public function _initialize(){
			$url = __ACTION__;
			if($url == "/rili/Admin/Admin/relogin" ||$url == "/rili/Admin/Admin/login"){
          
			}else{
			//$result = session('login_user');
			if(!session('login_user')){
            $this->redirect('Admin/relogin');
            }else {
			 
			 }
			}
			
    }

}

?>