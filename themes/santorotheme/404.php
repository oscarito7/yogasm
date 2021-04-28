<?php get_header(); ?>

<main>

<div class="container-m display-flex-column-align-items">
    <h1 class="post-title">Oops! </h1>
    <p class="subtitle">No podemos encontrarla página que estabas buscando</p>
    <img src="<?php echo home_url()?>/wp-content/uploads/2021/03/before-and-after-yoga-404.jpg" alt="error 404"/>
    <button class="btn btn-primary btn-jumbo" onclick="window.location.href='<?php echo home_url()?>'">Volver a la home</button>


</div>

</main>

<?php get_footer(); ?>