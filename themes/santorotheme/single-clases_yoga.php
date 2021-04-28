<?php get_header(); ?>

<?php if (have_posts()):  while(have_posts()): the_post(); ?>

<?php 
  
  /** 
  * UX
  *
  * Si es video gratis > Login/Register > Vuelva al video
  *
  * Si es video premium > Login/Register > (Payment) > Vuelva al video
  *
  * DEV
  * 
  * Si es video gratis
  *    Si login > Ver video *DONE*
  *    Si no login
  *         Si login > volver a video (functions.php)
  *         Si register > volver a video (functions.php)
  *
  * Si es video premium
  *     Si login 
  *         Si es como premium ok > Ver video 
  *         Si es como premium ko > Ir a la cuenta 
  *         Si es como gratis > ir a payment > volver a video (functions.php)
  *     Si no login
  *         Si login
  *             Si es como premium ok > Ver video (functions.php) 
  *             Si es como premium ko > Ir a la cuenta (functions.php) 
  *             Si es como gratis > ir a payment (functions.php) > volver a video (functions.php)
  *         Si register > ir a payment (functions.php) > volver a video (functions.php)
  * 
  **/

  $_SESSION['landing_before_login'] = home_url( $wp->request ); 
  $_SESSION['restricted'] =  "si";
  $_SESSION['last_class_seen'] = home_url( $wp->request ); 
  /*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/ 
  $_SESSION['last_class_plan'] = get_field('plan');


  /* Propieades para evento de amplitude */      
  $url_actual = home_url( add_query_arg( array(), $wp->request ) );
  $current_user = wp_get_current_user();
  $current_user_id = $current_user->ID;
  $current_user_es_regalo =  esc_attr(get_the_author_meta('user_es_regalo', $current_user_id ));

 
  if (wcs_user_has_subscription($user_id, '', 'active')) { 
    $user_plan = "active";
  } else if (wcs_user_has_subscription($user_id, '', 'pending-cancel')) { 
    $user_plan = "pending-cancel";
  } else if (wcs_user_has_subscription($user_id, '', 'cancelled')) { 
    $user_plan = "cancelled";
  } else if (wcs_user_has_subscription($user_id, '', 'on-hold')) {  
    $user_plan = "on-hold";
  } else if (wcs_user_has_subscription($user_id, '', 'expired')) {
    $user_plan = "expired";
  } else {
    $user_plan = "free";
  }


  if ( (get_field('plan') == "Gratis") || ($current_user_es_regalo == "si")) {
    if (is_user_logged_in()) { 
          $videoBlocked = false;
          $textButtonCTA = "";
          $idZoom="";
          $linkButtonCTA=""; 
          $textReproductorCTA = "Disfruta de tu práctica. ¡Namaste!" ;
          $textReproductorCTAFooter = "Disfruta de tu práctica. ¡Namaste!" ;
      } else {
          $videoBlocked = true;
          $textButtonCTA = "Login / Darme de alta";
          $idZoom="";     
          $linkButtonCTA=home_url()."/entrar/";
          $textReproductorCTA = "Para continuar la clase, haz login o date de alta."; 
          $textReproductorCTAFooter = "Para continuar la clase, <a href='$linkButtonCTA' class='linkColor'>haz login o date de alta</a>. ¡Namaste!"; 
      }
  }

  else {

    if (wcs_user_has_subscription( '', '', '')) {
            if (wcs_user_has_subscription( '', '', 'active')) { 
              $videoBlocked = false;
              $textButtonCTA = "";
              $idZoom="";
              $linkButtonCTA=""; 
              $textReproductorCTA = "Disfruta de tu práctica. ¡Namaste!" ;
              $textReproductorCTAFooter = "Disfruta de tu práctica. ¡Namaste!" ;
            }
            else if (wcs_user_has_subscription( '', '', 'pending-cancel')) { 
              $videoBlocked = false;
              $textButtonCTA = "";
              $idZoom="";   
              $linkButtonCTA=""; 
              $textReproductorCTA = "Disfruta de tu práctica." ;
              $textReproductorCTAFooter = "Disfruta de tu práctica. 'Namaste!" ;
          } else if (wcs_user_has_subscription( '', '', 'cancelled')) { 
              $videoBlocked = true;
              $textButtonCTA = "Mi cuenta"; 
              $idZoom="";     
              $linkButtonCTA=home_url()."/cuenta/";
              $textReproductorCTA = "Oops! Tu cuenta está cancelada."; 
              $textReproductorCTAFooter = "Oops! Tu cuenta está cancelada. Ve a tu cuenta y reactívala."; 
            } else if (wcs_user_has_subscription( '', '', 'on-hold')) {   
              $videoBlocked = true;
              $textButtonCTA = "Mi cuenta";
              $idZoom="";     
              $linkButtonCTA=home_url()."/cuenta/";    
              $textReproductorCTA = "Oops! Tu pago a fallado.";   
              $textReproductorCTAFooter = "Oops! Tu pago a fallado. Ve a tu cuenta y revísalo.";   
            } else if (wcs_user_has_subscription( '', '', 'expired')) {
              $videoBlocked = true;
              $textButtonCTA = "Mi cuenta";
              $idZoom="";     
              $linkButtonCTA=home_url()."/cuenta/";
              $textReproductorCTA = "Oops! Tu cuenta ha expirado."; 
              $textReproductorCTAFooter = "Oops! Tu cuenta ha expirado. Ve a tu cuenta y reactívala."; 
            } else { 
              $videoBlocked = true;
              $textButtonCTA = "Contacta con nosotros";
              $idZoom="";     
              $linkButtonCTA="mailto:soporte@theclassyoga.com"; 
              $textReproductorCTA = "Oops! Ha ocurrido un error. Ponte en contacto con nosostros"; 
              $textReproductorCTAFooter = "Oops! Ha ocurrido un error. Ponte en contacto con nosostros"; 
            }
      } else { 
          if (is_user_logged_in()) { 
              if (get_field('plan') == "Premium") {
                    $videoBlocked = true;
                    $textButtonCTA = "Completar alta";
                    $idZoom="";     
                    $linkButtonCTA=home_url()."/completar-alta/";  
                    $textReproductorCTA = "Completa tu alta para hacer una clase premium"; 
                    $textReproductorCTAFooter = "Las clases premium son de pago...<a href='$linkButtonCTA' class='linkColor'>Completar alta</a>."; 
              } else {
                  $videoBlocked = false;
                  $textButtonCTA = "";
                  $idZoom="";
                  $linkButtonCTA=""; 
                  $textReproductorCTA = "Disfruta de tu práctica." ;
                  $textReproductorCTAFooter = "Disfruta de tu práctica. ¡Namaste!" ;
              }
              
          } else { 
              $videoBlocked = true;
              $textButtonCTA = "Login / Darme de alta";
              $idZoom="";     
              $linkButtonCTA=home_url()."/entrar/";
              $textReproductorCTA = "Para continuar la clase, haz login o date de alta."; 
              $textReproductorCTAFooter = "Para continuar la clase, <a href='$linkButtonCTA' class='linkColor'>haz login o date de alta</a>. ¡Namaste!"; 
        
            }
      } 
    
  }
    ?>   

    

      <main>
      <div class="container-l">

            <div id="js-wrap-16-9" class="wrap-16-9">

                        <!-- Video de la clase: visible hasta que segun el estado del usuario se pone encima  el div con el video CTA -->
                        <div class="video-clase">
                              <script src="https://cdn.jwplayer.com/players/<?php echo get_field('id_video');?>.js"></script>
                              <script type="application/javascript">
                                    var div_player = "botr_" + "<?php echo get_field('id_video');?>" + "_div";
                                    div_player = div_player.replace (/-/g, "_"); //esto lo hago porque en jwplayer es con guion medio pero el div luego luego lo pone con guion bajo
                                    var reproductor = jwplayer(div_player);
                              </script>
                        </div>


                         <!-- Video CTA: oculto al inicio -->
                        <div id="js-reproductor-CTA" class="reproductor-CTA-clase">
                                  <?php if( get_field('imagen_inicio') ) { ?>
                                    <img class="reproductor-CTA-img-clase"  src="<?php the_field('imagen_inicio'); ?>" alt="<?php the_title(); ?>"/>
                                <?php } else {  ?>
                                  <img class="reproductor-CTA-img-clase"  src="<?php bloginfo('template_url'); ?>/img/clase-yoga.jpg" alt="<?php the_title(); ?>" />
                                <?php } ?>


                                  <div class="reproductor-CTA-description-clase"> 
                                      <p id="js-text-reproductor-CTA" class="reproductor-CTA-description-text-clase"><?php echo $textReproductorCTA ?></p>
                                      <button
                                        id="js-button-CTA"
                                        class="btn btn-primary"
                                        onclick="window.location.href='<?php echo $linkButtonCTA ?>'"
                                      >
                                      <?php echo $textButtonCTA ?>
                                      </button>
                                  </div>
                    </div>
              </div>
      </div>

      <div class="container-l">
        <h1 class="video-title"><?php the_title(); ?></h1>     
      
            <div class="post-autor-wrapper">
                <?php $profesor = get_field('profesor'); 
                $profesor_image = $profesor['user_avatar'];
                preg_match("/src=['\"](.*?)['\"]/i", $profesor_image, $matches);
                ?>
            <img class="video-autor-img" src="<?php echo $matches[1] ?>" alt="Profesor de yoga <?php echo $profesor['display_name']; ?>"/>
            <p class="video-autor-name"> <?php echo $profesor['display_name']; ?></p>
         </div>  


     
        <div class="video-properties">
        
              <h4>TIPO DE YOGA</h4>
              <p><?php the_field('tipo'); ?></p>
              <h4>DURACIÓN</h4>
              <p><?php the_field('duracion_exacta'); ?> min</p>
              <h4>NIVEL</h4>
              <p><?php the_field('nivel'); ?></p>
              <h4>INTENSIDAD</h4>
              <p><?php the_field('intensidad'); ?></p>
              <h4>FOCO</h4>
              <p><?php the_field('foco'); ?></p>
              <h4>SOPORTES</h4>
              <p><?php the_field('soportes'); ?></p>
              <h4>POPULARIDAD</h4>
              <p><?php the_field('visualizaciones'); ?> yoguis lo han visto</p>
              <h4>PROGRAMA</h4>
              <p><a href="<?php the_field('link_programa'); ?>"><?php the_field('nombre_programa'); ?></a></p>
              <h4>PLAN</h4>
              <?php if (get_field('plan') == "Gratis") { ?>
                <p><span class="clase-plan alert-success">Gratis</span> </p>
              <?php } else { ?>
                <p><span class="clase-plan alert-primary">Premium</span> </p>
              <?php } ?>
               
          </div>

          <?php  if ( !empty( get_the_content() ) ) {   ?>
          <div class="container-l video-properties-description">
                <h4>DESCRIPCIÓN</h4>
                <div><?php the_content(); ?></div>
          </div>
          <?php }  ?>

        </div>


      </div>

    </main>

    <?php endwhile; endif;?>

  <!-- Función para bloquear el vídeo y sacar el CTA y para enviar eventos amplitud-->
  <script type="application/javascript">

        reproductor.on("ready", () => {
                    let el = document.querySelector(".wrap-16-9")
                    el.style.removeProperty('padding-bottom')
                    })


            var eventProperties = {
              'CLASE_PLAN': '<?php the_field('plan'); ?>',
              'CLASE_TIPO': '<?php the_field('tipo'); ?>',
              'CLASE_DURACION': <?php the_field('duracion_exacta'); ?>,
              'CLASE_NIVEL': '<?php the_field('nivel'); ?>',
              'CLASE_INTENSIDAD': '<?php the_field('intensidad'); ?>',
              'CLASE_FOCO': '<?php the_field('foco'); ?>',
              'CLASE_SOPORTES': '<?php the_field('soportes'); ?>',
              'CLASE_RETO': '<?php the_field('nombre_programa'); ?>',
              'CLASE_PROFESOR': '<?php echo $profesor['display_name']; ?>',
              'CLASE_VISUALIZACIONES': '<?php the_field('visualizaciones'); ?>',
              'URL': '<?php echo $url_actual; ?>',
              'USER_PLAN': '<?php echo $user_plan; ?>',
              'USER_EMAIL': '<?php echo $current_user_email; ?>',
              'USER_ES_REGALO': '<?php echo $current_user_es_regalo; ?>'
             };
        
        
            var played_0 = false;
            var played_20 = false;
            var played_50 = false;
            var played_80 = false;
            var played_100 = false;


            var visualizaciones = <?php the_field('visualizaciones'); ?>;

            var suma_visualizaciones_hecha = false;

            reproductor.on('time', function(x) {
      

                      if (Math.round (x.position) == 0){
                                amplitude.getInstance().logEvent('CLASE PLAYED 0%', eventProperties);
                                played_0 = true;
                                console.log('visualizaciones B', visualizaciones);

                                if (!suma_visualizaciones_hecha) { /*porque a veces suma 2 veces ya que el reproductor va al microsegundos */
                                  if (visualizaciones != 0) { /*para no sumar en los descansos */
                                    visualizaciones = visualizaciones + 1;
                                    var postId = '<?php echo $post->ID; ?>'; 

                                    sumaVisualizaciones(postId, visualizaciones);
                                    suma_visualizaciones_hecha= true;
                                  }
                                }
                      }

                      var duracion_clase = reproductor.getDuration();

                      if ((Math.round (x.position) == Math.round((20/100)*duracion_clase)) && played_0 == true){
                                amplitude.getInstance().logEvent('CLASE PLAYED 20%', eventProperties);
                                played_20 = true;
                      }

                      
                      if ((Math.round (x.position) == Math.round((50/100)*duracion_clase)) && played_0 == true && played_20 == true){
                                amplitude.getInstance().logEvent('CLASE PLAYED 50%', eventProperties);
                                played_50 = true;
                      }

                      if ((Math.round (x.position) == Math.round((80/100)*duracion_clase)) && played_0 == true && played_20 == true && played_50 == true){
                                amplitude.getInstance().logEvent('CLASE PLAYED 80%', eventProperties);
                                played_80 = true;
                      }

                    

                      // Para bloquear el vídeo a los 20 segundos
                      if (x.position >= 20) {
                        <?php if ($videoBlocked){ ?>
                              reproductor.pause();
        
                              amplitude.getInstance().logEvent('CLASE PLAYED BLOCKED', eventProperties);


                              var reproductorCTA = document.getElementById("js-reproductor-CTA"); 
                              reproductorCTA.classList.add("reproductor-CTA-clase-visible");

                              var textReproductorCTA = document.getElementById("js-text-reproductor-CTA");
                              textReproductorCTA.innerHTML = "<?php echo $textReproductorCTA ?>";

                              var buttonCTA = document.getElementById("js-button-CTA");
                              buttonCTA.innerHTML = "<?php echo $textButtonCTA ?>";
                              buttonCTA.setAttribute('onclick',"window.location.href='<?php echo $linkButtonCTA?>';" );
                              
                              var video = document.getElementById(div_player);
                              video.classList.add("reproductor-hidden");
                        <?php } ?>
                      }
            });


            reproductor.on('complete', function(){
                  if (played_0 == true && played_20 == true && played_50  == true&& played_80 == true) {
                      amplitude.getInstance().logEvent('CLASE PLAYED 100%', eventProperties);
                      var played_100 = true;
                  }
            });
          
            reproductor.on('ready', function(){
                    var wrapper = document.getElementById("js-wrap-16-9");
                    wrapper.classList.add('padding-bottom-off');
                    })

   </script>

<?php get_footer(); ?>