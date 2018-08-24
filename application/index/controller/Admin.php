<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}下午 1:32
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Db;
use think\session;
use think\Request;
use think\Controller;
use app\index\model\Admin as AdminModel;

class Admin extends Base
{


    public function login()
    {

        if(Session::get('?admin_id') && Session::get('admin_id')>0){
            $this->success("您已登录",url('index/index'));
        }

        return $this->view->fetch();
    }



    /**
     * 检查登录
     * @param Request $request
     * @return array
     */
    public function checkLogin(Request $request)
    {
        //初始化返回参数
        $status = 0;
        $result = '验证失败';
        $data = $request->param();

        //验证规则
        $rule = [
            'username|用户名' => 'require',
            'password|密码' => 'require',
            'captcha|验证码' => 'require|captcha'
        ];

        $msg = [
            'username' => ['require' => '用户名不能为空'],
            'password' => ['require' => '密码不能为空'],
            'verify' => [
                'require' => '验证码不能为空',
                'captcha' => '验证码错误',
            ],
        ];

        //进行验证
        $result = $this->validate($data, $rule, $msg);

        if($result === true)
        {
            //查询条件
            $map = [
                'username' => $data['username'],
                'password' => encrypt( $data['password'] ),
            ];

            //数据表查询,返回模型对象
            $admin = AdminModel::get($map);
            if($admin->status == '已启用')
            {
                if ($admin)
                {
                    $status = 1;
                    $result = '登录成功,正在跳转......';

                    //设置session
                    Session::set('admin_id', $admin->id);
                    Session::set('login_time', time());
                    Session::set('login_ip', $_SERVER['REMOTE_ADDR']);
                    Session::set('admin_info', $admin);

                    //更新用户登录次数:自增1
                    $admin->setInc('login_count');

                    /* 记录日志 */
                    admin_log('后台登录', $data['username']);

                } else {
                    $status = 0;
                    $result = '用户名或密码错误';
                }
            }else{
                $status = 0;
                $result = '该帐号已被锁定,请联系管理员';
            }
        }
        return ['status' => $status, 'message' => $result, 'data' => $data];
    }



    /**
     * 安全退出
     */
    public function logout()
    {
        Admin::update(['login_time'=>time()],['id'=> Session::get('admin_id')]);
        Session::delete('admin_id');
        Session::delete('admin_info');
        $this->success('退出成功,正在跳转',url('admin/login'));
    }


    //管理员列表
    public function adminList()
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords', 'PHP中文网教学系统');
        $this -> view -> assign('desc', '教学案例');

        $this -> view -> count = AdminModel::count();

        $pre = config('database.prefix');
        $list = Db::name('admin')->field('a.*,r.role_name')->alias('a')
            ->join("{$pre}admin_role b",'a.id=b.admin_id')
            ->join("{$pre}role r", 'b.role_id=r.id')
            ->paginate(10);

        $this->view->assign('list', $list);
        //渲染管理员列表模板
        return $this->view->fetch('admin_list');
    }



    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = AdminModel::get($user_id);
        if($result->getData('status') == 1) {
            AdminModel::update(['status'=>0],['id'=>$user_id]);
        } else {
            AdminModel::update(['status'=>1],['id'=>$user_id]);
        }

        //记录日志
        admin_log('修改状态', '修改状态');

    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $admin_id = $request -> param('id');
        $result = AdminModel::get($admin_id);

        //取出所有的角色
        $role = Db::name('role')->select();
        $this->view->assign('role', $role);

        // 取出当前管理员所在的角色的ID
        $arModel = Db::name('AdminRole');
        $rid = $arModel->where(array('admin_id'=>array('eq', $admin_id)))->value('role_id');
        $this->view->assign('rid', $rid);

        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        $this->view->assign('admin_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //更新数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
        $data = $request->param();

        $param = $data;

        $condition = ['id'=>$data['id']];
        if(empty($data['password'])){
            unset($data['password']);
        }
        unset($data['role_id']);
        $result = AdminModel::update($data, $condition);

        //更新管理员权限表
        $post = array(
            'admin_id' => $param['id'],
            'role_id' => $param['role_id'],
        );
        Db::name('admin_role')->update($post,$condition);

        //记录日志
        admin_log('编辑管理员', $param['username']);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }



    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        AdminModel::update(['is_delete'=>1],['id'=> $user_id]);
        AdminModel::destroy($user_id);

    }

    //恢复删除操作
    public function unDelete()
    {
        AdminModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        //取出所有的角色
        $role = Db::name('role')->select();
        $this->view->assign('role', $role);

        $this->view->assign('title','添加管理员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        return $this->view->fetch('admin_add');
    }

    //检测用户名是否可用
    public function checkUserName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (AdminModel::get(['name'=> $userName])){
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //检测用户邮箱是否可用
    public function checkUserEmail(Request $request)
    {
        $userEmail = trim($request -> param('email'));
        $status = 1;
        $message = '邮箱可用';
        if (AdminModel::get(['email'=> $userEmail])) {
            //查询表中找到了该邮箱,修改返回值
            $status = 0;
            $message = '邮箱重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();

        $param = $data;

        $status = 1;
        $message = '添加成功';

        $rule = [
            'name|用户名' => "require|min:3|max:10",
            'password|密码' => "require|min:3|max:10",
            'email|邮箱' => 'require|email'
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true)
        {
            $data['password'] = encrypt($data['password']);
            unset($data['role_id']);
            $admin = AdminModel::create($data);
            if ($admin === null) {
                $status = 0;
                $message = '添加失败~~';
            }

            //添加管理员到权限表
            $post = array(
                'admin_id' => $admin->id,
                'role_id' => $param['role_id'],
            );
            Db::name('admin_role')->insert($post);

            //记录日志
            admin_log('添加管理员', $param['username']);
        }

        return ['status'=>$status, 'message'=>$message];
    }



    /**
     * 管理员日志
     */
    public function adminLog()
    {
        //获取记录数量
        $count = Db::name('admin_log')->count();

        $Log = Db::name('admin_log')->alias('l');
        $adminLog = $Log->join('__ADMIN__ a', 'a.id = l.user_id')->order('l.log_time DESC')->paginate(10);


        $this->view->assign('list', $adminLog);
        $this->view->assign('count', $count);

        return $this->view->fetch('admin_log');
    }


}