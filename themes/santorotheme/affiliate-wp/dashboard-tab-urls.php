<?php
$affiliate_id = affwp_get_affiliate_id();
$all_coupons  = affwp_get_affiliate_coupons( $affiliate_id );

if ( $all_coupons ) {
    foreach ( $all_coupons as $type => $coupons ) {
        foreach ( $coupons as $coupon ) {
                $ultimo_cupon = $coupon['code'];
                break;
        }
         break;  
    }
}
 
 ?>




<p class="subtitle">Y además gana 10€ para ti</p>

<div style="text-align:center">
<h2>¿Qué tengo que hacer?</h2>

<p><strong>1)</strong> Busca ese amigo que quieres que haga yoga desde casa</p>
<p><strong>2)</strong> Comparte con él tu código amigo de 10€.</p>
<p><strong>3)</strong> Cuando use tu código, tú también recibirás 10€</p>

<br/>
<h2>¿Cuál es mi código amigo?</p>
<p>Tu código es: <mark class="resaltado rotate2"><?php echo $ultimo_cupon;?></strong></mark>
<p>Si lo prefieres, también tienes tu link de amigo, con el que si tu amigo hace tú código se aplica automáticamente: <strong><?php echo esc_url( urldecode( affwp_get_affiliate_referral_url() ) ); ?> </p>

<br/>
<h2>¿Cómo sé cuántos amigos usan mi código?</h2>
<p>Aquí puedes ver todas las estadísticas</p>
<p><a href="https://www.theclassyoga.com/plan-amigo/?tab=visits">Visitas de amigos</a> | <a href="https://www.theclassyoga.com/plan-amigo/?tab=referrals">Amigos usando tu código</a> | <a href="https://www.theclassyoga.com/plan-amigo/?tab=payouts">Tu dinero</a> | <a href="https://www.theclassyoga.com/plan-amigo/?tab=stats">Estadísticas globales</a></p>

<p>¡Namaste!<br>


&nbsp;

&nbsp;
</div>