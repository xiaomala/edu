<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function encrypt($str)
{
    return md5(Config('AUTH_CODE').$str);
}




/**
 * 记录管理员的操作内容
 *
 * @access  public
 * @param   string      $action     操作的类型
 * @param   string      $content    操作的内容
 * @return  void
 */
function admin_log( $action, $content)
{
    $log_info = $action .': '. $content;

    $data = array(
        'log_time' => time(),
        'user_id' => \think\Session::get('admin_id'),
        'log_info' => $log_info,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    );
    db('admin_log')->insert($data);
}



/**
 * 获取登录的管理员ID 根据管理员ID来查询中间表admin_role 并返回role_id
 * @param $admin_id
 * @return array|false|PDOStatement|string|\think\Model
 */
function get_group_id($aid)
{
    $rid = db('admin_role')->where("admin_id={$aid}")->value('role_id');
    return $rid;
}


/**
 * 根据返回回的role_id 来查询管理员ID获得角色的名称
 * @param $id
 * @return mixed
 */
function get_group($role_id)
{
    $title = db('role')->where("id={$role_id}")->column('role_name');
    return $title[0];
}