<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}上午 11:09
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Db;
use think\Session;
use think\Controller;
use think\Request;

class Base extends Controller
{


    /**
     * 初始化操作
     */
    public function __construct()
    {
        parent::__construct();

        $request = Request::instance();
        $action = $request->action();

        //过滤不需要登陆的行为
        if(in_array($action, array('login','checklogin','checkLogin','logout','captcha'))){
            //return;
        }else{
            if(Session::get('admin_id') > 0 ){
                $this->check_priv();//检查管理员菜单操作权限
            }else{
                $this->error('请先登陆',url('admin/login'));
            }
        }

        define('ADMIN_ID', Session::has('admin_id') ? Session::get('admin_id'):null);

    }







    /**
     * 验证当前管理员是有权访问
     */
    public function check_priv()
    {
        $request = Request::instance();
        $module = $request->module();
        $controller = $request->controller();
        $action = $request->action();

        //先获取当前管理员的ID
        $admin_id = session('admin_id');

        //取出当前管理员所有的权限
        $where = "module_name='".$module."' AND controller_name='".$controller."' AND action_name='".$action."'";

        //无需验证的操作
        $uneed_check = array('login','checkLogin','logout','captcha');

        if($admin_id == 1){
            return true;  //超级管理员无需验证
        }elseif($controller == 'Index' || $controller == 'Uploads'){
            return true;  //后台首页控制器无需验证
        }elseif(strpos('ajax',$action) || in_array($action,$uneed_check)){
            //所有ajax请求不需要验证权限
            return true;
        }else{

            //查询 按中间表查询 role_privilege
            //连接 权限表 privilege
            //连接 中间表 admin_role
            $pre = Config('databases.prefix');
            $res = DB::name('role_privilege')->field("count(*) has")->alias('a')
                ->join("{$pre}privilege p,  a.pri_id=p.id")
                ->join("{$pre}admin_role c, a.role_id=c.role_id")
                ->where("c.admin_id={$admin_id} AND {$where}")->find();

            if($res['has'] < 1){
                $this->error('无权访问,请联系管理员!');
            }
        }
    }

}