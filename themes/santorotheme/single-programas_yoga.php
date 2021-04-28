
 <?php get_header(); 

$_SESSION['landing_before_login'] = home_url( $wp->request ); 
$_SESSION['restricted'] =  "no";
/*$_SESSION['last_class_seen'] = home_url( $wp->request ); 
$_SESSION['last_class_seen_directo'] = home_url( $wp->request ); 
$_SESSION['last_class_plan'] = get_field('plan');*/
  
 ?>




    <?php if (have_posts()):  while(have_posts()): the_post(); ?>
    
    <?php $_SESSION['last_programa_seen'] = home_url( $wp->request );  ?>

      <main>

      <div class="container-xl programa">


            <!-- parte izquierda o central -->
            <div class="video-presentacion">
                      <div class="container-l">

                            <div id="js-wrap-16-9" class="wrap-16-9">

                                        <!-- Video de la clase: visible hasta que segun el estado del usuario se pone encima  el div con el video CTA de arriba -->
                              <div class="video-clase">                 
                                        <script src="https://cdn.jwplayer.com/players/<?php echo get_field('id_video');?>.js"></script>
                                        <script type="application/javascript">
                                              var div_player = "botr_" + "<?php echo get_field('id_video');?>" + "_div";
                                              div_player = div_player.replace (/-/g, "_"); //esto lo hago porque en jwplayer es con guion medio pero el div luego luego lo pone con guion bajo
                                              var reproductor = jwplayer(div_player);
                                        </script>
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

                        <div class="programa-properties">
                   
                              <h4>TIPO DE YOGA</h4>
                              <p><?php the_field('tipo'); ?></p>
                              <h4>NÚMERO DE CLASES</h4>
                              <p><?php the_field('numero_de_videos'); ?></p>
                              <h4>DURACIÓN TOTAL</h4>
                              <p><?php echo toHours(get_field('duracion_exacta'), '');?></p>
                              <h4>NIVEL</h4>
                              <p><?php the_field('nivel'); ?></p>
                              <h4>INTENSIDAD</h4>
                              <p><?php the_field('intensidad'); ?></p>
                              <h4>FOCO</h4>
                              <p><?php the_field('foco'); ?></p>
                              <h4>SOPORTES</h4>
                              <p><?php the_field('soportes'); ?></p>
                              <h4>PLAN</h4>
                              <?php if (get_field('plan') == "Gratis") { ?>
                                <p><span class="clase-plan alert-success">Gratis</span> </p>
                              <?php } else { ?>
                                <p><span class="clase-plan alert-primary">Premium</span> </p>
                              <?php } ?>
                              </div>
                              <?php  if ( !empty( get_the_content() ) ) {   ?>
                               <div class="container-l programa-properties-description">
                                  <h4>DESCRIPCIÓN</h4>
                                    <div id="js-leer-mas-clase-description" class="leer-clase-description"><a href="#" onclick="leerClaseDescription(); return false;">Leer...</a></div>
                                    <div id="js-clase-description" class="clase-description"><?php the_content(); ?></div>
                              </div>
                            <?php }  ?>
                        </div>
                    
  
            </div> <!-- fin video presentacion -->




            <!-- columna derecha -->
            <div class="videos-programa">
                <h3 class="videos-programa-encabezado">Clases del programa</h3>

                <?php if( have_rows('videos') ): ?>

                  <ul class="videos-programa-list">
                        <?php while( have_rows('videos') ): the_row(); 

                          // vars
                          $image = get_sub_field('imagen');
                          $url = get_sub_field('url');
                          $titulo = get_sub_field('titulo');

                          ?>

                          <li class="videos-programa-item">
                          <?php if (get_sub_field('plan') == "Gratis") { ?>
                          <div class="videos-programa-item-plan alert-success"><?php the_sub_field('plan'); ?></div>
                          <?php } ?>
                              <a href="<?php echo $url; ?>">
                                <img class="videos-programa-item-image" src="<?php echo $image['url']; ?>"  alt="<?php echo $image['alt'] ?>" />
                                <div class="videos-programa-item-text">
                                  <h3 class="videos-programa-item-title"><?php echo $titulo; ?></h3>
                                  <p class="videos-programa-filter"><?php the_sub_field('tipo'); ?> | <?php the_sub_field('duracion_exacta'); ?> min | <?php the_sub_field('nivel'); ?></p>
                                </div>
                              </a>
                          </li>

                        <?php endwhile; ?>

                  </ul>

                  <?php endif; ?>

                
            </div>


      </div>
 
    </main>

  <script type="application/javascript">
 
   <?php endwhile; endif;?>


   reproductor.on('ready', function(){
                    var wrapper = document.getElementById("js-wrap-16-9");
                    wrapper.classList.add('padding-bottom-off');
                    })

    </script>

<?php get_footer(); ?>