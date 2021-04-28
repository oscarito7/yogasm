<?php get_header(); 

  $_SESSION['landing_before_login'] =  home_url( $wp->request );  
  $_SESSION['restricted'] =  "no";
  /*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
  /*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
  /*$_SESSION['last_class_plan'] = "Gratis";*/
  
  ?>

<?php
$args = array(
    'post_type' => 'clases_yoga',
    'posts_per_page' => 45,
    'facetwp' => true, // we added this
    //'meta_key'			=> 'visualizaciones',
	//'orderby'			=> 'meta_value_num',
	//'order'				=> 'DESC'
);
$query = new WP_Query( $args );?>


    <main>
      <h1>CLASES DE YOGA GRABADAS</h1>

      <p class="subtitle">
       Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor
      </p>


      <div class="container-xl">

        <div class="filter-list">
          <?php echo do_shortcode('[facetwp facet="tipo"]'); ?>
          <?php //echo do_shortcode('[facetwp facet="duracion"]'); ?>
          <div>
              <div>Duración</div>
              <?php echo do_shortcode('[facetwp facet="duracion_exacta"]'); ?>
          </div>
          <?php echo do_shortcode('[facetwp facet="nivel"]'); ?>
          <?php echo do_shortcode('[facetwp facet="intensidad"]'); ?>
          <?php echo do_shortcode('[facetwp facet="foco"]'); ?>
          <?php echo do_shortcode('[facetwp facet="profesor"]'); ?>
        </div>
        

        <div class="filter-result">
          Tienes <?php echo do_shortcode( '[facetwp counts="true"]' ); ?>
          <div class="filter-ordenadas">Ordenar por: <?php echo do_shortcode( '[facetwp sort="true"]');?></div>
          <?php /* <div class="filter-buscar">Si quieres puedes <?php echo do_shortcode('[facetwp facet="buscar"]'); ?></div> */ ?>
        </div>

        
        <ul class="video-grid">

        <?php if ($query->have_posts()):  while($query->have_posts()): $query->the_post(); ?>

        <?php if( get_field('duracion_exacta') != 0 ) { ?>

          <a href="<?php the_permalink(); ?>">
            <li class="video-item">
            <?php if (get_field('plan') == "Gratis") { ?>
            <div class="video-item-plan alert-success"><?php the_field('plan'); ?></div>
            <?php } ?>

            <?php if (get_field('nuevo') == "Nueva") { ?>
                <div class="video-item-plan alert-warning"><?php the_field('nuevo'); ?></div>
            <?php } ?>

            <?php if( get_field('imagen_inicio') ) { ?>
                <img class="video-item-img"  src="<?php the_field('imagen_inicio'); ?>" alt="<?php the_title(); ?>" />
            <?php } else {  ?>
              <img class="video-item-img"  src="<?php bloginfo('template_url'); ?>/img/clase-yoga.jpg" alt="<?php the_title(); ?>" />
            <?php } ?>
          
              <h2 class="video-item-title"><?php the_title(); ?></h2>
              <p class="video-item-filter"> <?php the_field('tipo'); ?> | <?php the_field('duracion_exacta') ?> min | <?php the_field('nivel') ?></p> 
            
              <div class="video-item-autor-wrapper">
                <p class="video-item-autor-name">
                <?php the_field('visualizaciones') ?> yoguis  | <?php $profesor = get_field('profesor'); echo $profesor['display_name']; ?> 
                </p>
              </div>

            </li>
          </a> 

       <?php } ?>




          
        <?php endwhile; endif;?>
        
        <?php wp_reset_query(); ?>
        
      
      </ul>

      <?php echo do_shortcode('[facetwp facet="paginador"]'); ?>



      </div>
    </main>

<?php get_footer(); ?>
