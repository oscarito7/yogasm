<?php
/**
 * Customer processing order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-order.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>

<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<p>Acabas de completar tu suscripción en THECLASSyoga.</p>

<p>Tu suscripción se renovará de forma automática cada mes. Podrás ver tus recibos/facturas en <a href="https://www.theclassyoga.com/cuenta">tu cuenta</a>.</p>

<p>¿Y ahora qué?</p>

<ul>
	<li><a href="https://www.theclassyoga.com/programas-yoga/">Programas de yoga:</a> Varios días seguidos de clases de yoga con una misma intención.</li>
	<li><a href="https://www.theclassyoga.com/clases-grabadas/">Clases grabadas:</a> Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor.</li>
	<li><a href="https://www.theclassyoga.com/clases-directo">Clases en directo:</a> Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude.</li>
	<!--<li><a href="https://www.theclassyoga.com/blog">Blog yogi:</a> Lee artículos sobre yoga y sé la mejor versión de ti mism@.</li>-->
</ul>

<p><strong>Siente lo mismo que si estuvieras en un centro de yoga!</strong>
<br/>
-- De todos los yoguis en THECLASSyoga</p>

<?php 
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
