<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class PIC_Template_Mail
{

    public function get_pack_offer($post_id){
        /**
         * GET ID PACK OFFER
         */
        $pack_offers = get_post_meta($post_id, 'produit_client', true);
        if(empty($pack_offers)){
            $args = array(
                'post_type'        => 'offre',
                'post_status'      => 'publish',
            );
            $products = get_posts( $args );
            foreach($products as $product){
                $def = get_post_meta($product->ID, '_pack_offer_default', true);
                if($def){
                $pack_offers = $product->ID;
                }
            }
            if(empty($pack_offers)){
                $pack_offers = $products[0]->ID;
            }
        }
        return $pack_offers;
    }

    public function get_gallery($post_id){
        $myGalerie=[];
        $gallery_data = get_post_meta($post_id, '_gallery_data', true);
        $count_gallery = isset($gallery_data["classement"])?count($gallery_data["classement"]):0;

        if ($count_gallery > 0) {
            for ($i = 0; $i < $count_gallery; $i++) {
                $line=[];
                $line['id'] = $gallery_data['classement'][$i];
                $line['url'] = $gallery_data['media_dir'][$i];
                $line['titre'] = $gallery_data['media_title'][$i];
                $line['type'] = $gallery_data['choice'][$i];
                $line['description'] = $gallery_data['media_desc'][$i];
                $myGalerie[] = $line; 
            }
        }
        return $myGalerie;
    }

    private function get_data64_other($img){
        $img_source = $img; // image path/name
        $type = pathinfo($img_source, PATHINFO_EXTENSION);
        $img_binary = fread(fopen($img_source, "r"), filesize($img_source));
        $img_string = base64_encode($img_binary);
       // return "data:image/$type;base64,".$img_string; 
        return "data:image/png;base64,".$img_string; 
    }

    private function get_data64($img){
        set_time_limit(0);

        $bmedia = wp_upload_dir()["basedir"] . $img;
        $type = pathinfo($bmedia, PATHINFO_EXTENSION);

        $finfo = new finfo(FILEINFO_MIME); // Retourne le type mime
        /* Récupère le mime-type d'un fichier spécifique */
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
        $resizeImage = imagescale( $resizeImage, 100 );
        imagejpeg($resizeImage);
        $contents =  ob_get_contents();
        ob_end_clean();

            $theme_image_enc_little = base64_encode($contents);

            //$base64 = 'data:' . $genre_media . '/' . $type . ';base64,' . base64_encode($data);
            $base64 = 'data:' . $genre_media . '/' . $type . ';base64,' .$theme_image_enc_little;
        } 
        return $base64;
    }

    public function templateGalleryDateLeft(){

        $template = get_option('template_pic'); 
        $template_galery_dateleft_default = '<p>Bonjour,</p>
        <p>Votre espace photo "{{title}}" créé le {{datecreate}} a une validité de {{datevalidite}}. Il expire dans {{dateleft}} à compter de cet email.</p> 
        <p>Pour rappel, votre lien d\'accès est :</p>
        <div style="background:#C9C9C9;padding:24px 12px;">
            Lien : <a style="color: #0d6efd" href="{{permalink}}?utm_source=referral&utm_medium=email&utm_campaign=galleryIsReady&utm_content=link">{{permalink}}</a><br>
            Votre mot de passe est : <b><i>{{password}}</i></b>
        </div><br>
        <p>Je reste à votre disposition pour des informations complémentaires,<br><br>Amicalement,<br><br>
        <i>{{site_name}}</i>
        </p>';

        $template_galery_interval = isset($template["mail"]["galery_interval"]) && !empty($template["mail"]["galery_interval"])?$template["mail"]["galery_interval"]:$template_galery_dateleft_default;

       // $template_galery_dateleft = isset($template["mail"]["galery_dateleft"]) && !empty($template["mail"]["galery_dateleft"])?$template["mail"]["galery_dateleft"]:$template_galery_dateleft_default;

        $message = '<html><body style="margin: 0; padding: 0;background-color:#EEEEEE;">
        <div style="display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
            Votre galerie privée est sur le point d\'expirer.
        </div>
        <table cellspacing="0" style="margin:0 auto; width:100%; border-collapse:collapse; background-color:#EEEEEE; font-family:\'Roboto\', Arial !important">
            <tbody>
            <tr>
                <td align="center" style="padding:20px 23px 0 23px">
                    <table width="600" style="background-color:#FFF; margin:0 auto; border-radius:5px">
                        <tbody>
                        <tr>
                            <td align="center">
                                <table width="500" style="margin:0 auto">
                                    <tbody>
                                    <tr>
                                        <td align="center" style="padding:40px 0 35px 0">
                                            <a href="'.home_url().'/?utm_source=referral&amp;utm_medium=email&amp;utm_campaign=galleryDateLeft&amp;utm_content=logo" style="
                                            color: #128ced;
                                            text-decoration: none;
                                            outline: 0;" target="_blank">
                                            <img
                                            width="500"
                                            alt=""
                                            src="'.esc_url(wp_get_attachment_url(get_theme_mod('custom_logo'))).'"
                                            border="0"
                                            style="
                                            height: auto;
                                            line-height: 100%;
                                            outline: none;
                                            text-decoration: none;
                                            display: block;
                                            border-style: none;
                                            border-width: 0;
                                            "/>
                                            </a>
                                        </td>
                                    </tr>                            
                                    <tr>
                                        <td align="center" style="font-family:\'Roboto\', Arial !important">
                                            <h2 style="margin:0; font-weight:bold; font-size:40px; color:#444; text-align:center; font-family:\'Roboto\', Arial !important">
                                                Plus que quelques jours avant la clôture de votre galerie.
                                            </h2>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>            
                            <td align="center" style="padding:0 0 15px 0; font-family:\'Roboto\';>
                                <img
                                width="50%"
                                src="{{img}}"
                                alt="mise en avant"
                                height="auto"
                                style="
                                height: auto;
                                line-height: 100%;
                                outline: none;
                                text-decoration: none;
                                display: block;
                                border-style: none;
                                border-width: 0;
                                "
                            />
                            </td>
                        </tr>     
                        <tr>
                            <td align="justify" style="padding:29px 30px 50px 30px;">
                                <table style="width:100%">
                                    <tbody>
                                        '.$template_galery_interval.'
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </body>
        </table>
      
      
    </body>
    </html>';

    return $message;

    }

    public function templateGalleryReady(){

        $template = get_option('template_pic'); 
        $template_galery_ready_default = '<p>Bonjour,</p>
        <p>Retrouvez votre séance photo, {{title}}, à cette adresse :</p><br>
        <div style="background:#C9C9C9;padding:24px 12px;">
          Lien : <a style="color: #0d6efd" href="{{permalink}}?utm_source=referral&utm_medium=email&utm_campaign=galleryIsReady&utm_content=link">{{permalink}}</a><br>
          Votre mot de passe est : <b><i>{{password}}</i></b>
        </div><br>
        <p>Votre galerie reste accessible pendant {{dateleft}} à partir d\'aujourd\'hui.</p><br><br>
        <p><i>PS : Sur Safari, des problèmes d\'affichage peuvent survenir.</i></p>
        <p>Nous restons à votre disposition pour toutes informations complémentaires,<br><br>
        Je vous souhaite une belle journée, Prenez bien soin de vous,<br><br>
        <i>{{site_name}}</i>
        </p>';
        $template_galery_ready = isset($template["mail"]["galery_ready"]) && !empty($template["mail"]["galery_ready"])?$template["mail"]["galery_ready"]:$template_galery_ready_default;

        $message = '
        <table
        valign="top"
        role="presentation"
        border="0"
        cellpadding="0"
        cellspacing="0"
        style="
          outline: 0;
          width: 100%;
          min-width: 100%;
          height: 100%;
          font-family: Helvetica, Arial, sans-serif;
          line-height: 24px;
          font-weight: normal;
          font-size: 16px;
          box-sizing: border-box;
          color: #000000;
          margin: 0;
          padding: 0;
          border-width: 0;
        "
        bgcolor="#ffffff"
      >
        <tbody>
          <tr>
            <td valign="top" style="line-height: 24px; font-size: 16px; margin: 0" align="left">
              <div style="display: none;font-size: 1px;color: #333333;line-height: 1px;max-height: 0px;max-width: 0px;opacity: 0;overflow: hidden;">
                Votre galerie est disponible !
              </div>
      
              <table
                cellspacing="0"
                style="width: 100%; border-collapse: collapse; margin: 0 auto"
                border="0"
                cellpadding="0"
                bgcolor="#EEEEEE"
              >
                <tbody>
                  <tr>
                    <td
                      align="center"
                      style="
                        line-height: 24px;
                        font-size: 16px;
                        margin: 0;
                        padding: 20px 23px 0;
                      "
                    >
                      <table
                        width="600"
                        style="border-radius: 5px; margin: 0 auto"
                        border="0"
                        cellpadding="0"
                        cellspacing="0"
                        bgcolor="#FFF"
                      >
                        <tbody>
                          <tr>
                            <td
                              align="center"
                              style="line-height: 24px; font-size: 16px; margin: 0"
                            >
                              <table
                                width="500"
                                style="margin: 0 auto"
                                border="0"
                                cellpadding="0"
                                cellspacing="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      align="center"
                                      style="
                                        line-height: 24px;
                                        font-size: 16px;
                                        margin: 0;
                                        padding: 40px 0 35px;
                                      "
                                    >
                                      <a href="'.home_url().'/?utm_source=referral&amp;utm_medium=email&amp;utm_campaign=galleryIsReady&amp;utm_content=logo" style="
                                          color: #128ced;
                                          text-decoration: none;
                                          outline: 0;" target="_blank">
                                          <img
                                          width="500"
                                          alt=""
                                          src="'.esc_url(wp_get_attachment_url(get_theme_mod('custom_logo'))).'"
                                          border="0"
                                          style="
                                            height: auto;
                                            line-height: 100%;
                                            outline: none;
                                            text-decoration: none;
                                            display: block;
                                            border-style: none;
                                            border-width: 0;
                                          "/>
                                      </a>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td
                                      align="center"
                                      style="
                                        line-height: 24px;
                                        font-size: 16px;
                                        margin: 0;
                                      "
                                    >
                                      <h2
                                        style="
                                          font-weight: bold;
                                          font-size: 40px;
                                          color: #444;
                                          padding-top: 0;
                                          padding-bottom: 0;
                                          vertical-align: baseline;
                                          line-height: 38.4px;
                                          margin: 0;
                                        "
                                        align="center"
                                      >
                                        Votre galerie photos est disponible.
                                      </h2>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="center"
                              style="
                                line-height: 24px;
                                font-size: 16px;
                                margin: 0;
                                padding: 0 0 15px;
                              "
                            >
                              <img
                                width="50%"
                                src="{{img}}"
                                alt="mise en avant"
                                height="auto"
                                style="
                                  height: auto;
                                  line-height: 100%;
                                  outline: none;
                                  text-decoration: none;
                                  display: block;
                                  border-style: none;
                                  border-width: 0;
                                "
                              />
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="justify"
                              style="
                                line-height: 24px;
                                font-size: 16px;
                                margin: 0;
                                padding: 29px 30px 50px;
                              "
                            >
                              <table
                                style="width: 100%"
                                border="0"
                                cellpadding="0"
                                cellspacing="0"
                              >
                                <tbody>
                                  <tr>
                                    <td>
                                        '.$template_galery_ready.'
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
      ';
        return $message;
    }

    

    public function templateOrder($order, $fdp, $txn=0)
    {
        
        //print_r($order);
        $post_id = $order["user"]["cartId"];
        //echo $post_id;
        $orders='';
        $total=0;

        $products_title = array();
        $products_src = array();
        $gallery = $this->get_gallery($post_id);

        foreach ($order['cart'] as $value) {

            //print_r($value);
            /*Si la limite est égale à 1, c'est que ce n'est pas un coffret*/
            if ($value['produit']['limite'] == 1) {

                if (!in_array($value['produit']['titre'], $products_title)) {
                    $products_title[] = $value['produit']['titre'];
                    $products_src[] = $this->get_data64($value["produit"]["src"]);
                }
            } else {

                $total = $total + ($value['produit']['prix'] * $value['qtt']);
                $orders .= '
                <tr>
                    <td align="center" cellspacing="0" style="padding:0; vertical-align:middle">
                        <table width="550" style="border-collapse:collapse; background-color:#FaFaFa; margin:0 auto; border-bottom:1px solid #E5E5E5">
                            <tbody>
                                <tr>
                                    <td align="left" style="padding:15px 0 15px 15px; font-family:\'Roboto\', Arial !important" width="300">
                                        <p style="font-size:16px; text-transform:uppercase; color:#333333; margin:0; font-weight:900;">
                                        ' . htmlentities($value['produit']['titre']) . '</p>
                                        <img style="max-width:160px;height:auto;max-height:100px;" height="auto" alt="" src="' . $this->get_data64($value["produit"]["src"]) . '" border="0">
                                    </td>
                                    <td align="left" width="240">
                                        <table style="border-collapse: collapse;">
                                            <tbody>
                                            <tr>
                                                <td style="font-family:\'Roboto\', Arial !important;background-color:#128ced; text-align:center; border-radius:4px; vertical-align:middle;padding: 6px 12px;">
                                                    ' . htmlentities($value['produit']['limite']) . ' Photos
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td width="60" align="right" style="font-family:\'Roboto\', Arial !important">
                                        <p style="margin:0; font-size:14px; color:#333333;padding:0;font-family:\'Roboto\', Arial !important;text-align:center;">X' . htmlentities($value['qtt']) . ' </p>
                                    </td>
                                    <td width="80" align="right" style="font-family:\'Roboto\', Arial !important;padding-right:10px;">
                                        <p style="margin:0; font-size:14px; color:#333333;padding:0;font-family:\'Roboto\', Arial !important;text-align:right;">
                                        ' . htmlentities(($value['produit']['prix'] * $value['qtt']) . '€') .'</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style=" font-family:\'Roboto\', Arial !important;padding:0;" align="center">

                                <tr>
                                    <td style="padding:24px 0 24px 10px; text-align:left;">';
                                    $i = 1;
                
                                    foreach ($value['array_media'] as $key => $photo) {
                                        $image = [];
                                        foreach($gallery as $k => $v){
                                            if($v["id"] == $photo){
                                                $image = $v;
                                            }
                                        }
                                        $orders .= '<img alt="" height="auto" style="max-width:100px;width:100px;height:auto;margin:12px;padding:12px;" src="' . $this->get_data64($image["url"]) . '" border="0">';
                                        if ($i == 5) {
                                            $orders .= '    </td>
                                                        </tr>
                                                    </td>
                                                    <td style=" font-family:\'Roboto\', Arial !important;padding:0;" align="center">
                                                        <tr>
                                                            <td style="padding:24px 0 24px 10px; text-align:left;">';
                                            $i = 1;
                                        } else {
                                            $i++;
                                        }
                                    }
                                $orders .= '</td>
                                </tr>
                    </td>
                </tr>';
            }
        }


        foreach ($products_title as $key => $title) {

            $orders .= '<tr>
                            <td align="center" cellspacing="0" style="padding:0; vertical-align:middle">
                                <table width="550" style="border-collapse:collapse; background-color:#FaFaFa; margin:0 auto; border-bottom:1px solid #E5E5E5">
                                    <tbody>
                                        <tr>
                                            <td align="left" style="padding:15px 0 15px 15px; font-family:\'Roboto\', Arial !important" width="700">
                                            <p style="font-size:16px; text-transform:uppercase; color:#333333; margin:0; font-weight:900; font-family:\'Roboto\', Arial !important; ">
                                                ' . html_entity_decode($title) . '</p>
                                                <img style="max-width:160px;height:auto;" alt="" src="' . $products_src[$key] . '" border="0">
                                            </td>
                                            <td align="left" width="240">
                                                <table style="border-collapse: collapse;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="font-family:\'Roboto\', Arial !important;background-color:#128ced; text-align:center; border-radius:4px; vertical-align:middle;padding: 7px 12px;">

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td width="60" align="right" style="font-family:\'Roboto\', Arial !important"><p style="margin:0; font-size:14px; color:#333333;padding:0;font-family:\'Roboto\', Arial !important;text-align:center;">
                                                QUANTIT&Eacute;</p>
                                            </td>
                                            <td width="80" align="right" style="font-family:\'Roboto\', Arial !important;padding-right:10px;"><p style="margin:0; font-size:14px; color:#333333;padding:0;font-family:\'Roboto\', Arial !important;text-align:right;">
                                                PRIX</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>';

            foreach ($order['cart'] as $value) {

                if ($value['produit']['titre'] == $title) {

                    foreach ($value['array_media'] as $key => $photo) {

                        $image = [];
                        foreach($gallery as $k => $v){
                            if($v["id"] == $photo){
                                $image = $v;
                            }
                        }

                        $total = $total + ($value['produit']['prix'] * $value['qtt']);

                        //$preview = wp_get_attachment_url($photo);
                        
                        if ($value['produit']['type'] == 'photo' || $value['produit']['type'] == 'image') $preview = $this->get_data64($image["url"]);

                        $orders .= '
                        <tr>
                            <td style=" font-family:\'Roboto\', Arial !important;padding:0;" align="center">
                                <table width="550" style="border-collapse:collapse;margin: 0 auto;border-bottom: 1px solid #EBEBEB">
                                    <tbody>
                                        <tr>
                                            <td width="117" align="right" style="padding:24px 0 24px 10px; text-align:left;">
                                                <img style="max-width:100px;width:100px;height:auto;max-height:160px;" height="auto" width="100" src="' . $preview . '" border="0">
                                            </td>
                                            <td width="270" style="vertical-align:middle; padding:0 0 0 10px; font-family:\'Roboto\', Arial !important;">
                                                <p style="font-size:16px; margin:0; color:#000; line-height:20px; font-family:\'Roboto\', Arial !important">
                                                    ' . htmlentities($value['produit']['titre']) . '
                                                </p>
                                            </td>
                                            <td align="center" width="60" style="vertical-align:middle; font-family:\'Roboto\', Arial !important;padding:0;">
                                                <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important;text-align:center;">
                                                    ' . htmlentities($value['qtt']) . '
                                                </p>
                                            </td>
                                            <td align="center" width="80" style="font-family:\'Roboto\', Arial !important;padding:0 10px 0 0;">
                                                <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important;text-align:center;font-weight:bold;text-align: right;">
                                                    ' . htmlentities(($value['produit']['prix'] * $value['qtt']) . '€'). '
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>';
                    }
                }
            }
        }

        $totalTTC = $total + $fdp;

        $urlparts = parse_url(home_url());
        $domain = $urlparts['host'];
        $message = '<html>
                        <body>
                            <table width="800" cellspacing="0" style="margin:0 auto; width:100%; max-width:800px; border-collapse:collapse; background-color:#EEEEEE;">
                                <tbody>
                                    <tr>
                                        <td align="center" style="padding:20px 23px 0 23px">
                                            <table width="600" style="background-color:#FFF; margin:0 auto; border-radius:5px">
                                                <tbody>
                                                    <tr>
                                                        <td align="center">
                                                            <table width="500" style="width:500px;max-width:500px;margin:0 auto">
                                                                <tbody>
                                                                    <tr style="display: block;max-width:300px;height:auto;max-height:200px;">
                                                                        <td align="center" style="display: block;max-width:300px;height:auto;max-height:200px;">
                                                                            <a href="'.home_url().'" target="_blank" style="display: block;max-width:300px;height:auto;max-height:200px; color:#128ced; text-decoration:none;outline:0;">
                                                                                <img style="max-width:300px;height:auto;max-height:200px" height="auto" alt="" src="'.$this->get_data64_other( get_attached_file( get_theme_mod( 'custom_logo' ) ) ).'" border="0">
                                                                            </a>
                                                                        </td>
                                                                    </tr>                            
                                                                    <tr>
                                                                        <td align="center" style="font-family:\'Roboto\', Arial !important">
                                                                            <h2 style="margin:0; font-weight:bold; font-size:40px; color:#444; text-align:center; font-family:\'Roboto\', Arial !important">
                                                                                Merci pour votre commande !
                                                                            </h2>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" style="padding:0 0 15px 0; font-family:\'Roboto\', Arial !important">
                                                                            <img src="'.$this->get_data64_other(PIC_SELL_PATH_PUBLIC.'img/truck-delivery.gif').'"  style="max-width:320px;height:240px;max-height:240px;" width="320" height="240"  border="0">           
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td align="center" style="padding:0 0 20px 0; font-family:\'Roboto\', Arial !important">
                                                                            <p style="margin:10px 0; font-size:16px; color:#000; line-height:12px; font-family:\'Roboto\', Arial !important">'.
                                                                            htmlentities("Votre commande arrive très vite !")
                                                                            .'</p>
                                                                        </td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" cellspacing="0" style="padding:0 0 30px 0; vertical-align:middle">
                                                            <table width="100%" style="border-collapse:collapse; background-color:#FaFaFa; margin:0 auto; border:1px solid #E5E5E5">
                                                                <tbody>
                                                                <tr>
                                                                    <td width="60%" style="vertical-align:top; border-right:1px solid #E5E5E5">
                                                                        <table style="width:100%; border-collapse:collapse">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td style="vertical-align:top; padding:18px 18px 8px 23px; font-family:\'Roboto\', Arial !important">
                                                                                    <p style="font-size:16px; color:#333333; text-transform:uppercase; font-weight:900; margin:0; font-family:\'Roboto\', Arial !important">
                                                                                        Récapitulatif :
                                                                                    </p>
                                                                                </td>
                                                                            </tr>
                                                                            <tr style="">
                                                                                <td style="vertical-align:top; padding:0 18px 18px 23px">
                                                                                    <table width="100%" style="border-collapse:collapse">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                                    Commande #:
                                                                                                </p>
                                                                                            </td>
                                                                                            <td align="left" style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                                ' . htmlentities($order['order_id']) . '
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                                    Date de la commande :
                                                                                                </p>
                                                                                            </td>
                                                                                            <td align="left" style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                                ' . htmlentities($order['order_date']) . '
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 10px 0; font-family:\'Roboto\', Arial !important">
                                                                                                    Total de la commande :
                                                                                                </p>
                                                                                            </td>
                                                                                            <td align="left" style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 10px 0; font-family:\'Roboto\', Arial !important">
                                                                                                ' . htmlentities($totalTTC . '€') .'
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                    
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                    <td style="vertical-align:top">
                                                                        <table width="100%" style="border-collapse:collapse">
                                                                            <tbody>
                                                                            <tr>
                                                                                <td style="vertical-align:top; padding:18px 18px 8px 23px; font-family:\'Roboto\', Arial !important">
                                                                                    <p style="font-size:16px; color:#333333; text-transform:uppercase; font-weight:900; margin:0; font-family:\'Roboto\', Arial !important">
                                                                                        Adresse de livraison:
                                                                                    </p>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="vertical-align:top; padding:0 18px 18px 23px; font-family:\'Roboto\', Arial !important">
                                                                                    <table width="100%" style="border-collapse:collapse">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                                ' . htmlentities($order['user']['first_name']) . ' ' . htmlentities($order['user']['last_name']) . '
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important">
                                                                                                <p style="font-size:16px; color:#000; margin:0 0 5px 0; font-family:\'Roboto\', Arial !important">
                                                                                            ' . htmlentities($order['user']['address1']) . '<br>
                                                                                            ' . htmlentities($order['user']['address2']) . '
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                    
                                                                                        <tr>
                                                                                            <td style="font-family:\'Roboto\', Arial !important;">
                                                                                                <p style="font-size:16px; color:#000; margin:0;padding:0; font-family:\'Roboto\', Arial !important">
                                                                                                ' . $order['user']['zip'] . ' ' . $order['user']['city'] . ' (' . $order['user']['state'] . ')' . '
                                                                                                </p>
                                                                                            </td>
                                                                                        </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>               
                                                        ' . $orders . '
                                                    <tr>
                                                        <td align="center" style="padding-top:24px; padding-bottom:20px">
                                                            <table width="520" style="border-collapse:collapse">
                                                                <tbody>
                                                                <tr>
                                                                    <tr>
                                                                    <td align="right" width="425" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important">
                                                                            Total TTC :
                                                                        </p>
                                                                    </td>
                                                                    <td align="right" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important">
                                                                        ' . htmlentities($total."€") . '
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                    <td align="right" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important">
                                                                            Frais de port :
                                                                        </p>
                                                                    </td>
                                                                    <td align="right" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#000; margin:0; font-family:\'Roboto\', Arial !important">
                                                                            ' .  htmlentities($fdp."€"). '</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="right" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#000; font-weight:900; margin:0; font-family:\'Roboto\', Arial !important">
                                                                            Total de la commande :
                                                                        </p>
                                                                    </td>
                                                                    <td align="right" style="padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; color:#bc0101; font-weight:bold; margin:0; font-family:\'Roboto\', Arial !important">
                                                                        ' .  htmlentities($totalTTC."€") . '
                                                                        </p>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td align="center" style="width: 100%; padding-bottom:15px; font-family:\'Roboto\', Arial !important">
                                                                        <p style="font-size:18px; font-style: italic; color:#000; margin:0; font-family:\'Roboto\', Arial !important">
                                                                            <br>
                                                                            TVA non applicable, art. 293B du CGI.
                                                                            <br><br>'.
                                                                            ($txn>0?"<i>Si l'email ne s'affiche pas correctement, <a href='".home_url()."/espace-prive/?commande=" . $txn . "'>cliquez ici</a></i>":"")
                                                                            
                                                                        .'</p>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>         
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding-top:29px; padding-bottom:50px">
                                            <table style="width:100%">
                                                <tbody>
                                                
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </body>
                    </html>';

        return $message;
    }

    
}
