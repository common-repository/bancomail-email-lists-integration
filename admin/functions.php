<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function BMWS_set_pagination($total_pages, $current_page, $pagination_range){
    $pagination=array();
    $total_page_set=ceil($total_pages/$pagination_range);
    $current_page_set=ceil($current_page/$pagination_range);
    $pagination_range= intval($total_pages) > $pagination_range ? $pagination_range : $total_pages;
    $upper_limit=$pagination_range * $current_page_set;
    $lower_limit=$upper_limit - $pagination_range;
    //if this is the last set
    if($current_page_set==$total_page_set){
        $upper_limit=($upper_limit - $total_pages) > 0 ?  ($upper_limit - ($upper_limit - $total_pages)) : $upper_limit;
    }

    //print previous page set link
    if($current_page_set > 1 && $current_page <= $total_pages){
        $pagination['previous']=($lower_limit);
    }
    for($i=$lower_limit;$i < $upper_limit;$i++){
        if($current_page > $total_pages || $current_page < 1){
            break;
        }
        $link=($i==0) ? '-' : ($i+1);
        $pagination[($i+1)]=intval($link);
    }
    //print next page set link
    if($current_page_set < $total_page_set){
        $pagination['next']=($upper_limit+1);
    }
     
    return $pagination;
}

function BMWS_check_wsdl_connection(){
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://wbx.bancopress.com/BancoMailWS/BancoWs?wsdl');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info['http_code'];
    }
    catch(Exception $e){
        return $e->getMessage();
    }
}

function BMWS_check_php_installed_extensions($extension_name){
    $extensions=get_loaded_extensions(false);
    //var_dump($extensions);
    if(!in_array($extension_name,$extensions)){
        return FALSE;
    }
    else{
        return TRUE;
    }
}
?>