<?php

/**
 * The public-facing functionality of the plugin. 
 * 
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * 
 *
 * @link       https://portfolio.cestre.fr
 * @since      1.0.0
 *
 * @package    Pic_Sell
 * @subpackage Pic_Sell/public
 * @author     Benjamin CESTRE <benjamin@cestre.fr>
 */

class Pic_Sell_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('wp_ajax_nopriv_picsell_logout', array( $this, 'picsell_protected_logout' ) ); 	// logout for non-logged in wp users
		add_action('wp_ajax_picsell_logout', array( $this, 'picsell_protected_logout' ) ); 		// logout for logged in wp users
		
		add_action('wp_ajax_post_app', array($this, 'post_app'));
		add_action('wp_ajax_nopriv_post_app', array($this, 'post_app'));

		$config = get_option('config_pic'); 
		$cron_active = isset($config["cron"]["active"])&&$config["cron"]["active"]?true:false;
		if($cron_active){
			//add_action('wp_footer', array($this, 'scheduled_task'), 999);
			add_action('picsell_cron_task', array($this, 'scheduled_task'));
		}
	
	}


	public function scheduled_task(){

		//	$cron = get_option('cron_pic'); //remove for let Wp_cron manage
		//	$last_date_cron = isset($cron)&&!empty($cron)?$cron:date("Y-m-d", strtotime('-1 days')); //par défault si date_cron n'est pas déclarer on met la date du jour à - 1 jour. //remove for let Wp_cron manage

		//	$date_left_en = date_create_from_format('Y-m-d', $last_date_cron);//remove for let Wp_cron manage
		//	$DateNow = date_create_from_format('Y-m-d', date('Y-m-d'));//remove for let Wp_cron manage

		//$TempsRestant = $DateNow->diff($date_left_en); //remove for let Wp_cron manage
		//if($TempsRestant->days >= 1){ //remove for let Wp_cron manage
			$this->checkGalleryDate();
			update_option('cron_pic', date("Y-m-d"));
		//}
	}

	 private function checkGalleryDate(){

		$show_gallery = get_posts( array(
			'post_type'      => 'espaceprive',
			'post_status'    => 'publish',
			'numberposts' => -1,
	   	));
		
		foreach($show_gallery as $id => $gallery){
			
			$DateNow = date_create_from_format('Y-m-d', date('Y-m-d'));
			$date_gallery = get_post_meta($gallery->ID, '_date_left', true);
			$date_create_gallery = new DateTime($gallery->post_date);

			// $date_create_gallery->format('Y-m-d');

			$date_left = date_create_from_format('Y-m-d', $date_gallery);
				
			$email_client = get_post_meta($gallery->ID, '_email_client', true);
	
			$TempsRestant = $DateNow->diff($date_left);

			$datevalidite = $date_create_gallery->diff($date_left);
			
			$email_sent = get_post_meta($gallery->ID, '_email_dateleft_sent', true);

			if($TempsRestant->invert == 0 && $TempsRestant->days > 0 &&  $TempsRestant->days <= 7 && !$email_sent){

				update_post_meta($gallery->ID, '_email_dateleft_sent', true);

				$password = trim($gallery->post_password);

				$urlparts = parse_url(home_url());
				$domain = $urlparts['host'];
				$site_name = get_bloginfo("name");
	
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'From:'.$site_name.' <contact@'.$domain.'>' . "\r\n" .
					'Reply-To: contact@'.$domain."\r\n" .
					'Content-Type: text/html; charset=UTF-8'."\r\n" .
					'Content-Disposition: inline'. "\r\n" .
					'Content-Transfer-Encoding: 7bit'." \r\n" .
					'X-Mailer:PHP/'.phpversion();
	
				require (PIC_SELL_TEMPLATE_DIR . "templateOrders.php");
				$template = new PIC_Template_Mail();
	
				$html = $template->templateGalleryDateLeft();
				$message = $html;		
	
				$m = ($TempsRestant->m > 0) ? $TempsRestant->m . " mois" : "";
				$d = ($TempsRestant->d > 0) ? $TempsRestant->d . " jours" : "";
				$a = (!empty($m) && !empty($d)) ? " et ":"";
		
				$dateleft = $m.$a.$d;

				$md = ($datevalidite->m > 0) ? $datevalidite->m . " mois" : "";
				$dd = ($datevalidite->d > 0) ? $datevalidite->d . " jours" : "";
				$ad = (!empty($m) && !empty($d)) ? " et ":"";
		
				$date_v = $md.$ad.$dd;

				$datefmt = new IntlDateFormatter('fr_FR', 0, 0, NULL, NULL, 'EEEE dd LLLL YYYY');
	
				$message = str_replace('{{site_name}}', $site_name, $message);
				$message = str_replace('{{dateleft}}', $dateleft, $message);
				$message = str_replace('{{datecreate}}', $datefmt->format($date_create_gallery), $message);
				$message = str_replace('{{datevalidite}}', $date_v, $message);
				$message = str_replace('{{title}}', $gallery->post_title, $message);
				$message = str_replace('{{permalink}}', get_permalink($gallery->ID), $message);
				$message = str_replace('{{password}}', $password, $message);
				$message = str_replace('{{img}}', get_the_post_thumbnail_url($gallery->ID), $message);

				if (isset($email_client) && !empty($email_client)) {
					for ($i = 0; $i < count($email_client); $i++) {
						wp_mail($email_client[$i], '['.$site_name.'] Plus que '.$dateleft.' ! ', $message, $headers );
					}
				}

				$config = get_option('config_pic'); 
				$galery_interval_send_mail_admin = (isset($config["mail"]["galeryinterval"])&&$config["mail"]["galeryinterval"])?true:false;
				$admin_address_mail = isset($config["config"]["adresse"])?$config["config"]["adresse"]:"";
				if($galery_interval_send_mail_admin && !empty($admin_address_mail)){
					wp_mail($admin_address_mail, '[ADMIN/'.$site_name.'] Plus que '.$dateleft.' ! ', $message, $headers );
				}
			}
			else if($TempsRestant->invert == 0 && $TempsRestant->days == 0 || $TempsRestant->invert == 1){

				$query = array(
					'ID' => $gallery->ID,
					'post_status' => 'draft',
				);
				wp_update_post( $query, true );

			}
		}
	} 

	//AJAX 
	function post_app(){
		$request = sanitize_text_field($_POST["act"]);
		
		require(PIC_SELL_PATH_INC . "app/panier.php");
		
		switch ($request) {

			/*case "/img/base64/":
				set_time_limit(0);
				$img = sanitize_text_field($_POST["img"]);
				$bmedia = $img;

				$type = pathinfo($bmedia, PATHINFO_EXTENSION);
	
				$finfo = new finfo(FILEINFO_MIME); // Retourne le type mime
				$media_info = $finfo->file($bmedia);
				$genre_media = explode("/", $media_info)[0];
	
				if ($genre_media == "image") {
					$image = imagecreatefromstring(file_get_contents($bmedia));

						$exif = exif_read_data($bmedia);
						if (!empty($exif['Orientation'])) {
							switch ($exif['Orientation']) {
								case 8:
									$image = imagerotate($image, 90, 0);
									break;
								case 3:
									$image = imagerotate($image, 180, 0);
									break;
								case 6:
									$image = imagerotate($image, -90, 0);
									break;
							}
						}
					
					// Get new sizes
					$width = imagesx($image);
					$height = imagesy($image);

					//list($newWidth, $newHeight) = $this->getScaledDimArray($image, 800);
					//list($newWidth, $newHeight) = getimagesize($bmedia);
					$newWidth = $width;
					$newHeight = $height;

					$resizeImage = imagecreatetruecolor($newWidth, $newHeight);

					imagecopyresized($resizeImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

					ob_start();
					imagejpeg($resizeImage);
					$contents =  ob_get_contents();
					ob_end_clean();
					$base64 = $contents;
					//echo $resizeImage;
					//$base64 = $resizeImage;

					/*list($width_orig, $height_orig) = getimagesize($bmedia);
					$data = file_get_contents($bmedia);

					$theme_image_little = imagecreatefromstring($data);  
					$w = $width_orig;
					$h = $height_orig;

					$image_little = imagecreatetruecolor($w, $h);
					imagecopyresampled($image_little, $theme_image_little, 0, 0, 0, 0, $w, $h, $width_orig, $height_orig);
					ob_start();
					imagejpeg($image_little);
					$contents =  ob_get_contents();
					ob_end_clean();-
					$base64 = $contents;
				}	
				echo $base64;
				break;*/
		
			case "/post/update/":
		
				$cart = new PIC_Panier();
				$cart->updateCart();
				break;
		
			case "/post/checkout/":

				if(session_id() == '') {
					session_start();
				}

				$values = array();
				parse_str($_POST['form'], $values);

				$values = array_map(function($input){
    						return sanitize_text_field($input);
						}, $values);
				

				$this->protectUserCart($values['cartId'], $values['password']);

				$cartId = get_post_meta($values['cartId'], 'panier_client', true);

				$paypal = get_option("paypal_pic");
				$paypal_sandbox = (isset($paypal["paypal"]["sandbox"])&&$paypal["paypal"]["sandbox"])?true:false;
				$paypal_url = $paypal_sandbox?"https://www.sandbox.paypal.com/cgi-bin/webscr?":"https://www.paypal.com/cgi-bin/webscr?";
		
				//$pack_offers = get_post_meta(, 'produit_client', true);
				$produit_data = get_post_meta($values['id_pack'], '_offer_data', true); //value sanitize_text l-279
				
				$query = array();
				$query['first_name'] = $values['prenom']; //sanitize l-279
				$query['last_name'] = $values['nom']; //sanitize l-279
				$query['email'] = sanitize_email($values['email']);  //sanitize_text l-279 + sanitize_email
				$query['telephone'] = $values['phone'];//sanitize l-279
				$query['address1'] = $values['adresse'];//sanitize l-279
				$query['address2'] = $values['adresse2'];//sanitize l-279
				$query['city'] = $values['ville'];//sanitize l-279
				$query['country'] = $values['pays'];//sanitize l-279
				$query['state'] = $values['etat'];//sanitize l-279
				$query['zip'] = $values['cp'];//sanitize l-279
				$query['cartId'] = $values['cartId'];//sanitize l-279

				if ($query['country'] != "FR") $query['shipping_1'] = 15;

				$i = 1;				
				foreach ( $this->object_to_array(json_decode($cartId)) as $value) {

					foreach($produit_data["classement"] as $key => $val){
						if($value['id_produit'] === (int)$val){

							$query['item_name_'.$i] = $produit_data['title'][$key];
							$query['amount_'.$i] = $produit_data['price'][$key];
							$query['quantity_'.$i] = $value['qtt'] > 0 ? $value['qtt'] : 1;				    
							$i++;	

						}
					}
				}

			    $cart = new PIC_Panier();
				$arg = $cart->paypalCheckOut($cartId, $query);
				echo sanitize_url($paypal_url . $arg); //on renvoie les arguments
			   
				break;

			 case "/post/testMail/":
		
				if(session_id() == '') {
					session_start();
				}
		
			   // $cart = new Panier();
				$cart->emailOrder();
			   
				break;
		
			case "/post/checkPrivateGallery/":
		
				$this->checkGalleryDate();
					   
				break;
		
			case "/post/checkOrders/":
				
			   // $cart = new Panier();
				$cart->getOrders($queries['commande'],$queries['getBy']);
				
			   
				break;
		
			default:
			   header('Location: /404/');
		}	
		die();
	}

	private function protectUserCart($id, $pass){
		$the_post = get_post($id);

		if ( $the_post->post_password != $pass ){
			exit;
		}
		
	}

	private function object_to_array($data){

	    if (is_array($data) || is_object($data))
	    {
	        $result = array();
	        foreach ($data as $key => $value)
	        {
	            $result[$key] = $this->object_to_array($value);
	        }
	        return $result;
	    }
	    return $data;
	}

	function picsell_protected_logout(){

		setcookie( 'wp-postpass_' . COOKIEHASH, stripslashes( '' ), time() - 864000, COOKIEPATH, COOKIE_DOMAIN );

	}

	public static function _get_header($name=null, $args = array()) {

		$require_once = true;
		$templates = array();
	
		$name = (string) $name;
		if ('' !== $name) {
			$templates[] = "header-{$name}.php";
		} 
	
		$templates[] = 'header.php';
	
		$located = '';
		foreach ($templates as $template_name) {
	
			if (!$template_name) {
				continue;
			}

			if (file_exists(PIC_SELL_TEMPLATE_DIR . $template_name)) {
				$located = PIC_SELL_TEMPLATE_DIR . $template_name;
				break;
			} elseif (file_exists(STYLESHEETPATH . '/' . $template_name)) {
				$located = STYLESHEETPATH . '/' . $template_name;
				break;
			} elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {
				$located = TEMPLATEPATH . '/' . $template_name;
				break;
			} elseif (file_exists(ABSPATH . WPINC . '/theme-compat/' . $template_name)) {
				$located = ABSPATH . WPINC . '/theme-compat/' . $template_name;
				break;
			}
		}
	
		if ('' !== $located) {
			load_template($located, $require_once, $args);
		}
	
		return $located;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		//global $post;
		$post_type = get_queried_object();
		if (isset($post_type->post_type) && 'espaceprive' == $post_type->post_type || is_post_type_archive('espaceprive')) {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pic-sell-public.css', array(), $this->version, 'all');
			wp_enqueue_style("font-awesome_css", plugin_dir_url(__FILE__) . 'css/font-awesome.min.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		//global $post;
		$post_type = get_queried_object();
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pic-sell-public.js', array('jquery'), $this->version, false);

				/**CUSTOM TYPE espaceprive only and user online */
				if (isset($post_type->post_type) && 'espaceprive' == $post_type->post_type && !post_password_required() && is_single()) { 

					$isArchive = is_post_type_archive('espaceprive')?true:false;

					if(!$isArchive){
						wp_enqueue_script( 'jquery' );
					
						wp_enqueue_script('picsell_logout_js', plugin_dir_url(__FILE__) . 'js/logout.js', array('jquery'), $this->version, false);
						wp_localize_script( 'picsell_logout_js', 'picsell_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );					
						
						$vars = array(
							'post' => (array)$post_type,
							'url_include' => PIC_SELL_URL_INC,
							'ajaxurl' => admin_url( 'admin-ajax.php' ),
							'dir_include_img' => wp_upload_dir()["basedir"],
							'url_include_img' => wp_upload_dir()["baseurl"],
							'display_images_url' => get_post_type_archive_link('picimage'),
							'display_videos_url' => get_post_type_archive_link('picvideo')

						);

						wp_enqueue_script('psvars', plugin_dir_url(__FILE__) . 'js/script2.js', array('jquery'), $this->version, false);
						wp_localize_script('psvars', 'PicSellVars', $vars);
					}
				}
	}

	public function add_async_attribute($tag, $handle) {
		if ( 'psvars' !== $handle )
			return $tag;
		return str_replace( ' src', ' async="async" src', $tag );
	}

	public function add_custom_type_offre()
	{
		add_theme_support( 'post-thumbnails' );

		$labels = array(
			'name' => 'Packs Offres',
			'singular_name' => 'Pack offre',
			'add_new' => 'Ajouter un pack d\'offre',
			'add_new_item' => 'Ajouter un pack d\'offre',
			'edit_item' => 'Modifier un pack d\'offre',
			'new_item' => 'Nouveau pack d\'offre',
			'all_items' => 'Tout les packs d\'offres',
			'view_item' => 'Voir les packs d\'offres',
			'search_items' => 'Chercher un pack d\'offre',
			'not_found' =>  'Pas de pack d\'offre',
			'not_found_in_trash' => 'No Products found in Trash',
			'parent_item_colon' => '',
			'menu_name' => 'Packs d\'offres',
		);

		// register post type
		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => 'offres'),
			'query_var' => true,
			'menu_icon' => 'dashicons-admin-post',
			'supports' => array(
				'title',
			)
		);
		register_post_type('offre', $args);

		remove_post_type_support( 'offre', 'comment' );

		// register taxonomy
		register_taxonomy('offre_category', 'offre', 
			array(
				'hierarchical' => true, 
				'label' => 'Categorie', 
				'show_in_menu' => false,
				'show_in_rest' => false,
				'show_in_nav_menus' => false,
				'show_ui' => true,
				'show_tagcloud' => false,
				'show_in_quick_edit' => false,
				'meta_box_cb' => false,
				'query_var' => true, 
				'rewrite' => array('slug' => 'offre-category')
				)
			);
	
		add_action( 'add_meta_boxes', function() {

			remove_meta_box( 'wpseo_meta', 'offre', 'normal' );
			remove_meta_box( 'wpseo_meta', 'offre_category', 'normal' );

		}, 100 );

		add_filter( 'manage_edit-offre_columns', [$this,'pic_remove_wp_seo_columns'], 10, 1 );
		add_filter( 'manage_edit-offre_category_columns', [$this, 'pic_remove_wp_seo_columns'], 10, 1 );

	}


	public function pic_add_cpt_video(){
		// register post type
		$labels = array(
			'name' => 'Video Pic Sell',
			'singular_name' => 'picvideo',
			'add_new' => 'Ajouter une vidéo pic',
			'add_new_item' => 'Ajouter un vidéo picé',
			'edit_item' => 'Modifier un vidéo pic',
			'new_item' => 'Nouvel vidéo pic',
			'all_items' => 'Tous les vidéo pic',
			'view_item' => 'Voir l\'vidéo pic',
			'search_items' => 'Chercher les vidéo pic',
			'not_found' =>  'Pas d\'vidéo pic',
			'not_found_in_trash' => 'No Space private found in Trash',
			'parent_item_colon' => '',
			'menu_name' => 'vidéo pic',
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
			'capability_type' => 'post',
			'show_in_rest'    => true,
			'hierarchical'        => false,
			'rewrite' => array('slug' => 'pic-video'),
			'query_var' => true,
			'menu_icon' => 'dashicons-admin-post',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
			)
		);
		$register = register_post_type('picvideo', $args);
	
		add_rewrite_tag( '%name_vid%','([^&]+)' );
		add_rewrite_tag( '%dir_vid%','([^&]+)' );
		
		add_rewrite_rule(
		  '^pic-video/([^/]*)/([^/]*)/?',
		  'index.php?post_type=picvideo&name_vid=$matches[1]&dir_vid=$matches[2]',
		  'top'
		);
		//flush_rewrite_rules(); //permet de valider la suppression de l'archive page
	}

	public function pic_add_cpt_image(){
		// register post type
		$labels = array(
			'name' => 'Image Pic Sell',
			'singular_name' => 'picimage',
			'add_new' => 'Ajouter une image pic',
			'add_new_item' => 'Ajouter une image picé',
			'edit_item' => 'Modifier une image pic',
			'new_item' => 'Nouvelle image pic',
			'all_items' => 'Toute les images pic',
			'view_item' => 'Voir l\'image pic',
			'search_items' => 'Chercher les images pic',
			'not_found' =>  'Pas d\'image pic',
			'not_found_in_trash' => 'No Space private found in Trash',
			'parent_item_colon' => '',
			'menu_name' => 'image pic',
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
			'capability_type' => 'post',
			'show_in_rest'    => true,
			'hierarchical'        => false,
			'rewrite' => array('slug' => 'pic-image'),
			'query_var' => true,
			'menu_icon' => 'dashicons-admin-post',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
			)
		);
		$register = register_post_type('picimage', $args);
	
		add_rewrite_tag( '%name_img%','([^&]+)' );
		add_rewrite_tag( '%dir_img%','([^&]+)' );
		
		add_rewrite_rule(
		  '^pic-image/([^/]*)/([^/]*)/?',
		  'index.php?post_type=picimage&name_img=$matches[1]&dir_img=$matches[2]',
		  'top'
		);
		//flush_rewrite_rules(); //permet de valider la suppression de l'archive page
	}


	public function add_custom_type_private_space()
	{
		add_theme_support( 'post-thumbnails' );

		$labels = array(
			'name' => __("Private space", "pic_sell_plugin"),
			'singular_name' => __("Private space", "pic_sell_plugin"),
			'add_new' => __("Add private space", "pic_sell_plugin"),
			'add_new_item' => __("Add private space", "pic_sell_plugin"),
			'edit_item' => __("Edit private space", "pic_sell_plugin"),
			'new_item' => __("New private space", "pic_sell_plugin"),
			'all_items' => __("All private spaces", "pic_sell_plugin"),
			'view_item' => __("View private space", "pic_sell_plugin"),
			'search_items' => __("Search private spaces", "pic_sell_plugin"),
			'not_found' =>  __("Not private space", "pic_sell_plugin"),
			'not_found_in_trash' => __("No private space found in Trash", "pic_sell_plugin"),
			'parent_item_colon' => '',
			'menu_name' => __("Private spaces", "pic_sell_plugin"),
		);

		// register post type
		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array('slug' => 'espace-prive'),
			'query_var' => true,
			'menu_icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg  xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512" fill="#AAAAAA"><path d="m387 117l-113-114c-2-2-4-3-7-3c0 0 0 0 0 0c-3 0-6 1-8 3l-114 114l-145 0l0 395l512 0l0-395z m-120-91l90 91l-182 0z m224 465l-470 0l0-352l470 0z m-22-331l-426 0l0 309l426 0z m-405 21l384 0l0 128l-53 0c-6 0-11 5-11 11l0 53l-32 0c-6 0-11 5-11 11l0 64l-37 0c-17-22-101-133-101-235c0-6-5-10-11-10c-6 0-11 4-11 10c0 101-84 213-101 235l-16 0z m320 267l-21 0l0-53l21 0z m-241-53l98 0c14 22 27 41 36 53l-170 0c9-12 22-31 36-53z m12-22c14-26 28-57 37-89c9 32 23 63 37 89z m250 75l0-117l43 0l0 117z"></path></svg>'),
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
			)
		);
		register_post_type('espaceprive', $args);
		
		remove_post_type_support( 'espaceprive', 'comment' );

		add_filter( 'comments_open', function( $open, $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( $post_type == 'espaceprive' || $post_type == 'offre' ) {
				return false;
			}
			return $open;
		}, 99 , 2 );
		// register taxonomy
		//register_taxonomy('espaceprive_category', 'espaceprive', array('hierarchical' => true, 'label' => 'Categorie', 'query_var' => true, 'rewrite' => array('slug' => 'espaceprive-category')));
	
		add_action( 'add_meta_boxes', function() {

			remove_meta_box( 'wpseo_meta', 'espaceprive', 'normal' );
			remove_meta_box( 'wpseo_meta', 'espaceprive_category', 'normal' );

		}, 100 );

		add_filter( 'manage_edit-espaceprive_columns', [$this,'pic_remove_wp_seo_columns'], 10, 1 );
		add_filter( 'manage_edit-espaceprive_category_columns', [$this, 'pic_remove_wp_seo_columns'], 10, 1 );
	
	}

	// Remove Yoast analysis tools and filters
	public function pic_remove_wp_seo_page_analysis() {
		global $wpseo_meta_columns;
		$current_screen = get_current_screen();
		if ( 'edit-offre' === $current_screen->id || 'edit-offre_category' === $current_screen->id ||
			'edit-espaceprive' === $current_screen->id || 'edit-espaceprive_category' === $current_screen->id ) {
			// Suppression de l'analyse de lisibilité
			add_filter( 'wpseo_use_page_analysis', '__return_false' );
			// Suppression des filtres
			if ( $wpseo_meta_columns  ) {
				remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown' ) );
				remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown_readability' ) );
			}
		}
	}

	public function pic_remove_wp_seo_columns( $columns ) {
		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-score-readability'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );
		unset( $columns['wpseo-links'] );
		unset( $columns['wpseo-linked'] );
		return $columns;
	}

	public function pic_add_template($single_template)
	{
		$post_type = get_queried_object();
		$file = PIC_SELL_TEMPLATE_DIR . 'single-' . $post_type->post_type . '.php';
		if (file_exists($file)) $single_template = $file;

		return $single_template;
	}

	public function pic_add_template_archive($archive_template)
	{
		$post_type = get_queried_object();
		$file = PIC_SELL_TEMPLATE_DIR . 'archive-' . $post_type->name . '.php';
		if (file_exists($file)) $archive_template = $file;

		return $archive_template;
	}
}

if(!function_exists("_get_header")){

	function _get_header($name=null, $args=array()){
		return Pic_Sell_Public::_get_header($name, $args);
	}

}
