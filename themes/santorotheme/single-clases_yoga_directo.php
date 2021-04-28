<?php get_header(); 
  
   /** 
  * 
  * Condicional para ver si está suscrito o no y bloquear el vídeo personaliando el mensaje
  * 
  * Si es suscriptor:
  * 1) Si está activo -> Ver video 
  * 2) Si está pending cancellation  -> Ver video
  * 3) Si está cancelled -> Ir a my account
  * 4) Si está onhold -> Ir a my account 
  * 5) Si está expired -> Ir a my account 
  * 6) Si es otra cosa (error controlado)> -> Ir a formulario contacto
  * 
  * Si no es suscriptor:
  * 1) Si está login 
  *           -> SI ES VÍDEO DE PAGO -> Ir a checkout > Ir a mi cuenta
  *           -> SI ES VÍDEO GRATIS -> IR A MI CUENTA
  * 2) Si no está login -> Ir a login/register (esto se controla ya en functions.php en santoro_register_redirect)
  *   2.1) Si va a login 
  *         -> Si estaba suscrito -> Ir a mi cuenta
  *         -> Si no estaba suscrito 
  *               -> SI ES VÍDEO DE PAGO -> Ir a checkout  -> Ir a mi cuenta
  *               -> SI ES VÍDEO GRATIS -> Ver vídeo
  *   2.1) Si va a register 
  *           -> SI ES VÍDEO DE PAGO -> Ir a checktout -> Ir a mi cuenta
  *           -> SI ES VÍDEO GRATIS -> IR A MI CUENTA
  * 
  **/

  $_SESSION['landing_before_login'] = home_url( $wp->request ); 
  $_SESSION['restricted'] =  "si";
  /*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
  $_SESSION['last_class_seen_directo'] = home_url( $wp->request ); 
  $_SESSION['last_class_plan'] = get_field('plan'); 

  $current_user = wp_get_current_user();
  $current_user_id = $current_user->ID;
  $current_user_es_regalo =  esc_attr(get_the_author_meta('user_es_regalo', $current_user_id ));

  
  if ( (get_field('plan') == "Gratis") || ($current_user_es_regalo == "si")) {
    if (is_user_logged_in()) { 
            $videoBlocked = false;
            $textButtonCTA = "";
            $idZoom=get_field('id_zoom');       
            $linkButtonCTA="https://zoom.us/j/$idZoom"; 
            $textReproductorCTA = "Disfruta de tu práctica. ¡Namaste!" ;
            $textReproductorCTAFooter = "Disfruta de tu práctica. ¡Namaste!" ;
        } else {
            $videoBlocked = true;
            $textButtonCTA = "Login / Darme de alta";
            $idZoom="";     
            $linkButtonCTA=home_url()."/entrar/";
            $textReproductorCTA = "Para continuar la clase, haz login o date de alta. ¡Namaste!"; 
            $textReproductorCTAFooter = "Para continuar la clase, <a href='$linkButtonCTA' class='linkColor'>haz login o date de alta</a>. ¡Namaste!"; 
        }
  } else {

      if (wcs_user_has_subscription( '', '', '')) {
              if (wcs_user_has_subscription( '', '', 'active')) { 
                $videoBlocked = false;
                $textButtonCTA = "Entrar";
                $idZoom=get_field('id_zoom');       
                $linkButtonCTA="https://zoom.us/j/$idZoom"; 
                $textReproductorCTA = "La clase es por zoom. Si al dar a play no se abre automáticamente <a target='_blank' href='$linkButtonCTA'>entra a la clase desde aquí</a>." ;  
              }
              else if (wcs_user_has_subscription( '', '', 'pending-cancel')) { 
                $videoBlocked = false;
                $textButtonCTA = "Entrar";
                $idZoom=get_field('id_zoom');   
                $linkButtonCTA="https://zoom.us/j/$idZoom";   
                $textReproductorCTA = "Oops! ¿Vas a dejarnos? ¿Qué poodemos hacer? Escríbenos a <a  href='mailto:soporte@theclassyoga.com'>soporte@theclassyoga.com</a>." ;  
            } else if (wcs_user_has_subscription( '', '', 'cancelled')) { 
                $videoBlocked = true;
                $textButtonCTA = "Mi cuenta"; 
                $idZoom="";     
                $linkButtonCTA=home_url()."/cuenta/";
                $textReproductorCTA = "Oops! Tu cuenta está cancelada. Para entrar en la clase <a href='$linkButtonCTA'>actívala por favor</a>. ¡Namaste!"; 
              } else if (wcs_user_has_subscription( '', '', 'on-hold')) {   
                $videoBlocked = true;
                $textButtonCTA = "Mi cuenta";
                $idZoom="";     
                $linkButtonCTA=home_url()."/cuenta/";    
                $textReproductorCTA = "Oops! Tu pago ha fallado. Para entrar en la clase <a href='$linkButtonCTA'>revísalo por favor</a>. ¡Namaste!";      
              } else if (wcs_user_has_subscription( '', '', 'expired')) {
                $videoBlocked = true;
                $textButtonCTA = "Mi cuenta";
                $idZoom="";     
                $linkButtonCTA=home_url()."/cuenta/";
                $textReproductorCTA = "Oops! Tu cuenta ha expirado. Para entrar en la clase <a href='$linkButtonCTA'>revísala por favor</a>. ¡Namaste!"; 
              } else { 
                  $videoBlocked = true;
                  $textButtonCTA = "Contacta con nosotros";
                  $textButtonCTA = "Login / Darme de alta";
                  $idZoom="";     
                  $linkButtonCTA="mailto:soporte@theclassyoga.com"; 
                  $textReproductorCTA = "Oops! Ha ocurrido un error. Ponte en <a href='$linkButtonCTA'>contacto con nosostros</a>. ¡Namaste!"; 

              }
        } else { 
            if (is_user_logged_in()) { 
                  if (get_field('plan') == "Premium") {
                        $videoBlocked = true;
                        $textButtonCTA = "Completar alta";
                        $idZoom="";     
                        $linkButtonCTA=home_url()."/completar-alta/";  
                        $textReproductorCTA = "Completa tu alta para hacer una clase premium"; 
                  } else {
                      $videoBlocked = false;
                      $textButtonCTA = "Entrar";
                      $idZoom=get_field('id_zoom');       
                      $linkButtonCTA="https://zoom.us/j/$idZoom"; 
                      $textReproductorCTA = "La clase es por zoom. Si al dar a play no se abre automáticamente <a target='_blank' href='$linkButtonCTA'>entra a la clase desde aquí</a>." ;  
          
                  }
          
            } else { 
                $videoBlocked = true;
                $textButtonCTA = "Login / Darme de alta";
                $idZoom="";     
                $linkButtonCTA=home_url()."/entrar/";
                $textReproductorCTA = "Para entrar en la clase <a href='$linkButtonCTA'>haz login o date de alta por favor</a>. ¡Namaste!"; 

              }
        } 
    
  }?>   



<?php if (have_posts()):  while(have_posts()): the_post(); ?>
      
    <main>
      <div class="container-l">
        


                    <div class="reproductor-CTA-clase-directo">
                        <!-- Video de la clase -->
                        <div id="js-cta-clase-embed" class="cta-clase-embed alert alert-warning">La clase es por zoom. Si al dar a play no se abre automáticamente <a target="_blank" href="<?php echo $linkButtonCTA ?>" class="alert-link alert-link-warning">entra a la clase desde aquí</a></div>
                        <?php if( get_field('imagen_clase') ) { ?>
                            <img class="reproductor-CTA-img-clase-directo"  src="<?php the_field('imagen_clase'); ?>" />
                        <?php } else {  ?>
                          <img class="reproductor-CTA-img-clase-directo"  src="<?php bloginfo('template_url'); ?>/img/clase-yoga-directo.jpg" />

                        <?php } ?>

                    

                        <div class="reproductor-CTA-description-clase-directo"> 
                              <?php if ($videoBlocked==true) { ?> 
                                  <a href="<?php echo $linkButtonCTA ?>"><i class="fas fa-play mi-play"></i></a>
                              <?php } else { ?>
                                <a id="js-play-clase-embed" target="_blank" href="<?php echo $linkButtonCTA ?>"><i class="fas fa-play mi-play"></i></a>
                              <?php } ?>
                          </div>
                      </div>   
                      
      </div>


      <div class="container-l">
        <h1 class="video-title">CLASE DE <?php the_field('tipo_de_yoga'); ?><?php if (get_field('plan') == "Gratis") { ?> GRATIS <?php } ?> EN DIRECTO </h1>     
        <p class="subtitle-clase-directo"><?php the_field('dia_y_hora'); ?></p>         
        <div class="post-autor-wrapper">
                <?php $profesor = get_field('profesor'); 
                $profesor_image = $profesor['user_avatar'];
                preg_match("/src=['\"](.*?)['\"]/i", $profesor_image, $matches);
                ?>
            <img class="video-autor-img" src="<?php echo $matches[1] ?>" alt="Profesor de yoga <?php echo $profesor['display_name']; ?>"/>
            <p class="video-autor-name"> <?php echo $profesor['display_name']; ?></p>
         </div>  
      
         
          <?php  if ( !empty( get_the_content() ) ) {  ?> 
           <?php the_content(); ?>
           &nbsp;
          <?php }  ?>
        
          <p class="consejo-clase-directo"><i class="fas fa-glasses"></i> Conoce más sobre la clase de <a class="" href="https://www.theclassyoga.com/tipos-yoga-clase/"> <?php echo strtolower(get_field('tipo_de_yoga')); ?></a> y sobre el profe <a class="" href="https://www.theclassyoga.com/profesores-yoga/"><?php echo $profesor['display_name']; ?></a> </p>  
          <p class="consejo-clase-directo"><i class="fas fa-link"></i> Plan  
          <?php if (get_field('plan') == "Gratis") { ?> <span class="clase-plan alert-success">Gratis</span> <?php } else { ?> <span class="clase-plan alert-primary">Premium</span><?php } ?>&nbsp;<?php echo $textReproductorCTA ?></p>  
          <p class="consejo-clase-directo"><i class="fas fa-video"></i> Enciende tú cámara para que el profesor pueda corregirte. Así también podrás ver al resto de yoguis.</p>  
          <p class="consejo-clase-directo"><i class="fas fa-microphone-alt-slash"></i> Cuando no quieras hablar apaga tu micrófono. </p>
          <p class="consejo-clase-directo"><i class="fas fa-mobile-alt"></i> Si entras con el móvil, en el último paso, selecciona "marcar utilizando de internet".</p>  



      </div>


         


    </main>


        
   <?php endwhile; endif;?>


   <script>

/**
*
* ANIMACIÓN DEL PLAY
*
*/

var reproductorCTAimg = document.getElementById("js-reproductor-CTA-img");
var playClaseEmbed = document.getElementById("js-play-clase-embed");
var claseEmbed = document.getElementById("js-clase-embed");
var ctaClaseEmbed = document.getElementById("js-cta-clase-embed");

var displayClaseEmbed = () => {
//reproductorCTAimg.classList.toggle("reproductor-CTA-img-hidden");
//playClaseEmbed.classList.toggle("play-clase-embed-hidden");
//claseEmbed.classList.toggle("clase-embed-display");
ctaClaseEmbed.classList.add("cta-clase-embed-display");
};

playClaseEmbed.addEventListener("click", displayClaseEmbed);

</script>


<?php get_footer(); ?>