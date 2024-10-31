<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php $viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' ); ?>
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php

$builder = get_option("builder_pic");
$header_background = isset($builder["builder"]["header"]["background"])&&!empty($builder["builder"]["header"]["background"])?$builder["builder"]["header"]["background"]:PIC_SELL_URL."public/img/camera-gf7958b3ec_1920.jpg";
$header_background = (get_the_post_thumbnail_url()&&!post_password_required())?get_the_post_thumbnail_url():$header_background;

$post = get_post(); 
//print_r($post);
$title = !post_password_required() ? $post->post_title : "Contenu protégé par mot de passe";
$title = !is_post_type_archive() ? $title : "Merci pour votre achat";
?>
<header class="espaceprive-header header-espaceprive">
    <div class="header-background" style="background-image: url('<?php echo sanitize_url($header_background); ?>');"></div>
    <?php  
    echo "<h1>".sanitize_text_field($title)."</h1>";
    ?> 
</header>