<?php get_header(); 

$_SESSION['landing_before_login'] = home_url()."/cuenta/";  /*ESE ES DISTINTO QUE EN EL RESTO DE SINGLES PORQUE EN EL BLOG QUIERO QUE VAYA  MI CUENTA */
$_SESSION['restricted'] =  "no";
/*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
/*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
/*$_SESSION['last_class_plan'] = "Gratis";*/

?>

        <?php if (have_posts()):  while(have_posts()): the_post(); ?>
        
        <main>
          <div class="container-l">
            <h1>
                <?php the_title();?>
            </h1>
            <div class="post-autor-wrapper">
              <img class="post-autor-img" src="<?php echo get_avatar_url( get_the_author_meta('ID')); ?>" alt="profe de yoga <?php the_author(); ?>" />
              <p class="post-autor-name">
                <?php the_author();?>
              </p>
            </div>

            <img class="post-image" src="<?php the_post_thumbnail_url();?>" alt="<?php the_title();?>" />

            <div class="post-content">
                <p class="subtitle-post"><?php the_field('subtitulo1'); ?></p>
                <?php the_content(); ?>
            </div>

          <?php /*
            <h2 class="post-CTA-title">OTROS POSTS</h2>
              <div class="post-navigation">
                <div>
                <?php $prev_post = get_previous_post(); ?>
                <?php if ( !empty( $prev_post ) ) : ?>
                    <a href="<?php echo get_permalink( $prev_post->ID ); ?>">« <?php echo $prev_post->post_title; ?></a>
                <?php endif; ?>
                </div>
                <div class="post-navigation-next">
                <?php $next_post = get_next_post(); ?>
                <?php if ( !empty( $next_post ) ) : ?>
                  <a href="<?php echo get_permalink( $next_post->ID ); ?>"><?php echo $next_post->post_title; ?> »</a>
                  <?php endif; ?>

                </div>
              </div>

            */ ?>


          
          </div>

          <div class="container-l display-flex-column-align-items">
            <h2 class="post-CTA-title">¿QUIERES HACER CLASES DE YOGA ONLINE?</h2>
            <p class="CTA-subtitle">
            Clases de yoga adaptadas a tu forma de vida
            </p>
            <br/>
            <ul class="home-grid">

            <a href="<?php home_url();?>/programas-yoga/">
                <li class="home-item">
                    <img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Flexibilidad-intro.jpg" alt="Programas de yoga" />
                    <h3>Programas</h3>
                    <p class="subtitle3">Varios días seguidos de clases de yoga con una misma intención</p> 
                    <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/programas-yoga/'">Programas</button>
                </li>
            </a>   
            <a href="<?php home_url();?>/clases-grabadas/">
                <li class="home-item">
                    <img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Yoga-x-manana-3.jpg" alt="Clases sueltas grabadas" />          
                    <h3>Clases grabadas</h3>
                    <p class="subtitle3">Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor</p> 
                    <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/clases-grabadas/'">Clases grabadas</button>
                </li>
            </a>         
            <a href="<?php home_url();?>/clases-directo/">
                <li class="home-item">
                    <img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/clases-directo-1.jpg" alt="Clases en directo por streaming" />          
                    <h3>Clases en directo</h3>
                    <p class="subtitle3">Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude</p> 
                    <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/clases-directo/'">Clases directo</button>
                </li>
            </a>         
            </ul>

          </div> 




          

        </main>
        
        <?php endwhile; endif;?>

<!-- This site is converting visitors into subscribers and customers with OptinMonster - https://optinmonster.com -->
<script type="text/javascript" src="https://a.omappapi.com/app/js/api.min.js" data-account="87313" data-user="77644" async></script>
<!-- / https://optinmonster.com -->
    
<?php get_footer(); ?>