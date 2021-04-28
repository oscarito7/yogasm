<?php get_header(); 

  $_SESSION['landing_before_login'] = home_url()."/cuenta/";  /*ESE ES DISTINTO QUE EN EL RESTO DE ARCHIVES PORQUE EN EL BLOG QUIERO QUE VAYA  MI CUENTA */
  $_SESSION['restricted'] =  "no";  /*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
  /*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
  /*$_SESSION['last_class_plan'] = "Gratis";*/

?>

    <main>
      <h1>LEE YOGA</h1>
      <p class="subtitle">Posts sobre yoga escritos con mucho amor</p>

      <div class="container-l">
        <ul class="post-list">

        <?php if (have_posts()):  while(have_posts()): the_post(); ?>
        
          <li class="post-item">
            <img class="post-item-image" src="<?php the_post_thumbnail_url();?>" alt="<?php the_title(); ?>" />
            <div class="post-item-text">
              <a href="<?php the_permalink(); ?>"><h2 class="post-item-title"><?php the_title(); ?></h2></a>
              <p class="post-item-description"><?php the_excerpt(); ?></p>
            </div>
          </li>
        
        <?php endwhile; endif;?>

        </ul>

        <div class="post-pagination">
            <div class="post-pagination-previous"><?php previous_posts_link(); ?></div>
            <div class="post-pagination-next"><?php next_posts_link(); ?></div>

        </div>


      </div>
    </main>

<?php get_footer(); ?>