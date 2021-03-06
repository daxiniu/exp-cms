<?php

/* * *
 * 控制器中action的工具包
 */

class Action {

    /**     * *
     * 根据数据状态返回相应的数据集合
     * @$posts
     */
    public static function sucess_status($view_data, $performance="") {
        if ($view_data === "none") {
            $view_data = array(
                'success' => FALSE,
                'message' => $performance . '没有任何此类数据！',
                'result' => '',
            );
        } else if ($view_data === "no_id") {
            $view_data = array(
                'success' => FALSE,
                'message' => $performance . '没有指定的数据！',
                'result' => '',
            );
        } else if ($view_data === "error" || $view_data === FALSE) {
            $view_data = array(
                'success' => FALSE,
                'message' => $performance . '操作失败！',
                'result' => '',
            );
        } else if ($view_data === "exist") {
            $view_data = array(
                'success' => FALSE,
                'message' => $performance . '已经存在',
                'result' => '',
            );
        } else if ($view_data === "data_equal") {
            $view_data = array(
                'success' => TRUE,
                'message' => $performance . '操作成功，但数据没有变动',
                'result' => '',
            );
        } else {
            if (is_array($view_data)) {
                $view_data['success'] = TRUE;
                $view_data['message'] = $performance . '操作成功';
            } else {
                $view_data = array(
                    'success' => TRUE,
                    'message' => $performance . '操作成功',
                    'result' => '',
                );
            }
        }
        return $view_data;
    }

    /**
     * 根据form的不同功能配置 生成装饰新的表单
     * @param <array> $form
     * @param <array> $function_config
     * @return <array>
     */
    public static function form_decorate($form, $function_config) {
        $new_form = array();
        if (isset($function_config)) {
            //处理需要推送字段
            if (isset($function_config["display"])) {
                $filed_names = $function_config["display"];
                $filed_names = explode(",", trim($filed_names));
                foreach ($filed_names as $key => $value) {
                    if (isset($form[$value])) {
                        $new_form[$value] = $form[$value];
                    }
                }
            }
            //处理需要设置只读字段
            if (isset($function_config["readonly"])) {
                $readonly_names = $function_config["readonly"];
                $readonly_names = explode(",", trim($readonly_names));
                foreach ($readonly_names as $key => $value) {
                    if (isset($new_form[$value])) {
                        $new_form[$value]["readonly"] = TRUE;
                    }
                }
            }
        }

        return $new_form;
    }

    public static $LEGAL_FORM_TYPE_READ = "r";
    public static $LEGAL_FORM_TYPE_WRITER = "W";

    /**
     * 根据配置和数据库操作类型返回合法字段名集合
     * @param <array> $function_config
     * @param <string> $type $LEGAL_FORM_TYPE_READ|$LEGAL_FORM_TYPE_WRITER
     * @param <array> $filter_fileds 特殊情况下过滤掉不需要的字段
     * @return <type>
     */
    public static function legal_fileds($function_config, $type, $filter_fileds=NULL) {
        $readonly_fileds = array();
        $display_fileds = array();
        if (isset($function_config["display"])) {
            $display_fileds = explode(",", trim($function_config["display"]));
            if (isset($function_config["readonly"])) {
                $readonly_fileds = explode(",", trim($function_config["readonly"]));
            }
        }
        if ($type == Action::$LEGAL_FORM_TYPE_WRITER) {
            foreach ($display_fileds as $key => $value) {
                if (in_array($value, $readonly_fileds)) {
                    unset($display_fileds[$key]);
                }
            }
        }
        if ($filter_fileds != NULL) {
            foreach ($display_fileds as $key => $value) {
                if (in_array($value, $filter_fileds)) {
                    unset($display_fileds[$key]);
                }
            }
        }
        return $display_fileds;
    }

    /**     * *
     * 将已有的值回填给form中
     * @param <type> $form
     * @param <type> $data_arr
     * @return <type>
     */
    public static function build_form_data($form, $data_arr) {
        foreach ($form as $name => $filed) {
            if (isset($data_arr[$name])) {
                if ($filed['type'] == "select") {
                    $form[$name]["value"]["select"] = $data_arr[$name];
                } else {
                    $form[$name]["value"] = $data_arr[$name];
                }
            }
        }
        return $form;
    }
    /**
     *设置一个赋予来源页面的URL值的隐藏表单
     * @param $form 表单配置
     * @return <type>
     */
    public static function set_next_redirect_url($form=array()) {
     
        $form["DXN_NEXT_REDIRECT_URL"] = array(
            'label' => '管理员用户名',
            'type' => 'hidden',
            'name' => 'DXN_NEXT_REDIRECT_URL',
            'value' => $_SERVER['HTTP_REFERER'],
        );
        return $form;
    }

}

?>
