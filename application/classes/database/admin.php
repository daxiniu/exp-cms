<?php

defined('SYSPATH') or die('No direct script access.');

class Database_admin {
    /*     * ****
     * 创建一个新的管理员数据 于admin表里
     * @$admin <array> 用户对象的数据内容
     * @return message <string> 直接返回执行情况消息
     */

    public function create($admin) {

        try {
            $columns = array();
            foreach ($admin as $key => $value) {
                $columns[$key] = $key;
            }
            $admin["password"] = md5($admin["password"]);
            $save = DB::insert("admin", $columns);
            $save->values($admin);
            $save->execute();
            return 'ok';
        } catch (Exception $e) {
            ErrorExceptionReport::_errors_report($e);
            return 'error';
        }
    }

    /*     * **
     * 获取符合条件的数据 进行分页
     * @$admin <array>  对应admin表列的筛选条件的多个参数
     * @$page_Param <array> 关于分页的一些参数
     * @return <array> 符合条件的user表数据以及其他参数
     * @return message <string> 有错误的情况下会直接返回消息 正常执行的状态下会封装在return array里返回
     */

    public function query_list($admin, $page_Param, $sort, $keyword="") {
        $query = DB::select(array('COUNT("id")', 'total_admin'))->from('admin');
        foreach ($admin as $filedName => $filedvalue) {
            if (isset($filedvalue))
                if ($filedvalue != null) {
                    $query->where('admin.' . $filedName, "like", "%" . $filedvalue . "%");
                }
        }
        if (!empty($keyword)) {
            $query->and_where_open();
            $query->where('username', "like", "%" . $keyword . "%");
            $query->and_where_close();
        }
        $count_Result = $query->execute()->as_array();
        $count = $count_Result[0]['total_admin'];

        //设置查询数据的sql
        $query = DB::select("admin.*")->from('admin');


        foreach ($admin as $filedName => $filedvalue) {
            if (isset($filedvalue))
                if ($filedvalue != null) {
                    $query->where('admin.' . $filedName, "like", "%" . $filedvalue . "%");
                }
        }
        if (!empty($keyword)) {
            $query->and_where_open();
            $query->where('username', "like", "%" . $keyword . "%");
            //$query->or_where("user.username", "like", "%" . $post["keyword"] . "%");
            //$query->or_where("category.name", "like", "%" . $post["keyword"] . "%");
            $query->and_where_close();
        }
        if (isset($sort["order_by"]) && isset($sort["sort_type"])) {
            $query->order_by($sort["order_by"], $sort["sort_type"]);
        }
        if (!isset($page_Param["items_per_page"])) {
            $page_Param["items_per_page"] = 20;
        }
        //获取当前数据起始位置
        $current_item = $page_Param["items_per_page"] * ($page_Param["page"] - 1);
        $total_page_count = (int) ceil($count / $page_Param["items_per_page"]);
        $query->offset($current_item)->limit($current_item + $page_Param["items_per_page"]);
        $admins = $query->execute();
        $admins = $admins->as_array();
        //加入一些业务值，特殊业务值的替换或者加入
        for ($i = 0; $i < count($admins); $i++) {

            $admins[$i]["role_name"] = Sysconfig_Business::admin_Role($admins[$i]["role"]);
            $admins[$i]["password"] = "";
        }

        if ($count > 0)
            return array(
                'total_items_count' => $count, //总记录数
                'total_page_count' => $total_page_count,
                'items_per_page' => $page_Param["items_per_page"], //每页显示数据条数
                'result' => $admins,
            );
        else
            return "none";
    }

    /*     * *
     * 获取指定用户的信息
     * @$id <int> 用户id
     * @return <array> 用户信息
     * @return message <string> 有错误的情况下会直接返回消息 正常执行的状态下会封装在return array里返回
     */

    public function get_admin($admin) {
        try {
            if ($admin["id"] == null || $admin["id"] == "") {
                return 'no_id';
            }
            if (isset($admin["password"])) {
                $admin["password"] = md5($admin["password"]);
            }

            //设置查询数据的sql
            $query = DB::select('id', 'username', "password", "role")->from('admin');
            $query->where("id", "=", $admin["id"]);
            $admins = $query->execute();
            $admins = $admins->as_array();

            $count = count($admins);
            //加入一些业务值，特殊业务值的替换或者加入
            for ($i = 0; $i < count($admins); $i++) {

                $admins[$i]["role_name"] = Sysconfig_Business::admin_Role($admins[$i]["role"]);
                $admins[$i]["password"] = "";
            }
            if ($count > 0)
                return $data = array('result' => $admins,);
            else
                return 'none';
        } catch (Exception $e) {
            ErrorExceptionReport::_errors_report($e);
            return "error";
        }
    }

    /**     *
     * 管理员登录检测
     * @$id <int> 用户id
     * @return <array> 用户信息
     * @return message <string> 有错误的情况下会直接返回消息 正常执行的状态下会封装在return array里返回
     */
    public function check_login($admin) {
        if ($admin["username"] == null || $admin["username"] == "") {
            return 'no_id';
        }
        $admin["password"] = md5($admin["password"]);
        //设置查询数据的sql
        $query = DB::select('id', 'username', "password", "role")->from('admin');
        $query->where('username', "=", $admin["username"])->where('password', "=", $admin["password"]);
        $admins = $query->execute();
        $admins = $admins->as_array();
        $count = count($admins);
        //加入一些业务值，特殊业务值的替换或者加入
        for ($i = 0; $i < count($admins); $i++) {

            $admins[$i]["role_name"] = Sysconfig_Business::admin_Role($admins[$i]["role"]);
            $admins[$i]["password"] = "";
        }
        // echo Kohana::debug($count);
        if ($count > 0)
            return $data = array('result' => $admins,);
        else
            return FALSE;
    }

    /*     * ***
     * 删除指定管理员
     * @$id <int> 用户id
     * @return message <string> 直接返回执行情况消息
     */

    public function delete($id) {
        try {
            if ($id == null || $id == "") {
                return 'no_id';
            }
            //设置删除数据的sql
            $delete = DB::delete('admin');
            $delete->where("id", "=", $id);
            $delete->execute();
            return 'ok';
        } catch (Exception $e) {
            echo ErrorExceptionReport::_errors_report($e, TRUE);
            return 'error';
        }
        //  return $result ? "ok" : "error";
    }

    /*     * ***
     * 根据ID，修改admin表行数据
     * @param $admin （array(integer)）
     */

    public function modify($admin) {
        if ($admin == null || count($admin) == 0 || $admin['id'] == null) {
            return 'no_id';
        }
        if (isset($admin["password"])) {
            $admin["password"] = md5($admin["password"]);
        }
        /* 根据需要从请求中取出需要的数据值 */
        $ids = explode(",", $admin['id']);
        $modify = DB::update()->table('admin')->set($admin);
        //判断是否是批量操作
        if (count($ids) > 1) {
            $modify->where('id', 'in', $ids);
        } else {
            $modify->where('id', '=', $admin['id']);
        }
        $result = (bool) $modify->execute();
        return $result ? 'ok' : 'error';
    }

    /**     * ***
     * 检测该管理员是否已经存在
     * @param $admin array 用户信息
     * @return bool 存在返回FALSE 不存在返回TRUE
     */
    public function check_exist($admin) {

        //设置查询数据的sql
        $query = DB::select(array('COUNT("id")', 'total_admin'))->from('admin');
        $query->where("username", "=", $admin["username"]);
        $admins = $query->execute();
        $admins = $admins->as_array();
        $count = $admins[0]["total_admin"];

        return $count > 0 ? FALSE : TRUE; //存在的话返回FALSE 不存在返回True
    }

}

?>
