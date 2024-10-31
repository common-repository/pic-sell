<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

_get_header();

$the_post = get_post(get_the_ID());
$password = trim($the_post->post_password);

///////////////////////////////////DISPLAY SHOOTING PRODUCTS/////////////////////////////////

/**
 * GET ID PACK OFFER
 */
$pack_offers = get_post_meta($the_post->ID, 'produit_client', true);
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

/**
 * GET PRODUCTS BY ID PACK OFFER
 */
$allProducts=[];
$produit_data = get_post_meta($pack_offers, '_offer_data', true);
$count_produit = count($produit_data["classement"]);

if ($count_produit > 0) {
  for ($i = 0; $i < $count_produit; $i++) {
    $cat = get_the_category_by_ID( $produit_data['cat'][$i] );
    $line=[];
    $line['product_id'] = $pack_offers;
    $line['id'] = $produit_data['classement'][$i];
    $line['titre'] = $produit_data['title'][$i];
    $line['cat'] = $cat;
    $line['prix'] = $produit_data['price'][$i]; 
    $line['description'] =  $produit_data['desc'][$i];
    $line['type'] = $produit_data['choice_media'][$i];
    $line['src'] = $produit_data['media'][$i];
    $line['limite'] = $produit_data['quantity'][$i] > 0 ? $produit_data['quantity'][$i] : 50;
    $line['classement'] = $produit_data['classement'][$i];
    $allProducts[] = $line;
  }
}

///////////////////////////////////GALERIE///////////////////////////////////////////////
$myGalerie=[];
$gallery_data = get_post_meta($post->ID, '_gallery_data', true);
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

/////////////////////////////////////////CONTENU/////////////////////////////////////////////

//Tant que le mdp n'est pas valide....

//On affiche le formulaire de connexion
$contenu = get_the_content();
//On ne charge pas le script de gestion de l'app 
$script='';
//On affiche en tant que titre que la page est protégée
$title='Contenu protégé par mot de passe';

//Si le mot de passe est correct
if (!post_password_required() ) {

    //On charge le vrai titre
    $title= get_the_title();

    //On vérifie que le panier n'est pas vide, c'est à dire égal à null ou "null"
    if ( get_post_meta( get_the_ID(), 'panier_client', true ) == null || get_post_meta( get_the_ID(), 'panier_client', true ) == 'null' ) $cart="[]";
    else {
      $cart =  str_replace('"[',"[", json_encode(get_post_meta( get_the_ID(), 'panier_client', true )));
      $cart = str_replace(']"',"]", $cart);
      $cart = str_replace("'","&pos;", $cart);
      $cart = str_replace("\\","", $cart);

      $cart = "'".$cart."'";
    }

    $allProducts = json_encode($allProducts);
    $allProducts = str_replace("'","&pos;", $allProducts);
    
    //LOAD SCREEN
    $contenu = "<div class='screen-loader'>
                  <h2>Votre galerie est en<br>cours de chargement</h2>
                  <div class='svg-loader'></div> 
                </div>";

    //On affiche une span avec tous les paramètres JS
    $contenu .= "<span id='params' data-products='".($allProducts)."' data-password='".$password."' data-id=".get_the_ID()." data-galerie='".json_encode($myGalerie)."' data-cart=".stripslashes($cart)."></span>";
    //On charge les balises ou s'insère la galerie masonnery
    $contenu .= '<div class="masonry gutterless"></div>';
    //Boutton de déconnexion de l'espace privé
    $contenu .= '<a class="button_pic logout" href="#"><i class="fa fa-sign-out" aria-hidden="true"></i></a>';

    //On affiche le panier
    $contenu .='<!-- Bouton du panier -->
        <div class="button_pic button_panier">
          <i class="card_icon fa fa-shopping-cart"></i>
          <span>14</span>
        </div>';

        ?>

    <div class='modal_form'>
           <div class="inner-container">
              
           </div>                                                     
    </div>

<?php
}

?>

<div class="container">
<div class="UI_Elements" style="display: none;">
  <h1>h1 Lorem</h1>
  <h2>h2 Lorem</h2>
  <h3>h3 Lorem</h3>
  <h4>h4 Lorem</h4>
  <h5>h5 Lorem</h5>
  <h6>h6 Lorem</h6>
  <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam enim rem quibusdam sapiente accusamus autem at facere labore nihil dicta fugit sed distinctio numquam dolor tempore, sequi quas eius quo.</p>
  <button class="button ajout_panier">
  <i class="card_icon fa fa-shopping-cart"></i>
    Ajouter
  </button>

  <button class="button commande_panier">
  <i class="card_icon fa fa-shopping-cart"></i> Commander
  </button>

  <button class="button select">
  <i class="card_icon fa fa-shopping-cart"></i>
  </button>
  <button class="button select active">
  <i class="card_icon fa fa-shopping-cart"></i>
  </button>
  <button class="button select button_small">
  <i class="card_icon fa fa-shopping-cart"></i>
  </button>
  <button class="button select button_small active">
  <i class="card_icon fa fa-shopping-cart"></i>
  </button>

  <div class="button close"></div>

  <hr>

  <template id="template_select_product">

    <div class="product" data-index="{INDEX}" data-id_produit="{ID_PRODUIT}" data-id_media="{ID_MEDIA}" data-cat="{CAT}">
      
      <div class="product_head">
        <span class="product_titre">{TITRE}</span> 
      </div>  

      <div class="product_image" style="background-image: url('{IMAGE_SRC}')">
        <div class="product_more_info">
          <i class="fa fa-eye" aria-hidden="true"></i>
        </div>
      </div>

      <span class="product_price">{PRIX}€</span>
      
      <button class="button select button_small">
      <i class="card_icon fa fa-plus"></i>
      </button>

    </div>

  </template>


  <template id="template_select_product_active">

    <div class="product active" data-index="{INDEX}" data-id_produit="{ID_PRODUIT}" data-id_media="{ID_MEDIA}" data-cat="{CAT}">

      <div class="product_head">
        <span class="product_titre">{TITRE}</span>
        <span class="product_price">{PRIX}€</span>
      </div>

      <div class="product_image" style="background-image: url('{IMAGE_SRC}')">
        <div class="product_more_info">
          <i class="fa fa-eye" aria-hidden="true"></i>
        </div>
      </div>

      <button class="button select button_small active">
      <i class="card_icon fa fa-check"></i>
      </button>

    </div>

  </template>
  
  <template id="template_panier_article">
    <div class="panier_article" data-index="{INDEX}" data-role="drag-drop-container"  >
      <div class="article_photo" style="background-image: url('{IMAGE_SRC}')"></div>
      <div class="article_contenu">
        <div class="article_titre">{TITRE}</div>
        <div class="article_prix">{PRIX}€</div>
        <div class="article_qtt">
          <p>QTT</p>
          <span class="moins">-</span>
          <span class="nb">{QTT}</span>
          <span class="plus">+</span>
        </div>
        <span class="search fa fa-plus-circle"></span>
      </div>
      <div class="pack_nb" style="display: {DISPLAY_X_SUR_MAX}">{X_SUR_MAX}</div>
    </div>
  </template>
  
  <template id="template_panier_item_photo_section">
    <div class="item_photo" data-id_media="{ID_MEDIA}">
      <div class="photo" style="background-image: url('{IMAGE_SRC}'); display: {IMAGE_DISPLAY};"></div>
      <video class="video" muted style="display: {VIDEO_DISPLAY};">
        <source autoplay="" src="{VIDEO_SRC}" type="video/mp4">
        Sorry, your browser doesn't support embedded videos.
      </video>
      <div class="button supprimer"><i>X</i>supprimer</div>
    </div>
  </template>

  <template id="template_form_livraison">
     <div class="button close"><i class="fa fa-times-circle"></i></div>

      <form action="#" method="post"  class="form_checkout">
      
      <input type="hidden" name="cartId" value="<?php echo get_the_ID(); ?>">
      <input type="hidden" name="password" value="<?php echo sanitize_text_field($password) ?>">
      <input type="hidden" name="id_pack" value="<?php echo intval(sanitize_text_field($pack_offers)); ?>">
      
      <input type="hidden" name="etat" value="France">
      <h2>Adresse de livraison</h2>
      <div class="flex-container">

        <div class="col">

          <p>
            <label for="nom">Votre nom <abbr class="required" title="requis">*</abbr></label> 
            <input required name="nom" class="" type="text" id="nom" value="" />
          </p>

          <p>
            <label for="prenom">Prénom <abbr class="required" title="requis">*</abbr></label> 
            <input name="prenom" required class="" type="text" id="prenom" value="" />
          </p>

          <p>
            <label for="tel">N° Téléphone <abbr class="required" title="requis">*</abbr></label> 
            <input required name="phone" class="text_input is_phone" type="text" id="tel" value="" />
          </p>

          <p>
            <label for="email">Adresse e-mail <abbr class="required" title="requis">*</abbr></label>
            <input name="email" class="text_input is_email" type="email" id="email" value="" />
          </p>

        </div>

        <div class="col">

          <p>
            <label for="adresse">Votre adresse <abbr class="required" title="requis">*</abbr></label>
            <input required name="adresse" class="" type="text" id="adresse" value="" />
          </p>

          <p>
            <label for="adresse2">Complément d'adresse </label> 
            <input name="adresse2" class="" type="text" id="adresse2" value="" />
          </p>

          <p>
            <label for="cp">Votre code postale <abbr class="required" title="requis">*</abbr></label>
            <input required name="cp" class="" type="text" id="cp" value="" />
          </p>

          <p>
            <label for="ville">Votre ville </label>
            <input required name="ville" class="" type="text" id="ville" value="" />
          </p>

        </div>

      </div>
      
      <select required name="pays" class="" style="margin:0 12px;width: auto;" id="pays" aria-required="true" aria-invalid="false"><option value="FR">France</option><option value="AF">Afghanistan</option><option value="ZA">Afrique du Sud</option><option value="AL">Albanie</option><option value="DZ">Algérie</option><option value="DE">Allemagne</option><option value="AD">Andorre</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctique</option><option value="AG">Antigua-et-Barbuda</option><option value="SA">Arabie saoudite</option><option value="AR">Argentine</option><option value="AM">Arménie</option><option value="AW">Aruba</option><option value="AU">Australie</option><option value="AT">Autriche</option><option value="AZ">Azerbaïdjan</option><option value="BS">Bahamas</option><option value="BH">Bahreïn</option><option value="BD">Bangladesh</option><option value="BB">Barbade</option><option value="BE">Belgique</option><option value="BZ">Belize</option><option value="BJ">Bénin</option><option value="BM">Bermudes</option><option value="BT">Bhoutan</option><option value="BY">Biélorussie</option><option value="BO">Bolivie</option><option value="BA">Bosnie-Herzégovine</option><option value="BW">Botswana</option><option value="BR">Brésil</option><option value="BN">Brunéi Darussalam</option><option value="BG">Bulgarie</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodge</option><option value="CM">Cameroun</option><option value="CA">Canada</option><option value="CV">Cap-Vert</option><option value="EA">Ceuta et Melilla</option><option value="CL">Chili</option><option value="CN">Chine</option><option value="CY">Chypre</option><option value="CO">Colombie</option><option value="KM">Comores</option><option value="CG">Congo-Brazzaville</option><option value="CD">Congo-Kinshasa</option><option value="KP">Corée du Nord</option><option value="KR">Corée du Sud</option><option value="CR">Costa Rica</option><option value="CI">Côte d’Ivoire</option><option value="HR">Croatie</option><option value="CU">Cuba</option><option value="CW">Curaçao</option><option value="DK">Danemark</option><option value="DG">Diego Garcia</option><option value="DJ">Djibouti</option><option value="DM">Dominique</option><option value="EG">Égypte</option><option value="SV">El Salvador</option><option value="AE">Émirats arabes unis</option><option value="EC">Équateur</option><option value="ER">Érythrée</option><option value="ES">Espagne</option><option value="EE">Estonie</option><option value="VA">État de la Cité du Vatican</option><option value="FM">États fédérés de Micronésie</option><option value="US">États-Unis</option><option value="ET">Éthiopie</option><option value="FJ">Fidji</option><option value="FI">Finlande</option><option value="FR">France</option><option value="GA">Gabon</option><option value="GM">Gambie</option><option value="GE">Géorgie</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Grèce</option><option value="GD">Grenade</option><option value="GL">Groenland</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernesey</option><option value="GN">Guinée</option><option value="GQ">Guinée équatoriale</option><option value="GW">Guinée-Bissau</option><option value="GY">Guyana</option><option value="GF">Guyane française</option><option value="HT">Haïti</option><option value="HN">Honduras</option><option value="HU">Hongrie</option><option value="CX">Île Christmas</option><option value="AC">Île de l’Ascension</option><option value="IM">Île de Man</option><option value="NF">Île Norfolk</option><option value="AX">Îles Åland</option><option value="KY">Îles Caïmans</option><option value="IC">Îles Canaries</option><option value="CC">Îles Cocos</option><option value="CK">Îles Cook</option><option value="FO">Îles Féroé</option><option value="GS">Îles Géorgie du Sud et Sandwich du Sud</option><option value="FK">Îles Malouines</option><option value="MP">Îles Mariannes du Nord</option><option value="MH">Îles Marshall</option><option value="UM">Îles mineures éloignées des États-Unis</option><option value="SB">Îles Salomon</option><option value="TC">Îles Turques-et-Caïques</option><option value="VG">Îles Vierges britanniques</option><option value="VI">Îles Vierges des États-Unis</option><option value="IN">Inde</option><option value="ID">Indonésie</option><option value="IQ">Irak</option><option value="IR">Iran</option><option value="IE">Irlande</option><option value="IS">Islande</option><option value="IL">Israël</option><option value="IT">Italie</option><option value="JM">Jamaïque</option><option value="JP">Japon</option><option value="JE">Jersey</option><option value="JO">Jordanie</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KG">Kirghizistan</option><option value="KI">Kiribati</option><option value="XK">Kosovo</option><option value="KW">Koweït</option><option value="RE">La Réunion</option><option value="LA">Laos</option><option value="LS">Lesotho</option><option value="LV">Lettonie</option><option value="LB">Liban</option><option value="LR">Libéria</option><option value="LY">Libye</option><option value="LI">Liechtenstein</option><option value="LT">Lituanie</option><option value="LU">Luxembourg</option><option value="MK">Macédoine</option><option value="MG">Madagascar</option><option value="MY">Malaisie</option><option value="MW">Malawi</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malte</option><option value="MA">Maroc</option><option value="MQ">Martinique</option><option value="MU">Maurice</option><option value="MR">Mauritanie</option><option value="YT">Mayotte</option><option value="MX">Mexique</option><option value="MD">Moldavie</option><option value="MC">Monaco</option><option value="MN">Mongolie</option><option value="ME">Monténégro</option><option value="MS">Montserrat</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibie</option><option value="NR">Nauru</option><option value="NP">Népal</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigéria</option><option value="NU">Niue</option><option value="NO">Norvège</option><option value="NC">Nouvelle-Calédonie</option><option value="NZ">Nouvelle-Zélande</option><option value="OM">Oman</option><option value="UG">Ouganda</option><option value="UZ">Ouzbékistan</option><option value="PK">Pakistan</option><option value="PW">Palaos</option><option value="PA">Panama</option><option value="PG">Papouasie-Nouvelle-Guinée</option><option value="PY">Paraguay</option><option value="NL">Pays-Bas</option><option value="BQ">Pays-Bas caribéens</option><option value="PE">Pérou</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Pologne</option><option value="PF">Polynésie française</option><option value="PR">Porto Rico</option><option value="PT">Portugal</option><option value="QA">Qatar</option><option value="HK">R.A.S. chinoise de Hong Kong</option><option value="MO">R.A.S. chinoise de Macao</option><option value="CF">République centrafricaine</option><option value="DO">République dominicaine</option><option value="CZ">République tchèque</option><option value="RO">Roumanie</option><option value="GB">Royaume-Uni</option><option value="RU">Russie</option><option value="RW">Rwanda</option><option value="EH">Sahara occidental</option><option value="BL">Saint-Barthélemy</option><option value="KN">Saint-Christophe-et-Niévès</option><option value="SM">Saint-Marin</option><option value="MF">Saint-Martin (partie française)</option><option value="SX">Saint-Martin (partie néerlandaise)</option><option value="PM">Saint-Pierre-et-Miquelon</option><option value="VC">Saint-Vincent-et-les-Grenadines</option><option value="SH">Sainte-Hélène</option><option value="LC">Sainte-Lucie</option><option value="WS">Samoa</option><option value="AS">Samoa américaines</option><option value="ST">Sao Tomé-et-Principe</option><option value="SN">Sénégal</option><option value="RS">Serbie</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapour</option><option value="SK">Slovaquie</option><option value="SI">Slovénie</option><option value="SO">Somalie</option><option value="SD">Soudan</option><option value="SS">Soudan du Sud</option><option value="LK">Sri Lanka</option><option value="SE">Suède</option><option value="CH">Suisse</option><option value="SR">Suriname</option><option value="SJ">Svalbard et Jan Mayen</option><option value="SZ">Swaziland</option><option value="SY">Syrie</option><option value="TJ">Tadjikistan</option><option value="TW">Taïwan</option><option value="TZ">Tanzanie</option><option value="TD">Tchad</option><option value="TF">Terres australes françaises</option><option value="IO">Territoire britannique de l’océan Indien</option><option value="PS">Territoires palestiniens</option><option value="TH">Thaïlande</option><option value="TL">Timor oriental</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinité-et-Tobago</option><option value="TA">Tristan da Cunha</option><option value="TN">Tunisie</option><option value="TM">Turkménistan</option><option value="TR">Turquie</option><option value="TV">Tuvalu</option><option value="UA">Ukraine</option><option value="UY">Uruguay</option><option value="VU">Vanuatu</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="WF">Wallis-et-Futuna</option><option value="YE">Yémen</option><option value="ZM">Zambie</option><option value="ZW">Zimbabwe</option></select>
  
      <p class="blue"><i><b>Les frais de port sont offerts en France métropolitaine</b></i></p>
      <input type="submit" value="Passer au paiement (paypal)" class="validate_commande button submit"  data-sending-label="Envoi"/></p>
        

    </form>
  </template>

</div>


<?php 
echo wp_kses($contenu, _pic_allowed_tags_all());
?>

<!-- Panier -->
<div class="panier">
  <div class="panier_head">
    <h2>Mon panier</h2>
    <div class="button close"><i class="fa fa-times-circle"></i></div>
  </div>
  <div class="panier_articles" data-role="drag-drop-container">
  </div>
  <div class="drop_zone" data-role="drag-drop-container"]>
    Ajouter un produit
  </div>
  <div class="panier_recap">
    <p class="montant">Montant: 120 €</p>
      <input type="hidden" name="cartId" value="<?php the_ID() ?>">
      <input type="hidden" name="password" value="<?php echo sanitize_text_field($password); ?>">
      <button class="button commande_panier">
        <i class="card_icon fa fa-shopping-cart"></i> Commander
      </button>
  </div>
  <div class="item_presentation" data-role="drag-drop-container">
    <div class="item_presentation_photo"></div>
    <h3 class="item_presentation_titre">{TITRE}</h3>
    <div class="item_presentation_button button close"><i class="fa fa-times-circle"></i></div>
    <div class="item_photo_button active">Photos</div>
    <div class="item_description_button">Description</div>
    <div class="item_photo_section active">
    </div>
    <div class="item_description_section">{DESCRIPTION}</div>
  </div>
</div>

<!-- Modal pour séléctionner le produit -->
<div id="product_modal" class="modal">
  <div class="button close"><i class="fa fa-times-circle"></i></div>
  <div id="content_select" class="modal-content">
    <span id="titre_photo"></span>
    <img id="product_modal_img" src="" width="15%" style="display: none;">
    <video id="product_modal_vid" controls muted style="display: none;">
      <source src="" type="video/mp4">
      Sorry, your browser doesn't support embedded videos.
    </video>
    <p id="titre">Choix du/des produit(s)</p>
    <p id="desc_selection_ablum">Ajouter à un album existant</p>
    <div id="select_albums">
    </div>
    <p id="desc_selection_produits">Sélectionnez un produit</p>
    <div class="select_cat">
    </div>
    <div id="select_products">
    </div>
  </div>
  <div id="content_description" class="modal-content">
    <img class="content_description_image" src="" alt=""/>
    <p class="description"></p>
    <a class="fermer" href='#'>Fermer</a>
  </div>
</div>

<!-- Visionneuse -->
<div id="visionneuse">
  <div id="visionneuse_close" class="button close"><i class="fa fa-times-circle"></i></div>
  <img id="visionneuse_img" src="" alt="">
  <span id="before"><</span>
  <span id="after">></span>
  <video id="visionneuse_vid" controls muted>
    <source src="" type="video/mp4">
    Sorry, your browser doesn't support embedded videos.
  </video>
  <button id="ajout_panier" class="button_pic ajout_panier">
  <i class="card_icon fa fa-shopping-cart"></i>
    Ajouter
  </button>
</div>
<div id="alert" class="modal">
  <div class="button close"><i class="fa fa-times-circle"></i></div>
  <div class="modal-content">
    <p id="message"></p>
  </div>
</div>
</div>

<?php
if (post_password_required() ) {
  wp_footer();
}