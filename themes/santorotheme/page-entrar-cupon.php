<?php get_header(); 

/*$_SESSION['landing_before_login'] = home_url()."/cuenta/"; /* AQUÍ NUNCA LO PONGO PORQUE SI NO SE QUEDA SIEMPRE EN CUENTA Y NO VOLVERÍA AL VIDEO JAMAS */ 
/*$_SESSION['restricted'] =  "no";*/
/*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
/*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
/*$_SESSION['last_class_plan'] = "Gratis";*/
$_SESSION['coupon_visible'] = "si";

?>

<?php

if (have_posts()):  while(have_posts()): the_post(); ?>


<main>
  

    <div class="container-l">
            <h1 class="post-title"><?php the_title();?></h1>
                   
            <div class="page-content">
                <?php the_content(); ?>
            </div>

    </div>

</main>


<?php endwhile; endif;?>




</main>


<?php get_footer(); ?>