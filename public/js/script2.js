jQuery(function ($) {
  $.fn.animateRotate = function (angle, duration, easing, complete) {
    var args = $.speed(duration, easing, complete);
    var step = args.step;
    return this.each(function (i, e) {
      args.step = function (now) {
        $.style(e, "transform", "rotate(" + now + "deg)");
        if (step) return step.apply(this, arguments);
      };

      $({ deg: 0 }).animate({ deg: angle }, args);
    });
  };

  sleep = function (callback, milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
      if (new Date().getTime() - start > milliseconds) {
        callback();
        break;
      }
    }
  };

  class UI {
    constructor(galerie, produits, panier) {
      return (async () => {
        var _this = this;
        this.temp = []; // mémoire tempon
        this.listeners = []; // gestion des évènements

        //LOAD SCREEN
        this.loadscreen(true);

        galerie.forEach( function (gal) {
          if (typeof gal.id == "string") gal.id = parseInt(gal.id);
          if (gal.type == "image" || gal.type == "photo") {
            //gal.base64 =   _this.convertJpgToBase64(gal.url);
            gal.base64 = _this.src_image(gal.url);
          }
        });

        this.galerie = galerie;

        produits.forEach( function (produit) {
          produit.cat = produit.cat.replace("&pos;", "'");
          produit.description = produit.description.replace("&pos;", "'");
          produit.titre = produit.titre.replace("&pos;", "'");
          if (typeof produit.prix == "string")
            produit.prix = parseFloat(produit.prix.replace(",", "."));
        //  produit.base64 =  _this.convertJpgToBase64(produit.src);
          produit.base64 =  _this.src_image_product(produit.src, produit.product_id);
          if (typeof produit.id == "string") produit.id = parseInt(produit.id);
        });
        this.produits = produits;

        panier.articles.forEach(function (article, index) {
          var current_array = article.array_media;
          article.array_media = [];
          current_array.forEach(function (media, index) {
            article.array_media.push(parseInt(media));
          });
        });
        this.panier = panier; // instance de l'objet Panier

        this.modal = document.getElementById("product_modal");
        this.visionneuse = $("#visionneuse"); // création de la galerie Masonry

        panier.enrichissementArticles();
        panier.updatePanier();
        this.closePanier();

        $.when(this.createMasonry()).done(function () {
          console.log("GALERIE FINISH INIT");
          _this.loadscreen(false);
        });
        return this;
      })();
    }
    loadscreen(etat) {
      if (etat) {
        $(".screen-loader").show("slow");
        $("body").css({ overflow: "hidden" });
        sleep(function () {
          $(".screen-loader h2").html(
            "Votre galerie est en<br>cours de chargement"
          );
        }, 1000);
      } else {
        $(".screen-loader").hide("slow");
        $("body").css({ overflow: "auto" });
      }
    }

    convertJpgToBase64(src) {
      var data = new FormData();
      data.append("img", PicSellVars.dir_include_img + src);
      data.append("action", "post_app");
      data.append("act", "/img/base64/");

      var base = $.ajax({
        async: false,
        type: "POST",
        url: PicSellVars.ajaxurl,
        data: data,
        beforeSend: function (xhr) {
          xhr.overrideMimeType("text/plain; charset=x-user-defined");
        },
        contentType: false,
        processData: false,
        cache: false,
      });
      //var url2;
      base.then(function (result, textStatus, jqXHR) {
        var responseText = jqXHR.responseText;
        var responseTextLen = responseText.length;
      });

      var binary = "";

      for (var i = 0; i < base.responseText.length; i++) {
        binary += String.fromCharCode(base.responseText.charCodeAt(i) & 255);
      }
      var url2 = "data:image/jpg;base64," + btoa(binary);
      return url2;
    }
    
    src_image(url){
      var split = url.split("/");
      var count = split.length;
      var name = split[count-1];

      return PicSellVars.display_images_url + "?name_img="+name+"&dir_img="+PicSellVars.post.ID;
    }

    src_image_product(url, id_product){
      var split = url.split("/");
      var count = split.length;
      var name = split[count-1];

      return PicSellVars.display_images_url + "?name_img="+name+"&dir_img="+id_product;
    }

    prompt(message, type) {
      this.resetAlert();
      var $alert = $("#alert");
      this.alert(message);

      switch (type) {
        case "number":
          // to do : gestion de contraintes auto
          $("#alert").attr("data-type", "number");
          $("#alert .modal-content").append(
            '<input class="input_prompt" type="number" value=1 min=1 placeholder="1" />'
          );
          $("#alert .modal-content").append(
            '<button class="button submit">OK</button>'
          );
          break;

        case "boolean":
          $("#alert").attr("data-type", "boolean");
          $("#alert .modal-content").append(
            '<button class="button submit" data-val="true" >Oui</button>'
          );
          $("#alert .modal-content").append(
            '<button class="button submit" data-val="false" >Non</button>'
          );
          break;
      }

      return new Promise(function (resolve, reject) {
        $("#alert .close").on("click", function (event) {
          event.preventDefault();
          $("#alert").hide();
          reject();
        });
        $("#alert .submit").on("click", function (event) {
          event.preventDefault();
          var type = $("#alert").attr("data-type");
          console.log(type);

          switch (type) {
            case "number":
              var number = $(".input_prompt").val();
              console.log(number);
              if (!Number.isInteger) parseInt(number);
              resolve(number);
              $("#alert").hide();
              break;

            case "boolean":
              var bool = $(this).attr("data-val");
              console.log(bool);
              bool == "true" ? resolve(true) : resolve(false);
              $("#alert").hide();
              break;
          }
        });
      });
    }
    alert(message) {
      this.resetAlert();
      $("#alert #message").text(message);
      $("#alert").show();
    }
    resetAlert() {
      $("#alert .modal-content").html('<p id="message"></p>');
    }
    events() {

      // au click sur une photo où vidéo
      this.listeners["brick"] = $(".brick").on("click", function (event) {
        var id = $(this).data("id");
        user_interface.openViewer(id);
      });
      
      // au click sur le bouton du panier
      this.listeners["panierOpen"] = $(".button_panier, .panier .panier_head .close").on("click", function (event) {
        var panier = $(".panier");

        if (panier.hasClass("active")) {
          panier.hide("200");
          user_interface.closePanier();
        //  $(".container").width("100%");
          $("#visionneuse").width("100%");
          $("#alert").width("100%");
          $("#product_modal").width("100%");
        } else {
          panier.show("200");
          user_interface.openPanier();
          //$(".container").width(window.innerWidth - 312);
          $("#visionneuse").width(window.innerWidth - 312);
          $("#alert").width(window.innerWidth - 312);
          $("#product_modal").width(window.innerWidth - 312);
        }

        panier.toggleClass("active"); // console.log(panier);
      }); 
      
      // écran redimensionné, on réajuste l'interface
      this.listeners["modal"] = $(window).on("resize", function (event) {
        var panier = $(".panier");

        if (panier.hasClass("active")) {
          //$(".container").width(window.innerWidth - 300);
          $("#visionneuse").width(window.innerWidth - 300);
          $("#product_modal").width(window.innerWidth - 300);
          $("#alert").width(window.innerWidth - 300);
        } else {
         // $(".container").width("100%");
          $("#visionneuse").width("100%");
          $("#product_modal").width("100%");
          $("#alert").width("100%");
        }
      }); 
      
      // au niveau de la visionneuse, click sur le bouton "ajouter au panier" qui ouvre la modal de selection de l'article
      this.listeners["visionneuse_ajout_panier"] = $("#visionneuse #ajout_panier").on("click", function (event) {
        event.preventDefault();
        /* Act on the event */

        var id = $(this).data("id");
        user_interface.closeViewer();
 
        setTimeout(function(){user_interface.openSelectProduct(id)},500);
      }); 
      
      // before / after
      this.listeners["visionneuse_before"] = $("#visionneuse #before").on(
        "click",
        function (event) {
          event.preventDefault();
          /* Act on the event */

          var id_media = $("#visionneuse #ajout_panier").data("id");
          var media = user_interface.galerie.find(function (obj) {
            return obj.id === id_media;
          });
          var index = user_interface.galerie.indexOf(media);
          var new_index;

          if (index === 0) {
            new_index = user_interface.galerie.length - 1;
          } else {
            new_index = index - 1;
          }

          user_interface.openViewer(user_interface.galerie[new_index].id);
        }
      ); 
      // before / after
      this.listeners["visionneuse_after"] = $("#visionneuse #after").on(
        "click",
        function (event) {
          event.preventDefault();
          /* Act on the event */

          var id_media = $("#visionneuse #ajout_panier").data("id");
          var media = user_interface.galerie.find(function (obj) {
            return obj.id === id_media;
          });
          var index = user_interface.galerie.indexOf(media);
          var new_index;

          if (index === user_interface.galerie.length - 1) {
            new_index = 0;
          } else {
            new_index = index + 1;
          }

          user_interface.openViewer(user_interface.galerie[new_index].id);
        }
      ); 

      //close visionneuse
      this.listeners["visionneuse_close"] = $("#visionneuse #visionneuse_close").on(
          "click",
          function (event) {
            event.preventDefault();
            user_interface.closeViewer();
          }
      ),
      $(user_interface.visionneuse).on(
        "click",
        function (event) {
          event.preventDefault();
          var condition = event.target.id == "before" ? true : event.target.id == "after" ? true : event.target.id == "ajout_panier" ? true : false;
          if(condition) return;
          user_interface.closeViewer();
        }        
      );

      //peut importe la modal, au click d'un el avec la classe .close, va récupérer le parent .modal et va le fermer
      this.listeners["modal"] = $(".modal .close").on(
        "click",
        function (event) {
          event.preventDefault();
          user_interface.closeSelectProduct();
        }
      ),
      $("#product_modal").on(
        "click",
        function (event) {
          event.preventDefault();
          //console.log(event.target);
          var condition = event.target.classList.contains("product_more_info") ? true :
           event.target.classList.contains("fa-eye") ? true : //content description product
           event.target.classList.contains("product_image") ? true : //content description product
           event.target.classList.contains("fermer") ? true : //le bouton fermer de la content description product
           event.target.classList.contains("button") ? true : //button add product
           event.target.classList.contains("modal-content") ? true : //class modal content
           false;
          if(condition) return;
          user_interface.closeSelectProduct();
        }        
      );
      
      // au choix d'une catégorie, on affiche les produits concernés
      this.listeners["select_cat"] = $(".modal .select_cat").on(
        "click",
        ".button",
        function (event) {
          var cat = $(this).data("cat");
          var buttons = $(".modal .select_cat .button");
          buttons.each(function (index, el) {
            this.classList.remove("active");
          });
          this.classList.add("active");

          if (cat == "tout") {
            $("#select_products .product").hide();
            $("#select_products .product").show("20");
          } else {
            $("#select_products .product").hide();
            $('#select_products .product[data-cat="' + cat + '"]').show("20");
          }
        }
      );

      this.listeners["more_info_product_open"] = $(".modal").on(
        "click",
        ".product .product_image",
        function (event, el) {
          var id_produit = parseInt($(this).parent().attr("data-id_produit"));
          var produit = user_interface.produits.find(function (obj) {
            return obj.id === id_produit;
          });

          if ($(".panier").hasClass("active")) {
            $(".panier .button.close").click(); // correction, permet de mieux voir la description du produit en fermant le panier
          }

          $("#content_description img")[0].src = produit.base64;
          $("#content_description .description").html(produit.description);
          $("#content_description").addClass("active");
          $("#content_select").css("opacity", 0);
          $(".modal").css("overflow", "hidden");
        }
      );

      this.listeners["more_info_product_close"] = $(".modal").on(
        "click",
        "#content_description .fermer, .close, .modal",
        function (event, el) {
          $("#content_description img")[0].src = "";
          $("#content_description .description").html();
          $("#content_description").removeClass("active");
          $("#content_select").css("opacity", 1);
          $(".modal").css("overflow", "auto");
        }
      ); 

      // Lance les event d'ajout /suppression au panier
      this.listeners["Ajout_suppression"] = $(".modal").on(
        "click",
        ".product .button",
        function (event, el) {
          // event.preventDefault();

          /* Act on the event */
          // Amélioration possible : vérification de la correspondance entre l'index de l'article, l'id_media et l'id_product
          if ($(this).hasClass("active")) {
            $(document).trigger("remove_produit", [this]);
          } else {
            $(document).trigger("add_produit", [this]);
          } // console.log('clique sur un produit');
        }
      ); 
      
      // Event d'ajout au panier, gestion des différents cas
      this.listeners["add_produit"] = $(document).on(
        "add_produit",
        function (event, el) {
          // console.log('event test in UI : add_produit');
          var id_media = parseInt(user_interface.modal.dataset["id_media"]);
          var media = user_interface.galerie.find(function (obj) {
            return obj.id === id_media;
          });
          var id_produit = parseInt($(el).parent().attr("data-id_produit"));
          var produit = user_interface.produits.find(function (obj) {
            return obj.id === id_produit;
          });
          var article_index = $(el).parent().attr("data-index");
          user_interface.temp = {
            el: el,
            id_media: id_media,
            id_produit: id_produit,
            article_index: article_index, // console.log(id_media, id_produit, article_index);
            // let article = user_interface.panier.articles.find(obj => {
            //  return obj.index === article_index
            // });
            // console.log(article);

            /*
            Liste des cas possibles :
            - c'est un produit à 1 média :
              on le rajoute dans le panier => avec la classe Panier (on envoi le PRODUIT et le MEDIA) 
              on update l'élément html avec l'index de l'article
            - c'est un produit à 1+ média :
              on le rajoute dans le panier => avec la classe Panier (on envoi le PRODUIT et le MEDIA) 
              on update l'élément html avec l'index de l'article
            - c'est un produit à 1+ média déjà en cours :
              on récupère l'index et l'article
              on ajoute la photo (vérification quand même qu'elle n'est pas déjà présente) => avec la classe Panier (on envoi le PRODUIT le MEDIA et l'INDEX)
              on update l'élément html
          */
          };

          if (article_index == "null") {

            // il faut créer l'article
            user_interface
              .prompt("Sélectionnez la quantité", "number")
              .then(function (qtt) {
                qtt = parseInt(qtt);
                console.log("OK ?", qtt);
                var new_index = user_interface.panier.addItem(
                  user_interface.temp.id_produit,
                  user_interface.temp.id_media
                );
                user_interface.panier.updateItem(new_index, false, false, qtt);

                if (produit.limite > 1) {
                  // alors on créé un élément html
                  $("#desc_selection_ablum").show(); // on affiche le titre pour le choix des albums
                  var article = user_interface.panier.getItem(new_index);
                }

                $(user_interface.temp.el).parent().attr("data-index", new_index);
                $(user_interface.temp.el).addClass("active");
                $(user_interface.temp.el).parent().addClass("active");
              })
              ["catch"](function () {
                console.log("not OK T_T");
              }); // do{
            //     var qtt = parseInt(window.prompt("Combien de "+produit.titre+" souhaitez vous ? (vous pourrez toujours modifier la quantité dans le panier par la suite)", "1"), 10);
            // }while(isNaN(qtt) || qtt < 1);
            // let new_index = user_interface.panier.addItem(id_produit, id_media);
            // user_interface.panier.updateItem(new_index, false, false, qtt);
            // if (produit.limite > 1) { // alors on créé un élément html
            //  $('#desc_selection_ablum').show(); // on affiche le titre pour le choix des albums
            //  let article = user_interface.panier.getItem(new_index);
            //  let html = user_interface.html_template_select_product(id_produit, article, true);
            //  $("#product_modal #select_albums").append(html);
            //  return;
            // }
            // $(el).parent().attr('data-index', new_index);
          } else {
            var index = user_interface.panier.addMediaItem(
              article_index,
              id_media
            );
            $(el).addClass("active");
            $(el).parent().addClass("active");
          }
        }
      ); 
      
      // Event de suppression au panier, gestion des différents cas
      this.listeners["remove_produit"] = $(document).on(
        "remove_produit",
        function (event, el) {
          // console.log('event test in UI : remove_produit');
          var id_media = parseInt(user_interface.modal.dataset["id_media"]); // let media = user_interface.galerie.find(obj => {
          //  return obj.id === id_media
          // });

          var id_produit = parseInt($(el).parent().attr("data-id_produit"));
          var produit = user_interface.produits.find(function (obj) {
            return obj.id === id_produit;
          });
          var article_index = parseInt($(el).parent().attr("data-index"));
          var article = user_interface.panier.articles.find(function (obj) {
            return obj.index === article_index;
          }); // console.log(id_media, id_produit, article_index);

          /*
          Liste des cas possibles :
          - c'est un produit à 1 média :
            on demande confirmation de suppression de l'article dans le panier
            on enlève du panier => avec la classe Panier
            on update l'élément html
          - c'est un produit à 1+ média :
            si 2 photos où plus dedans :
              on enlève le média de array_media => avec la classe Panier
            si 1 photo (donc celle que l'utilisateur veut enlever normalement)
              on demande confirmation de suppression de l'article dans le panier
              on supprime du panier l'article => avec la classe Panier
            Puis on update ou supprime l'élément html
            */

          if (produit.limite > 1) {
            if (article.array_media.length > 1) {
              user_interface.panier.removeMediaItem(article_index, id_media);
            } else {
              user_interface.temp.el = el;
              user_interface.panier.deleteItem(article_index).then(function () {
                var el = user_interface.temp.el;
                $(el).parent().hide("200");
                setTimeout(function () {
                  $(el).parent().remove();

                  if ($("#select_albums .product").length == 0) {
                    $("#desc_selection_ablum").hide(); // si il n'y a plus d'album, on affiche plus le titre 'choix album'
                  }
                }, 200);
                return;
              });
            }
          } else {
            user_interface.panier.deleteItem(article_index);
            $(el).parent().attr("data-index", "null");
          }

          $(el).removeClass("active");
          $(el).parent().removeClass("active");
        }
      );

      this.listeners["panier_plus_moins"] = $(".panier").on(
        "click",
        ".article_qtt .plus,.article_qtt .moins",
        function (event, el) {
          var index = parseInt(
            $(this).parent().parent().parent().attr("data-index")
          );

          if ($(this).hasClass("plus")) {
            user_interface.panier.addOneQtt(index);
          } else {
            user_interface.panier.removeOneQtt(index);
          }
        }
      );

      this.listeners["panier_more_info_open"] = $(".panier").on(
        "click",
        ".panier_article .search, .panier_article .article_titre, .panier_article .article_photo",
        function (event, el) {
          $(".item_presentation").addClass("active");
          var article_index = $(this)
            .parents(".panier_article")
            .attr("data-index");
          user_interface.open_item_photo_section(parseInt(article_index));
        }
      );

      this.listeners["panier_more_info_close"] = $(".panier").on(
        "click",
        ".item_presentation_button",
        function (event, el) {
          $(".item_presentation").removeClass("active");
        }
      );

      this.listeners["panier_more_info_suppr_media"] = $(".panier").on(
        "click",
        ".item_photo .supprimer",
        function (event, el) {
          var id_media = parseInt($(this).parent().attr("data-id_media"));
          var article_index = parseInt(
            $(this).parent().parent().parent().attr("data-index")
          );
          var article = user_interface.panier.articles.find(function (obj) {
            return obj.index === article_index;
          });

          if (article.array_media.length > 1) {
            user_interface.panier.removeMediaItem(article_index, id_media); // update les photos

            user_interface.open_item_photo_section(article_index);
          } else {
            user_interface.panier.deleteItem(article_index);
            $(".item_presentation_button").click();
          }
        }
      );

      this.listeners["panier_more_info_description_open"] = $(".panier").on(
        "click",
        ".item_description_button, .item_photo_button",
        function (event, el) {
          $(".item_photo_section").toggleClass("active");
          $(".item_description_section").toggleClass("active");
          $(".item_photo_button").toggleClass("active");
          $(".item_description_button").toggleClass("active");
        }
      );

      this.listeners["panier_validate_commande"] = $(".panier").on(
        "click",
        "button.validate_commande",
        function(event, el){

          console.log("commande panier ouvre AJAX");
          var data = new FormData();

          data.append("password", PicSellVars.post.post_password);
          data.append("cartId", PicSellVars.post.ID);
          data.append("action", "post_app");
          data.append("act", "/commande/livraison");

          var base = $.ajax({
            async: false,
            type: "POST",
            url: PicSellVars.ajaxurl,
            data: data,
            beforeSend: function (xhr) {
              xhr.overrideMimeType("text/plain; charset=x-user-defined");
            },
            success: function(retour){
              
            },
            contentType: false,
            processData: false,
            cache: false,
          });
        }
      );

      this.listeners["panier_commande_panier_open"] = $(".panier").on(
        "click",
        "button.commande_panier",
        function(event, el){
          var panier = $(".panier");
          user_interface.BodyOverflowHidden(true, "panier_commande_panier");
          panier.hide("200");
          user_interface.closePanier();
          //$(".container").width("100%");
          $("#visionneuse").width("100%");
          $("#alert").width("100%");
          $("#product_modal").width("100%");
          panier.toggleClass("active");

          var html = user_interface.html_template_form_livraison();

          $(".modal_form .inner-container").html(html);
          $(".modal_form").show(200);

        }
      );

      this.listeners["panier_commande_panier_close"] = $(".modal_form").on(
        "click",
        ".button.close",
        function(event, el){
          user_interface.BodyOverflowHidden(false, "panier_commande_panier");
          $(".modal_form").hide(200);
          $(".modal_form .inner-container").empty();
        }
      );

      this.listeners["panier_commande_panier_checkout"] = $(".modal_form").on(
        "click",
        ".validate_commande",
        function(event, el){
          event.preventDefault();
          var data = new FormData();
          data.append("form", $('.form_checkout').serialize());
          data.append("action", "post_app");
          data.append("act", "/post/checkout/");
    
          var base = $.ajax({
            async: false,
            type: "POST",
            url: PicSellVars.ajaxurl,
            data: data,
            beforeSend: function (xhr) {
              xhr.overrideMimeType("text/plain; charset=x-user-defined");
            },
            contentType: false,
            processData: false,
            cache: false,
          });
          base.then(function (result, textStatus, jqXHR) {
            var responseText = jqXHR.responseText;
            window.open(
              responseText,
              "_blank"
            );
          });
        }
      );

    }

    createMasonry() {
      console.log("INITIALISATION GALERIE");
      var html = "";
      this.galerie.map((elem, index) => {
        console.log("MEDIA NUMERO " + index);
        if (elem.type == "photo" || elem.type == "image") {
          html +=
            '<div class="brick" draggable="true" data-id="' +
            elem.id +
            '"><img class="media-' +
            elem.id +
            '" src="' +
            elem.base64 +
            '" alt="' +
            elem.titre +
            '" title="' +
            elem.titre +
            " - " +
            elem.description +
            '"><div class="more"><i class="fa fa-eye" aria-hidden="true"></i></div></div>';
        } else {
          var split = elem.url.split("/");
          var count = split.length;
          var name = split[count-1];
    
          html +=
            '<div class="brick" draggable="true" data-id="' +
            elem.id +
            '"><video  width="100%"><source class="media-' +
            elem.id +
            '" src="' +
            PicSellVars.display_videos_url + "?name_vid="+name+"&dir_vid="+PicSellVars.post.ID +
            '" type="video/mp4">Sorry, your browser doesn\'t support embedded videos.</video><div class="more"><i class="fa fa-eye" aria-hidden="true"></i></div></div>';
        }
      });
      $(".masonry").append(html);
    }

    openViewer(id_media) {
      this.closeViewer();

      this.BodyOverflowHidden(true, "openViewer");

      var media = this.galerie.find(function (obj) {
        return obj.id === id_media;
      });

      var src = $(".brick")
        .find(".media-" + id_media)
        .attr("src"); //on va chercher le src en data64 car sécurisé a l'URL

      if (media.type === "video") {
        $("#visionneuse #visionneuse_vid")[0].src = src;
        $("#visionneuse #visionneuse_vid")[0].load();
        $("#visionneuse #visionneuse_vid")[0].style.display = "block";
      } else if (media.type === "photo" || media.type === "image") {
        $("#visionneuse #visionneuse_img")[0].style.display = "block";

        $("#visionneuse #visionneuse_img")[0].src = media.base64;
      } // ajout de l'id

      $("#visionneuse #ajout_panier").data("id", media.id);
      this.visionneuse.show();

    }

    BodyOverflowHidden(etat, message){

      var condition = $("body").css('overflow').toLowerCase();
      if(condition == "auto" && etat){
        $("body").css('overflow','hidden');
      }
      if(condition == "hidden" && !etat){
        $("body").css('overflow','auto');
      }
    }

    closeViewer() {

      this.BodyOverflowHidden(false, "closeViewer");

      $("#visionneuse #visionneuse_img")[0].src = "";
      $("#visionneuse #visionneuse_img")[0].style.display = "none";
      $("#visionneuse #visionneuse_vid")[0].src = "";
      $("#visionneuse #visionneuse_vid")[0].style.display = "none";
      this.visionneuse.hide();
      
    }

    openSelectProduct(id_media) {
      this.closeSelectProduct();

      this.BodyOverflowHidden(true, "openSelectProduct");
      $(".modal").css("overflow", "auto");

      var media = this.galerie.find(function (obj) {
        return obj.id === id_media;
      });

      var src = $(".brick")
        .find(".media-" + id_media)
        .attr("src"); //on va chercher le src en data64 car sécurisé a l'URL

      if (media.type === "video") {
        $("#product_modal_vid")[0].style.display = "block";
        $("#product_modal_vid")[0].src = src;
      } else if (media.type === "photo" || media.type === "image") {
        $("#product_modal_img")[0].src = media.base64;
        $("#product_modal_img")[0].style.display = "block";
      }

      $(this.modal).attr("data-id_media", id_media);
      this.modal.style.display = "block";

      var content_select = $(this.modal).find("#content_select");
      var content_description = $(this.modal).find("#content_description"); 
      // let produitsWithThisMedia  = this.panier.getAllProductforThisMedia(id_media); // les produits où la photo/video est présente
      // pour chaque PRODUITS, on génère le HTML correspondant

      var categories = [];

      for (var i = 0; i < this.produits.length; i++) {
        if (media.type == this.produits[i].type) {
          var html = this.html_template_select_product(
            this.produits[i].id,
            null,
            false
          );
          $.when(html).done(function (re) {
            $("#product_modal #select_products").append(re);
          });
          // $("#product_modal #select_products").append(html); // on rajoute la catégorie du produit dans un tableau

          if (categories.indexOf(this.produits[i].cat) == -1) {
            categories.push(this.produits[i].cat);
          }
        }
      }

      if (categories.length > 1) {
        categories.sort();
        categories = ["tout"].concat(categories);
      }
      /*
        <div class="select_cat">
          <button class="button" data-cat="all">tout</button>
        </div>
      */

      for (var i = 0; i < categories.length; i++) {
        if (categories[i] == "tout") {
          $(".select_cat").append(
            '<button class="button active" data-cat="' +
              categories[i] +
              '">' +
              categories[i] +
              "</button>"
          );
        } else if (categories.length == 1) {
          $(".select_cat").append(
            '<button class="button active" data-cat="' +
              categories[i] +
              '">' +
              categories[i] +
              "</button>"
          );
        } else {
          $(".select_cat").append(
            '<button class="button" data-cat="' +
              categories[i] +
              '">' +
              categories[i] +
              "</button>"
          );
        }
      }
      /*
        On va :
        - mettre l'index de l'article et .active sur les items dit simples dans lequel il y a le média
        - ajouter les ablums en cours ( si le média est dedans : .active et l'index sinon, non )
      */

      for (var i = 0; i < this.panier.articles.length; i++) {
        var articleInPanier = this.panier.articles[i];

        if (articleInPanier.produit.type == media.type) {
          // si le média et l'article sont compatible
          var thisMediaIsIn =
            articleInPanier.array_media.indexOf(id_media) > -1;

          if (articleInPanier.produit.limite > 1) {
            // il y a un album, donc on affiche #desc_selection_ablum
            $("#desc_selection_ablum").show();
          }

          if (articleInPanier.produit.limite > 1 && thisMediaIsIn) {
            // si c'est un ablum est que le média est dedans
            var _html = this.html_template_select_product(
              articleInPanier.id_produit,
              articleInPanier,
              true
            );
            $.when(_html).done(function (re) {
              console.log("select product");
              $("#product_modal #select_albums").append(re);
            });

            //  $("#product_modal #select_albums").append(_html);
          } else if (articleInPanier.produit.limite > 1 && !thisMediaIsIn) {
            // si c'est un album mais que le média n'est pas dedans
            var _html2 = this.html_template_select_product(
              articleInPanier.id_produit,
              articleInPanier,
              false
            );
            $.when(_html2).done(function (re) {
              console.log("select product2");
              $("#product_modal #select_albums").append(re);
            });
          } else if (articleInPanier.produit.limite == 1 && thisMediaIsIn) {
            // si c'est un produit simple et que c'est ce média qui est choisi avec
            var itemInModal = $(
              "#product_modal .product[data-id_produit='" +
                articleInPanier.id_produit +
                "']"
            );
            itemInModal.attr("data-index", articleInPanier.index);
            itemInModal.addClass("active");
            itemInModal.find(".button.select").addClass("active");
          }
        }
      }
    }

    closeSelectProduct() {

      this.BodyOverflowHidden(false, "closeSelectProduct");

      $("#product_modal_img")[0].src = "";
      $("#product_modal_img")[0].style.display = "none";
      $("#product_modal_vid")[0].src = "";
      $("#product_modal_vid")[0].style.display = "none";
      $("#desc_selection_ablum").hide(); // par défaut il n'y a pas d'albums

      $("#product_modal #select_products").empty();
      $("#product_modal #select_albums").empty();
      $("#product_modal .select_cat").empty();
      $(this.modal).data("id_media", "null");

      /**DESCRIPTION ITEM */
      $("#content_description img")[0].src = "";
      $("#content_description .description").html();
      $("#content_description").removeClass("active");
      $("#content_select").css("opacity", 1);

      this.modal.style.display = "none";

    }

    openPanier() {
      this.closePanier();
      /* 
                On boucle sur les articles
                on génère le template HTML 
                On insère le template HTML dans le panier après l'avoir vidé
                On met à jour le bouton
                On sauvegarde le panier en bdd (un envois post de l'objet article)
            */

      var montant = 0;

      for (var i = 0; i < this.panier.articles.length; i++) {
        // montant += parseFloat(articles[i].produit.prix * articles[i].qtt);
        montant +=
          parseFloat(this.panier.articles[i].produit.prix) *
          parseInt(articles[i].qtt);
        var article_html = this.html_template_panier_article(articles[i]);
        $(".panier_articles").append(article_html);
      }

      $(".panier_recap .montant").html("Montant : " + montant + " €");
    }

    closePanier() {
      $(".panier_articles").empty();
      $(".button_panier span").html(this.panier.articles.length);
    }

    open_item_photo_section(article_index) {
      this.close_item_photo_section();
      var article = this.panier.getItem(article_index);
      $(".item_presentation").attr("data-index", article_index);

      if (article.produit.type == "photo" || article.produit.type == "image") {
        $(".item_presentation_photo").css(
          "background-image",
          "url(" + article.produit.base64 + ")"
        );
        $(".item_photo_button").html("Photo(s)");
      } else {
        $(".item_presentation_photo").css(
          "background-image",
          "url(" + article.produit.base64 + ")"
        );
        $(".item_photo_button").html("Vidéo");
      }

      $(".item_presentation_titre").html(article.produit.titre);
      $(".item_description_section").html(article.produit.description);

      for (var i = 0; i < article.array_media.length; i++) {
        var id_media = article.array_media[i];
        console.log(article.produit.type);
        var html = this.html_template_panier_item_photo_section(
          id_media,
          article.produit.type
        );
        $(".item_photo_section").append(html);
      }
    }

    close_item_photo_section() {
      $(".item_photo_section").empty();
    }

    html_template_select_product(produit_id, article, isIn) {
      var produit = this.produits.find(function (obj) {
        return obj.id === produit_id;
      });
      var template = $("#template_select_product");

      if (isIn != false) {
        template = $("#template_select_product_active");
      }

      var id_media = this.modal.dataset["id_media"];

      var template_html = template.html(); // {ID_PRODUIT}

      // {ID_MEDIA}
      // {TITRE}
      // {PRIX}
      // template.children[0].innerHTML.replace
      if (article != null) {
        template_html = template_html.replace("{INDEX}", article.index);
      } else {
        template_html = template_html.replace("{INDEX}", "null");
      }

      template_html = template_html.replace("{ID_PRODUIT}", produit_id);
      template_html = template_html.replace("{ID_MEDIA}", id_media);

      template_html = template_html.replace("{IMAGE_SRC}", produit.base64);
      template_html = template_html.replace("{TITRE}", produit.titre);
      template_html = template_html.replace("{PRIX}", produit.prix);
      template_html = template_html.replace("{CAT}", produit.cat);
      // console.log(base);
      return template_html;

      // template.querySelector(".product").dataset.produit_id = produit_id;
      // template.querySelector(".product_price").innerText = produit.prix + " €";
      // debugger;
      // template = document.importNode(templa, true);
      // TO DO : image de fond bien sur :)
      // template.querySelector(".product_titre").innerText = produit.titre;
      // {"id": 1, "titre":"Photo Lorem impsum 1", "cat" : "A", "prix":"50", "type":"photo", "limite":"1"},
    }

    html_template_panier_article(article) {
      var template = $("#template_panier_article");
      var template_html = template.html(); // {TITRE}
      // {PRIX}
      // {QTT}
      // {X_SUR_MAX}

      template_html = template_html.replace("{INDEX}", article.index);
      template_html = template_html.replace("{TITRE}", article.produit.titre);
      template_html = template_html.replace("{IMAGE_SRC}", article.produit.base64);
      template_html = template_html.replace("{PRIX}", article.produit.prix);
      template_html = template_html.replace("{QTT}", parseInt(article.qtt));

      if (article.produit.limite == -1) {
        template_html = template_html.replace("{DISPLAY_X_SUR_MAX}", "none");
      } else {
        template_html = template_html.replace("{DISPLAY_X_SUR_MAX}", "block");
        template_html = template_html.replace(
          "{X_SUR_MAX}",
          article.array_media.length + "/" + article.produit.limite
        );
      }

      return template_html;
    }

    html_template_panier_item_photo_section(id_media, type) {
      var media = user_interface.galerie.find(function (obj) {
        return obj.id === id_media;
      });
      var template = $("#template_panier_item_photo_section");
      var template_html = template.html();
      template_html = template_html.replace("{ID_MEDIA}", media.id);

      if (type == "photo" || type == "image") {
        template_html = template_html.replace("{IMAGE_SRC}", media.base64);
        template_html = template_html.replace("{IMAGE_DISPLAY}","inline-block");
        template_html = template_html.replace("{VIDEO_DISPLAY}", "none");
        template_html = template_html.replace("{VIDEO_SRC}", "" );
      } else if (type == "video") {
        console.log("video ?");
        template_html = template_html.replace("{VIDEO_SRC}", PicSellVars.url_include+"pic-sell-handlerStream.php?url=" + PicSellVars.dir_include_img +media.url );
        template_html = template_html.replace("{VIDEO_DISPLAY}","inline-block");
        template_html = template_html.replace("{IMAGE_DISPLAY}", "none");
        template_html = template_html.replace("{IMAGE_SRC}", "");
      }

      return template_html;
    }
  
    html_template_form_livraison(){

      var template = $("#template_form_livraison");
      var template_html = template.html();

      return template_html;


    }
    
  }

  class Panier {
    constructor(articles) {
      return (async () => {

        this.articles = articles;

        this.articles.forEach(function (article) {
          article.id_produit = parseInt(article.id_produit);
          article.index = parseInt(article.index);
          article.produit.id = parseInt(article.produit.id);
          article.qtt = parseInt(article.qtt);
          article.produit.base64 = "";
          article.produit.limite = parseInt(article.produit.limite);
          article.produit.prix = parseFloat(article.produit.prix.toString().replace(",", "."));
        });
        this.enrichissementArticles();
       // this.updatePanier();
        return this;
      })();
    }
    
    enrichissementArticles() {
      var _this = this;

      for (var i = 0; i < this.articles.length; i++) {
        var produit = produits.find(function (obj) {
          // ici produit c'est la variable globale, qui ne change pas
          return obj.id === _this.articles[i].id_produit;
        }); // on récupère le produit correspondant
        this.articles[i].produit = produit; // et on enrichie l'item avec les données du produit
      }
    }

    updatePanier() {
   //   this.save_panier();
      /**on retire base64 img pour eviter la surcharge serveur en BDD */
      let arts = JSON.parse(JSON.stringify(this.articles));
      for (var i = 0; i < arts.length; i++) {
        arts[i].produit.base64 = null;
      }

      var data = new FormData();
      data.append("cartId", $("span#params").data("id"));
      data.append("password", $("span#params").data("password"));
      data.append("cart", JSON.stringify(arts));
      //console.log("clone", arts);
      //console.log("origine",this.articles);
      data.append("action", "post_app");
      data.append("act", "/post/update/");
      $.ajax({
        type: "POST",
        url: PicSellVars.ajaxurl,
        data: data,
        contentType: false,
        processData: false,
        success: function (retour) {
        },
        //dataType: "json",
      });
      //  $.post('/post/update/', {cartId: $('span#params').data('id'), password: $('span#params').data('password'), cart: this.articles});
      console.log("sauvegarde en base de donnée");
    }

    events() {
      $(document).on("addItem", function (event) {
        // accès aux variables globales du documents
        $(document).trigger("updatePanier", panier.articles);
      });
      $(document).on("updateItem", function (event, id) {
        // accès aux variables globales du documents
        $(document).trigger("updatePanier", panier.articles);
      });
      $(document).on("deleteItem", function (event, id) {
        // accès aux variables globales du documents
        console.log("event deleteItem in panier", id);
        $(document).trigger("updatePanier", panier.articles);
      });
      $(document).on("updatePanier", function (event, articles) {
        // accès aux variables globales du documents
        panier.enrichissementArticles();
        panier.updatePanier();
        //user_interface.closePanier();
        user_interface.openPanier();

        if ($("#product_modal").is(":visible")) {
          var id_media = $("#product_modal").attr("data-id_media");
          user_interface.openSelectProduct(parseInt(id_media));
        }
      });
    }

    getItem(index) {
      return this.articles.find(function (produit) {
        return produit.index === index;
      });
    }

    addMediaItem(index, id_media) {
      var article = this.getItem(parseInt(index));

      if (article.produit.limite > article.array_media.length) {
        article.array_media.push(parseInt(id_media));
      }

      $(document).trigger("updateItem", [index]);
    }

    addItem(id_produit, id_media) {
      id_produit = parseInt(id_produit);
      id_media = parseInt(id_media);
      var media = user_interface.galerie.find(function (obj) {
        return obj.id === id_media;
      });
      var produit = user_interface.produits.find(function (obj) {
        return obj.id === id_produit;
      });
      var timestamp = new Date().getUTCMilliseconds();
      this.articles.push({
        index: timestamp,
        id_produit: id_produit,
        array_media: [id_media],
        produit: produit,
        qtt: 1,
      });
      //$(document).trigger("addItem");
      return timestamp;
    }

    addOneQtt(index) {
      var article = this.getItem(parseInt(index));
      article.qtt += 1;
      $(document).trigger("updateItem", [index]);
    }

    removeOneQtt(index) {
      var article = this.getItem(parseInt(index));

      if (article.qtt == 1) {
        this.deleteItem(index);
      } else {
        article.qtt -= 1;
      }

      $(document).trigger("updateItem", [index]);
    }

    updateItem(index, id_produit, array_media, qtt) {
      var article = this.getItem(parseInt(index));

      if (id_produit != false) {
        article.id_produit = id_produit;
      }

      if (array_media != false) {
        article.array_media = array_media;
      }

      if (qtt != false) {
        article.qtt = qtt;
      }

      $(document).trigger("updateItem", [index]);
    }

    removeMediaItem(index, id_media) {
      var article = this.getItem(parseInt(index));
      var index_media = article.array_media.indexOf(id_media);
      article.array_media.splice(index_media, 1);
      $(document).trigger("updateItem", [index]);
    }

    deleteItem(index) {
      user_interface.temp.index = index;
      return user_interface
        .prompt("Cela va supprimer l'article, êtes-vous sûr ?", "boolean")
        .then(function (bool) {
          if (bool == true) {
            var _index = user_interface.temp.index;
            var article = panier.getItem(parseInt(_index));
            var index_article = panier.articles.indexOf(article);
            panier.articles.splice(index_article, 1);
            $(document).trigger("deleteItem", [_index]);
            return true;
          } else {
            return false;
          }
        });
    }

    getAllProductforThisMedia(id) {
      var res = this.articles.filter(function (obj) {
        return obj.array_media.indexOf(id) > -1;
      });
      return res;
    }

    getAllProductAlbum() {
      // return tout les produits avec Albums en cours
    }

    save_panier() {
      // save en BDD
    }
  }

  const produits = $("span#params").data("products");
  const galerie = $("span#params").data("galerie");
  const articles = $("span#params").data("cart");

  var user_interface, panier;

  $(document).ready(async function ($) {

    panier = await new Panier(articles);

    $.when(panier).done(async function () {
      console.log("PANIER FINISH INIT");
      user_interface = await new UI(galerie, produits, panier);

      $.when(user_interface).done(function () {
        //Quand user_interface est chargé, on charge les évènements
        user_interface.events();
        panier.events();
      //  $(document).trigger("updatePanier", this.articles);

        //Gestion du parallax
        var moving__background = $(".header-background");
        $(window).scroll(function () {
          var offsetTop = $(".header-espaceprive")[0].offsetTop;
          var offset = $(window).scrollTop();

          if (offset >= offsetTop) {
            moving__background.css("margin-top", (offset - offsetTop) / 3); // Parallax scrolling

            moving__background.css(
              "opacity",
              1 - (offset - offsetTop) / moving__background.height()
            ); // Fading out
          } else {
            moving__background.css("margin-top", 0); // Parallax scrolling

            moving__background.css("opacity", 1); // Fading out
          }
        });
        /* Gestion du drag and drop : pour l'instant à l'extérieur des classes */

        var id_media = "null"; // Allow multiple draggable items

        var dragSources = jQuery('[draggable="true"]');
        dragSources.each(function (index, el) {
          this.addEventListener("dragstart", dragStart);
          this.addEventListener("dragend", dragEnd);
        });

        function dragStart(e) {
          dragSources.each(function (index, el) {
            this.style.opacity = 0.25;
          });
          jQuery(".header-espaceprive").css({ opacity: "0.25" });
          jQuery(".drop_zone").addClass("active");
          var panier = document.querySelectorAll(".panier");

          if (!jQuery(panier).hasClass("active")) {
            jQuery(".button_panier").click();
          } // On boucle sur les articles dans le panier pour mettre en surbrillance celles qui peuvent recevoir le média

          this.style.opacity = 1;
          this.classList.add("dragging");
          e.dataTransfer.setData("text/plain", e.target.id);
          id_media = parseInt($(this).attr("data-id")); // Allow multiple dropped targets

          var dropTargets = $('[data-role="drag-drop-container"]');
          dropTargets.each(function (index, el) {
            this.addEventListener("drop", dropped);
            this.addEventListener("dragenter", cancelDefault);
            this.addEventListener("dragover", dragOver);
            this.addEventListener("dragleave", dragLeave);
          });
        }

        function dragEnd(e) {
          dragSources.each(function (index, el) {
            this.style.opacity = 1;
          });
          $(".drop_zone").removeClass("active");
          jQuery(".header-espaceprive").css({ opacity: "1" });
          this.classList.remove("dragging");
        }

        function dropped(e) {
          cancelDefault(e);

          if (
            $(this).hasClass("panier_article") ||
            $(this).hasClass("item_presentation")
          ) {
            this.classList.remove("drag_hover");
            var article_index = parseInt($(this).attr("data-index"));
            var article = panier.getItem(article_index);
            var media = user_interface.galerie.find(function (obj) {
              return obj.id === id_media;
            });
            var produit = user_interface.produits.find(function (obj) {
              return obj.id === article.id_produit;
            });

            if (
              article.array_media.length < article.produit.limite &&
              media.type == produit.type
            ) {
              panier.addMediaItem(article_index, id_media);
              user_interface.open_item_photo_section(article_index);
            } else {
              // Ce produit
              if (media.type !== produit.type) {
                user_interface.alert(
                  "le média et le produit ne sont pas compatibles."
                );
              } else if (article.array_media.length >= article.produit.limite) {
                user_interface.alert(
                  "cet article ne peut contenir plus de " + produit.type + "."
                );
              }
            }
          } else if (
            $(this).hasClass("panier_articles") ||
            $(this).hasClass("drop_zone")
          ) {
            user_interface.openSelectProduct(id_media);
          }
        }

        function dragOver(e) {
          cancelDefault(e);
          this.classList.add("drag_hover");
        }

        function dragLeave(e) {
          this.classList.remove("drag_hover");
        }

        function cancelDefault(e) {
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
      });
    });
  });
});
