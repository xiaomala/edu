<?php
// +----------------------------------------------------------------------
// | Blog [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://lexiang123.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 2275840206@qq.com
// +----------------------------------------------------------------------
// | Time : ${DATA}下午 1:05
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Db;
use think\Controller;
use think\Request;
use app\index\model\Teacher as TeacherModel;

class Teacher extends Base
{
    //教师列表
    public function teacherList()
    {
        //获取所有教师表teacher数据
        $teacherList = TeacherModel::paginate(10);

        //获取记录数量
        $count = TeacherModel::count();

        $this->view->assign('teacherList', $teacherList);
        $this->view->assign('count', $count);

        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑班级');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');

        return $this->view->fetch('teacher_list');
    }



    //教师状态变更
    public function setStatus(Request $request)
    {
        $teacher_id = $request -> param('id');
        $result = TeacherModel::get($teacher_id);
        if($result->getData('status') == 1) {
            TeacherModel::update(['status'=>0],['id'=>$teacher_id]);
        } else {
            TeacherModel::update(['status'=>1],['id'=>$teacher_id]);
        }
    }



    //渲染教师编辑界面
    public function teacherEdit(Request $request)
    {
        $teacher_id = $request -> param('id');
        $result = TeacherModel::get($teacher_id);

        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑教师信息');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');

        //给当前教师编辑页面模板赋值
        $this->view->assign('teacher_info',$result);

        //将班级表中所有数据赋值给当前模板
        $this->view->assign('gradeList',\app\index\model\Grade::all());

        //渲染出当前的编辑模板
        return $this->view->fetch('teacher_edit');
    }



    //教师更新
    public function doEdit(Request $request)
    {
        //从提交表单中排除关联字段teacher字段
        $data = $request -> except('grade');

        //设置更新条件
        $condition = ['id'=>$data['id']];

        //更新当前记录
        $result = TeacherModel::update($data,$condition);

        //设置返回数据
        $status = 0;
        $message = '更新失败,请检查';

        //检测更新结果,将结果返回给grade_edit模板中的ajax提交回调处理
        if (true == $result){
            $status = 1;
            $message = '恭喜, 更新成功~~';
        }

        //记录日志
        admin_log('编辑教师', $data['name']);

        return ['status'=>$status, 'message'=>$message];
    }



    //渲染教师添加界面
    public function teacherAdd()
    {
        $this->view->assign('title','添加班级');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');

        //将班级表中所有数据赋值给当前模板
        $this->view->assign('gradeList',\app\index\model\Grade::all());

        return $this->view->fetch('teacher_add');
    }



    //添加教师
    public function doAdd(Request $request)
    {
        //从提交表单中获取数据
        $data = $request -> param();

        //向表中新增记录
        $result = TeacherModel::create($data);

        //设置返回数据
        $status = 0;
        $message = '添加失败,请检查';

        //检测更新结果,将结果返回给teacher_add模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 添加成功~~';
        }

        //记录日志
        admin_log('添加教师', $data['name']);

        return ['status'=>$status, 'message'=>$message];
    }



    //软删除操作
    public function deleteTeacher(Request $request)
    {
        $teacher_id = $request -> param('id');
        TeacherModel::update(['is_delete'=>1],['id'=> $teacher_id]);
        TeacherModel::destroy($teacher_id);

    }



    //恢复删除操作
    public function unDelete()
    {
        TeacherModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }


}