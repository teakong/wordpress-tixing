<?php
function tixing_load(){
    //获得配置 tixing_option
    $option = tixing_get_option();
    //查看要监控的钩子类型
    $hook = tixing_get_array_value_by_key($option, 'hook', []);
    //钩子：新用户注册
    $hook_user_signup = tixing_get_array_value_by_key($hook, 'user_signup', 0);
    if($hook_user_signup === 1) { add_action( 'register_post','tixing_hook_user_signup', 999, 3 ); }
    //钩子：评论发表
    $hook_comment_post = tixing_get_array_value_by_key($hook, 'comment_post', 0);
    if($hook_comment_post === 1) { add_action( 'comment_post','tixing_hook_comment_post', 999, 3 ); }
}

/**
 * 获得设置
 */
function tixing_get_option(){
    $option = get_option('tixing_option');
    if(empty($option)) return [];
    return json_decode($option, true);
}
/**
 * 写入设置
 */
function tixing_update_option($option){
    return update_option('tixing_option', json_encode($option));
}


/**
 * 从数组内获得参数
 */
function tixing_get_array_value_by_key($array, $key, $defalutValue=null){
    if(array_key_exists($key, $array)) return $array[$key];
    return $defalutValue;
}

/**
 * 各种钩子类型hook
 */

/** hook:新用户注册 */
function tixing_hook_user_signup( $sanitized_user_login, $user_email, $errors){
    if(empty($errors->errors)){
        tixing_send('新用户注册：' . $user_email);
    }
}
/** hook:用户发表评论 */
function tixing_hook_comment_post($comment_ID, $comment_approved, $commentdata){
    if($comment_approved === 0 || $comment_approved === 1){
        $comment_content = $commentdata['comment_content'];
        if(mb_strlen($comment_content) > 20) $comment_content = mb_substr( $comment_content, 0, 20, 'utf-8' );
        tixing_send('新评论(文章id-' . $commentdata['comment_post_ID'] . ')', $comment_content);
    }
}


/**
 * 执行推送
 */
function tixing_send($head, $body = ""){
    $option = tixing_get_option();
    //通道码
    $channel_code = tixing_get_array_value_by_key($option, 'channel_code', null);
    //下次发送时间
    $nextsend_timestamp = tixing_get_array_value_by_key($option, 'nextsend_timestamp', 0);
    //当前时间戳
    $now_timestamp = time();
    $now_timestring = date('H:i:s', $now_timestamp);
    //缓存的提醒内容
    $text_buffer = tixing_get_array_value_by_key($option, 'text_buffer', []);

    $text = $now_timestring . '  ' . $body;
    $text_buffer[] = $text;
    $text = join(PHP_EOL ,$text_buffer);
    $option['text_buffer'] = $text_buffer;
    
    if($now_timestamp > $nextsend_timestamp){
        $jsonObj = tixing_send_function($channel_code, $head, $text);
        
        if ($jsonObj->code === 0) {
            //发送成功，更新下次发送时间
            $option['nextsend_timestamp'] = time() + 61;
            $option['text_buffer'] = [];
        }
        else{
            //如果发送失败，记录下最后一条失败记录
            $option['last_error'] = $now_timestring . ' 发送失败，错误代码：' . $jsonObj->code . '，描述：' . $jsonObj->msg;
        }
    }
    else {
        //距离上次发送不到1分钟，设置定时器
        $cron_send_timestamp = wp_next_scheduled('tixing_cron_send_event');
        if($cron_send_timestamp === false){
            wp_schedule_single_event($nextsend_timestamp, 'tixing_cron_send_event' );
        }
    }
    tixing_update_option($option);
}
/**
 * 外部调用：发送提醒
 */
function tixing($text){
    return tixing_send($text);
}

/**
 * 定时推送
 */
function tixing_cron_send(){
    $option = tixing_get_option();
    //通道码
    $channel_code = tixing_get_array_value_by_key($option, 'channel_code', null);
    //缓存的提醒内容
    $text_buffer = tixing_get_array_value_by_key($option, 'text_buffer', []);
    $text = join(PHP_EOL ,$text_buffer);
    if(!empty($text)){
        $jsonObj = tixing_send_function($channel_code, "定时推送", $text);
        
        if(!empty($jsonObj)) {
            if ($jsonObj->code === 0) {
                //发送成功，更新下次发送时间
                $option['nextsend_timestamp'] = time() + 61;
                $option['text_buffer'] = [];
            }
            else{
                //如果发送失败，记录下最后一条失败记录
                $option['last_error'] = $now_timestring . ' 发送失败，错误代码：' . $jsonObj->code . '，描述：' . $jsonObj->msg;
            }
        }
        else {
            //未知错误
            $option['last_error'] = $now_timestring . ' 发送失败，错误代码：未知';
        }
        tixing_update_option($option);
    }
}
add_action( 'tixing_cron_send_event','tixing_cron_send' );


/**
 * 推送请求发送
 */
function tixing_send_function($channel_code, $head, $body){
	$channel_code = esc_html($channel_code);
	if(strpos($channel_code, "http") === 0) {
		$api_url = $channel_code;
	}else{
		$api_url = 'https://www.phprm.com/services/push/trigger/'.$channel_code;
	}
    $response = wp_remote_get($api_url ."?head=". urlencode($head) . '&body=' . urlencode($body));
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        return json_decode($response['body'], false);
    }
    else {
        return null;
    }
}