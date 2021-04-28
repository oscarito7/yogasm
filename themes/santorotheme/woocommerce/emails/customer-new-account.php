<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


<?php /* translators: %s: Customer username */ ?>

<p>¡Ya eres de THECLASSyoga!</p> 

<p>Eres yogui de la primera comunidad de clases de yoga online en español.</p>

<p>Lo que vas a encontrar:</p>

<ul>
	<li><a href="https://www.theclassyoga.com/programas-yoga/">Programas de yoga:</a> Varios días seguidos de clases de yoga con una misma intención.</li>
	<li><a href="https://www.theclassyoga.com/clases-grabadas/">Clases grabadas:</a> Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor.</li>
	<li><a href="https://www.theclassyoga.com/clases-directo">Clases en directo:</a> Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude.</li>
	<!--<li><a href="https://www.theclassyoga.com/blog">Blog yogi:</a> Lee artículos sobre yoga y sé la mejor versión de ti mism@.</li>-->
</ul>

<p>Tienes clases "Gratis" y clases "Premium" que son de pago para las que tienes que <a href="https://www.theclassyoga.com/completar-alta/">completar tu alta</a>

<p><strong>Siente lo mismo que si estuvieras en un centro de yoga.</strong>
<br/>
-- De todos los yoguis en THECLASSyoga</p>


<?php


do_action( 'woocommerce_email_footer', $email );
