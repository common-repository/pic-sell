<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$name2 = get_query_var('name_img');
$dir2 = get_query_var('dir_img')."/";

$basedir = wp_upload_dir();
$location = $basedir["basedir"] . '/pic_sell/';

$url = ($location . $dir2 . $name2);

if(is_file($url)){
    //require PIC_SELL_PATH_INC . "class-pic-sell-stream.php";
    //$stream = new PIC_VideoStream($url);
    //$stream->start();
    echo file_get_contents($url);

    exit();
  }else{
    die("This file not exist.");
  }
