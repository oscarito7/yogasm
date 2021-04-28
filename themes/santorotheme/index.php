<?php get_header(); ?>



<?php
$args = array(
    'post_type' => 'clases_yoga',
    'posts_per_page' => 10,
    'facetwp' => true, // we added this
);
$query = new WP_Query( $args );?>

<?php echo facetwp_display( 'facet', 'profesor' ); ?>


<H1>ESTO ES INDEX.PHP</H1>

<div class="facetwp-template">

<?php if ($query->have_posts()):  while($query->have_posts()): $query->the_post(); ?>

<h1>
<?php the_title(); ?>

</h1>

<?php endwhile; endif;?>

</div>





<?php get_footer(); ?>