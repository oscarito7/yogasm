
<?php get_header(); 

  $_SESSION['landing_before_login'] =  home_url( $wp->request );  
  $_SESSION['restricted'] =  "no";
  /*$_SESSION['last_class_seen'] = home_url( $wp->request );*/ 
  /*$_SESSION['last_class_seen_directo'] = home_url( $wp->request );*/   
 /* $_SESSION['last_class_plan'] = "Gratis";*/
  
  ?>

<main>

<h1 class="post-title">CLASES ONLINE EN DIRECTO</h1>
<p class="subtitle">Practica a la vez con más yoguis y un profe que te ayude</p>



    <div class="container-xl my-calendar">
        <div class="flecha-ayuda"></div>
        <div class="timetable">
            <section class="timeWrapper">
                    <div>08:00</div>
                    <div></div>
                    <div>09:00</div>
                    <div></div>
                    <div>10:00</div>
                    <div></div>
                    <div>11:00</div>
                    <div></div>
                    <div>12:00</div>
                    <div></div>
                    <div>13:00</div>
                    <div></div>
                    <div>14:00</div>
                    <div></div>
                    <div>15:00</div>
                    <div></div>
                    <div>16:00</div>
                    <div></div>
                    <div>17:00</div>
                    <div></div>
                    <div>18:00</div>
                    <div></div>
                    <div>19:00</div>
                    <div></div>
                    <div>20:00</div>
                    <div></div>
                    <div>21:00</div>
                    <div></div>
                    <div></div>
                </section>
            
            <section class="titleWrapper">
                <div class="timeColumn"></div>
                    <div class="monday">LUNES</div>
                    <div class="tuesday">MARTES</div>
                    <div class="wednesday">MIÉRCOLES</div>
                    <div class="thursday">JUEVES</div>
                    <div class="friday">VIERNES</div>
                    <div class="saturday">SÁBADO</div>
                    <div class="sunday">DOMINGO</div>
                </section>
             
            <!--<a class="tableElement monday tenAM endEleven30AM" href="<?php home_url()?>/clases-directo/lunes-10/">ROCKET <br><span class="hora">10:00 a 11:15</span><br><span class="hora">David Cabezas</span></a>-->
            <!-- <a class="tableElement monday twelvePM endOne30PM" href="<?php home_url()?>/clases-directo/lunes-12/">JIVAMUKTI XL <br><span class="hora">12:00 a 13:30</span><br><span class="hora">Candida Vivalda</span></a>-->
            <a class="tableElement monday twoPM endThree30PM" href="<?php home_url()?>/clases-directo/lunes-14/">JIVAMUKTI <br><span class="hora">14:00 a 15:15</span></a>
            <a class="tableElement monday sixPM endSeven30PM" href="<?php home_url()?>/clases-directo/lunes-18/">VINYASA <br><span class="hora">18:00 a 19:15</span></a>
            <a class="tableElement monday eightPM endNine30PM" href="<?php home_url()?>/clases-directo/lunes-20/">POWER <br><span class="hora">20:00 a 21:15</span></a>

            <!--<a class="tableElement tuesday eightAM endNine30AM" href="<?php home_url()?>/clases-directo/martes-8/">VINYASA <br><span class="hora">8:00 a 09:15</span><br><span class="hora">Sofia Paravisini</span></a> -->
            <!--<a class="tableElement tuesday elevenAM endTwelve30PM" href="<?php home_url()?>/clases-directo/martes-11/">JIVAMUKTI XL <br><span class="hora">11:00 a 12:30</span><br><span class="hora">Candida Vivalda</span></a>-->
            <a class="tableElement tuesday twoPM endThree30PM" href="<?php home_url()?>/clases-directo/martes-14/">VINYASA<br><span class="hora">14:00 a 15:15</span></a>
            <a class="tableElement tuesday sixPM endSeven30PM" href="<?php home_url()?>/clases-directo/martes-18/">VINYASA<br><span class="hora">18:00 a 19:15</span></a>
            <a class="tableElement tuesday eightPM endNine30PM" href="<?php home_url()?>/clases-directo/martes-20/">POWER <br><span class="hora">20:00 a 21:15</span></a>

            <a class="tableElement wednesday eightAM endNine30AM" href="<?php home_url()?>/clases-directo/miercoles-8/">INTEGRAL <br><span class="hora">08:00 a 09:15</span></a>
            <a class="tableElement wednesday twoPM endThree30PM" href="<?php home_url()?>/clases-directo/miercoles-14/">VINYASA <br><span class="hora">14:00 a 15:15</span></a>
            <a class="tableElement wednesday sixPM endSeven30PM" href="<?php home_url()?>/clases-directo/miercoles-18/">JIVAMUKTI <br><span class="hora">18:00 a 19:15</span></a>
            <a class="tableElement wednesday eightPM endNine30PM" href="<?php home_url()?>/clases-directo/miercoles-20/">VINYASA <br><span class="hora">20:00 a 21:15</span></a>

            <a class="tableElement thursday eightAM endNine30AM" href="<?php home_url()?>/clases-directo/jueves-8/">ROCKET <br><span class="hora">8:00 a 09:15</span></a>
            <!--<a class="tableElement thursday eleven30AM endOnePM" href="<?php home_url()?>/clases-directo/jueves-11/">VINYASA XL <br><span class="hora">11:30 a 13:00</span><br><span class="hora">Marina Vara</span></a>-->
            <a class="tableElement thursday twoPM endThree30PM" href="<?php home_url()?>/clases-directo/jueves-14/">ROCKET <br><span class="hora">14:00 a 15:15</span></a>
            <a class="tableElement thursday sixPM endSeven30PM" href="<?php home_url()?>/clases-directo/jueves-18/">ROCKET <br><span class="hora">18:00 a 19:15</span></a>
            <a class="tableElement thursday eightPM endNine30PM" href="<?php home_url()?>/clases-directo/jueves-20/">YIN <br><span class="hora">20:00 a 21:15</span></a>

            <!--<a class="tableElement friday tenAM endEleven30AM" href="<?php home_url()?>/clases-directo/viernes-10/">VINYASA <br><span class="hora">10:00 a 11:15</span><br><span class="hora">Beatriz Perez </span></a>
            <a class="tableElement friday twoPM endThree30PM" href="<?php home_url()?>/clases-directo/viernes-14/">VINYASA <br><span class="hora">14:00 a 15:15</span><br><span class="hora">Sofia Paravisini</span></a>-->
            <a class="tableElement friday sixPM endSeven30PM" href="<?php home_url()?>/clases-directo/viernes-18/">JIVAMUKTI <br><span class="hora">18:00 a 19:15</span></a>


            <a class="tableElement saturday elevenAM endTwelve30PM" href="<?php home_url()?>/clases-directo/sabado-11/">POWER XL&nbsp;<span class="clase-plan alert-success">Gratis</span><br><span class="hora">11:00 a 12:30 </span></a>
            <!--<a class="tableElement saturday elevenAM endTwelve30PM" href="<?php home_url()?>/clases-directo/sabado-11/">JIVAMUKTI XL&nbsp;<span class="clase-plan alert-success">Gratis</span><br><span class="hora">11:00 a 12:30</span><br><span class="hora">Rebeca Recatero </span></a>-->

            <a class="tableElement sunday elevenAM endTwelve30PM" href="<?php home_url()?>/clases-directo/domingo-11/">VINYASA XL<span class="clase-plan alert-success">Gratis</span><br><span class="hora">11:00 a 12:30 </span></a>
            <!--<a class="tableElement sunday elevenAM endTwelve30PM" href="<?php home_url()?>/clases-directo/domingo-11/">VINYASA XL <span class="clase-plan alert-success">Gratis</span><br><span class="hora">11:00 a 12:30</span><br><span class="hora">Yulia Persova </span></a>-->

        </div>

    </div>


</main>


<?php get_footer(); ?>