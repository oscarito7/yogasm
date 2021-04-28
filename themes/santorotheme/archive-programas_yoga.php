<?php get_header(); 

  $_SESSION['landing_before_login'] =  home_url( $wp->request ); 
  $_SESSION['restricted'] =  "no";  /*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
  /*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
  /*$_SESSION['last_class_plan'] = "Gratis";*/
  
?>

<?php
$args = array(
    'post_type' => 'programas_yoga',
    'posts_per_page' => 44,
    'facetwp' => true, // we added this
);
$query = new WP_Query( $args );?>


    <main>
      <h1>PROGRAMAS DE YOGA</h1>

      <p class="subtitle">
        Varios días seguidos de clases de yoga con una misma intención
      </p>


      <div class="container-xl">

<?php /* lo englobo en php para comentarlo todo

     <div class="filter-list"> 
          <?php echo do_shortcode('[facetwp facet="tipo_programa"]'); ?>
          <?php echo do_shortcode('[facetwp facet="clases_programa"]'); ?>
          <?php echo do_shortcode('[facetwp facet="nivel_programa"]'); ?>
          <?php echo do_shortcode('[facetwp facet="intensidad_programa"]'); ?>
          <?php echo do_shortcode('[facetwp facet="foco_programa"]'); ?>
          <?php echo do_shortcode('[facetwp facet="profesor_programa"]'); ?>
        </div>
        
        <div class="filter-result">
        Después de tu filtrado hay <?php echo do_shortcode( '[facetwp counts="true"]' ); ?> programas.&nbsp;&nbsp;&nbsp;<a class="linkColor" href="" onclick="FWP.reset()">Ver todos los programas</a></p>
        </div>


*/ ?>

        <ul class="video-grid">

        <?php if ($query->have_posts()):  while($query->have_posts()): $query->the_post(); ?>

          <a href="<?php the_permalink(); ?>">
            <li class="video-item">
            <?php if (get_field('plan') == "Gratis") { ?>
            <div class="video-item-plan  alert-success"><?php the_field('plan'); ?></div>
            <?php } ?>

            <?php if (get_field('nuevo') == "Nuevo") { ?>
            <div class="video-item-plan  alert-warning"><?php the_field('nuevo'); ?></div>
            <?php } ?>
 
            <?php if( get_field('imagen_inicio') ) { ?>
                <img class="video-item-img"  src="<?php the_field('imagen_inicio'); ?>" alt="<?php the_title(); ?>" />
            <?php } else {  ?>
              <img class="video-item-img"  src="<?php bloginfo('template_url'); ?>/img/clase-yoga-directo.jpg" alt="<?php the_title(); ?>" />
            <?php } ?>
          
              <h2 class="video-item-title"><?php the_title(); ?></h2>
              <p class="video-item-filter"><?php the_field('numero_de_videos') ?> clases | <?php the_field('tipo'); ?></p> 

              <div class="video-item-autor-wrapper">
                <p class="video-item-autor-name">
                  <?php $profesor = get_field('profesor'); echo $profesor['display_name']; ?> 
                </p>
              </div>
            </li>
          </a> 

          
        <?php endwhile; endif;?>
        
        <?php wp_reset_query(); ?>
        
      
      </ul>

      <?php echo do_shortcode('[facetwp facet="paginador_programa"]'); ?>



      </div>
    </main>

<?php get_footer(); ?>
