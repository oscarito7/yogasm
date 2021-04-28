
<?php get_header(); 

$_SESSION['landing_before_login'] = home_url()."/cuenta/";  
$_SESSION['restricted'] =  "no";
/*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
/*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
/*$_SESSION['last_class_plan'] = "Gratis";*/

?>




<main class="mainhome">

<div class="section headerhero">

    <div id="vimeohero">
        <iframe class="iframe-home"  src="https://cdn.jwplayer.com/players/achIvHS2-zJrl1uMA.html" frameborder="0" scrolling="auto" title="Spot Apartamento" allowfullscreen></iframe> 
            <?php /* Para cuando pruebe a poner el vídeo en autoplay
                <script src="https://cdn.jwplayer.com/libraries/3P77nX5W.js"></script>
                <script type="text/javascript"> 
                    let player = jwplayer('player')
                    player.setup({
                        playlist: "https://cdn.jwplayer.com/v2/media/VjvowSFi", 
                        autostart: true 
                    })
                    </script> 
            */ ?>
    </div>
    
    
    <div class="ctahero">

        <h1>PRACTICA YOGA DESDE CASA</h1>
        <p class="subtitle">Siente lo mismo que si estuvieras en un centro de yoga</p>
        <div class="align-center">
            <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/entrar/'">Empezar ahora gratis</button>
        </div>
    </div>

</div>



  

<div class="container-xl section">

    <h2 class="titlehome">SE COMO TÚ QUIERAS</h2>
    <p class="subtitle">Clases de yoga adaptadas a tu forma de vida</p>

            <ul class="home-grid">

                <a href="<?php home_url();?>/programas-yoga/">
                    <li class="home-item">
                        <img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Flexibilidad-intro.jpg" alt="Programas de yoga" />
                        <h3>Programas</h3>
                        <p class="subtitle3">Varios días seguidos de clases de yoga con una misma intención</p> 
                        <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/programas-yoga/'">Programas yoga</button>
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
                        <img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/clases-directo-1.jpg" alt="Clases en directo" />          
                        <h3>Clases directo</h3>
                        <p class="subtitle3">Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude</p> 
                        <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/clases-directo/'">Clases directo</button>
                    </li>
                </a>         
            </ul>

</div>

<div class="container-xxl section">
 
  <?php /* <div class="reto-item">
            
            <A href="https://www.theclassyoga.com/programas-yoga/bikini-challenge/"><img class="reto-item-image" src="https://www.theclassyoga.com/wp-content/uploads/2020/06/bikini-challege-2.jpg" alt="reto de yoga" /></a>
            <div class="reto-item-text">
              <h2 class="titlehome">¡RETO GRATIS!<br/>OPERACIÓN BIKINI</h2>
              <!--<p class="subtitle">1 programa de yoga cada mes es gratis</p>-->
              <button class="btn btn-secondary" onclick="window.location.href='https://www.theclassyoga.com/programas-yoga/bikini-challenge/'">ÚNETE AHORA GRATIS</button>
              <p class="subtitle3" style="margin:1rem;">También tienes <a href="<?php home_url();?>/clases-grabadas/">clases online GRATIS</a> y <a href="<?php home_url();?>/clases-directo/">clases por streaming GRATIS</a>. Búscalas con la etiqueta&nbsp;<span class="clase-directo-plan">Gratis</span></p>

              <br/>
            </div>

    </div> */ ?>


</div>

<?php /*

<div class="container-xl section">

    <h2 class="titlehome">CONOCE A LOS PROFESORES</h2>

    <p class="subtitle">El equipo de mejores profesores en español</p>

            <ul class="profesores-grid">

                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/alejandra-estrada-bio.jpg" alt="Alejandra Estrada" />
                        <h4>Alejandra Estrada</h4>
                    </li>
                </a>   
                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/barbara-parra-bio.jpg" alt="Barbara Parra" />          
                        <h4>Barbara Parra</h4>
                    </li>
                </a>         
                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/candida-vivalda-bio.jpg" alt="Candida Vivalda" />          
                        <h4>Candida Vivalda</h4>
                    </li>
                </a>     

                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/david-cabezas-bio.jpg" alt="David Cabezas" />          
                        <h4>David Cabezas</h4>
                    </li>
                </a>     
                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/lucia-liencres-bio.jpg" alt="Lucía Liencres" />
                        <h4>Lucía Liencres</h4>
                    </li>
                </a>       
                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/luis-rivero-bio.jpg" alt="Luis Rivero" />          
                        <h4>Luis Rivero</h4>                    
                    </li>
                </a>     
                <a href="https://www.theclassyoga.com/profesores-yoga">
                    <li class="profesores-item">
                        <img class="profesores-item-img"  src="<?php bloginfo('template_url'); ?>/img/logo-theclassyoga-black.png" alt="Logo TheClass Yoga" />          
                        <h4>Ver todos</h4>                 
                    </li>
                </a>  
            </ul>

</div>


<div class="container-xl section">

    <h2 class="titlehome">LEE SOBRE YOGA</h2>

    <p class="subtitle">Posts más recientes</p>
            <ul class="posthome-grid">
                <?php 
                // Define our WP Query Parameters
                $the_query = new WP_Query( 'posts_per_page=3' ); ?>
                
                
                <?php 
                // Start our WP Query
                while ($the_query -> have_posts()) : $the_query -> the_post(); 
                // Display the Post Title with Hyperlink
                ?>
                <a href="<?php the_permalink();?>">
                        <li class="posthome-item">
                            <h3><?php the_title(); ?></h3>

                        </li>
                </a>     
                <?php 
                // Repeat the process and reset once it hits the limit
                endwhile;
                wp_reset_postdata();
                ?>
    
            </ul>

</div>

*/ ?>



<div class="container-xl">

    <h2 class="titlehome">¡Empieza ahora gratis!</h2>
    <p class="subtitle">Clases gratis y Clases premium</p>
    <div class="align-center">
        <button class="btn btn-secondary" onclick="window.location.href='<?php home_url();?>/entrar/'">Empezar ahora gratis</button>
    </div>

  
</div>

</main>

<?php get_footer(); ?>