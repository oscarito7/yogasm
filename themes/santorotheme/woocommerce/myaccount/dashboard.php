<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>    



<div class="container-xl my-mat">
		<?php if (wcs_user_has_subscription( '', '', '')) {
              if (wcs_user_has_subscription( '', '', 'active')) { 
				?>
				<!-- no tngo nada porque está activo y no quiero poner ningun mensaje -->
              <?php } else if (wcs_user_has_subscription( '', '', 'pending-cancel')) { 
				?>
				<?php 
				$users_subscriptions	=			wcs_get_users_subscriptions(get_current_user_id() );
				// esto lo hago por que es la unica manera que he encontrado de sacar la ultima suscripción. Se que es una ñaña
				foreach ($users_subscriptions as $subscription){ break; } ?>				
				
				<p>Oops! ¿Has cancelado THECLASSyoga?</p>
				<button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url( $subscription->get_view_order_url() ); ?>'">Ir a reactivar tu suscripción</button>
				<br/>
				<p>¡Buenas noticias! Puedes seguir practicando con las clases <span class="clase-plan alert-success">gratis</span> de THECLASSyoga</p>
				<br/>
		
			 
			 <?php } else if (wcs_user_has_subscription( '', '', 'cancelled')) { 
			  ?>
			  	<?php 
				$users_subscriptions	=			wcs_get_users_subscriptions(get_current_user_id() );
				// esto lo hago por que es la unica manera que he encontrado de sacar la ultima suscripción. Se que es una ñaña
				foreach ($users_subscriptions as $subscription){ break; } ?>				

				<p>Oops! Tu cuenta está cancelada.</p>
				<button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url( $subscription->get_view_order_url() ); ?>'">Ir a reactivar tu suscripción</button>
				
				<p>¡Buenas noticias! Puedes seguir practicando con las clases <span class="clase-plan alert-success">gratis</span> de THECLASSyoga</p>
				<br/>

  			<?php } else if (wcs_user_has_subscription( '', '', 'on-hold')) {     
                ?>
				<?php 
				$users_subscriptions	=			wcs_get_users_subscriptions(get_current_user_id() );
				// esto lo hago por que es la unica manera que he encontrado de sacar la ultima suscripción. Se que es una ñaña
				foreach ($users_subscriptions as $subscription){ break; } ?>				

				<p>Oops! Tu forma de pago ha fallado...</p>

				<button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url( $subscription->get_view_order_url() ); ?>'">Ir a cambiar forma de pago</button>
				<p>¡Buenas noticias! Puedes seguir practicando con las clases <span class="clase-plan alert-success">gratis</span> de THECLASSyoga</p>
				<br/>

			  <?php } else if (wcs_user_has_subscription( '', '', 'expired')) {
                ?>
				<p>Oops! Tu cuenta ha expirado.</p>
				<?php 
				$users_subscriptions	=			wcs_get_users_subscriptions(get_current_user_id() );
				// esto lo hago por que es la unica manera que he encontrado de sacar la ultima suscripción. Se que es una ñaña
				foreach ($users_subscriptions as $subscription){ break; } ?>				
				<button class="btn btn-secondary" onclick="window.location.href='<?php echo esc_url( $subscription->get_view_order_url() ); ?>'">Ir a reactivar tu suscripción</button>
				<p>¡Buenas noticias! Puedes seguir practicando con las clases gratis de THECLASSyoga</p>
				<br/>

			  <?php } else { 
                ?>
				<p>Oops! Ha ocurrido un error. Escríbenos a <a href="mailto:soporte@theclassyoga.com">soporte@theclassyoga.com</a></p>
				<p>¡Buenas noticias! Puedes seguir practicando con las clases <span class="clase-plan alert-success">gratis</span> de THECLASSyoga</p>
				<br/>
              <?php }?>
			  <br/>
			  <ul class="home-grid">
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Flexibilidad-intro.jpg" alt="Programas de yoga" />
					<a href="<?php home_url();?>/programas-yoga/"><h3>Programas</h3></a> 
					<p class="subtitle3">Varios días seguidos de clases de yoga con una misma intención</p> 
					<?php if (isset($_SESSION['last_programa_seen'])) { ?> 
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo $_SESSION['last_programa_seen']; ?>'">Tu último programa</button>
					<?php } else { ?>
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/programas-yoga'">Ver programa</button>
					<?php } ?>
				</li>
	
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Yoga-x-manana-3.jpg" alt="Clases sueltas grabadas" />          
					<a href="<?php home_url();?>/clases-grabadas/"><h3>Clases grabadas</h3></a>
					<p class="subtitle3">Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor</p> 
					<?php if (isset($_SESSION['last_class_seen'])) { ?> 
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo $_SESSION['last_class_seen']; ?>'">Tu última clase grabada</button>
						<?php } else { ?>
							<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/clases-grabadas'">Ver clases grabadas</button>
					<?php } ?>					
				</li>
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/clases-directo-1.jpg" alt="Clases en directo por streaming" />          
					<a href="<?php home_url();?>/clases-directo/"><h3>Clases directo</h3></a>
					<p class="subtitle3">Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude</p> 
					<?php if (isset($_SESSION['last_class_seen_directo'])) { ?> 
					<button class="btn btn-secondary"  style="margin:0rem" onclick="window.location.href='<?php echo $_SESSION['last_class_seen_directo']; ?>'">Tu última clase directo</button>
					<?php } else { ?>
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/clases-directo'">Ver clases directo</button>
					<?php } ?>	
										
				</li>     
		</ul>
		<br/>
		<br/>

		<?php } else { ?>
		
		<br/>			
		<ul class="home-grid">
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Flexibilidad-intro.jpg" alt="Programas de yoga" />
					<a href="<?php home_url();?>/programas-yoga/"><h3>Programas</h3></a> 
					<p class="subtitle3">Varios días seguidos de clases de yoga con una misma intención</p> 
					<?php if (isset($_SESSION['last_programa_seen'])) { ?> 
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo $_SESSION['last_programa_seen']; ?>'">Tu último programa</button>
					<?php } else { ?>
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/programas-yoga'">Ver programa</button>
					<?php } ?>
				</li>
	
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/06/Yoga-x-manana-3.jpg" alt="Clases sueltas grabadas" />          
					<a href="<?php home_url();?>/clases-grabadas/"><h3>Clases grabadas</h3></a>
					<p class="subtitle3">Elige el tipo de yoga, duración, nivel, intensidad, foco del cuerpo y profesor</p> 
					<?php if (isset($_SESSION['last_class_seen'])) { ?> 
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo $_SESSION['last_class_seen']; ?>'">Tu última clase grabada</button>
						<?php } else { ?>
							<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/clases-grabadas'">Ver clases grabadas</button>
					<?php } ?>					
				</li>
				<li class="home-item">
					<img class="home-item-img"  src="https://www.theclassyoga.com/wp-content/uploads/2020/07/clases-directo-1.jpg" alt="Clases en directo por streaming" />          
					<a href="<?php home_url();?>/clases-directo/"><h3>Clases directo</h3></a>
					<p class="subtitle3">Clases online en directo para practicar a la vez con otros yoguis y un profe que te ayude</p> 
					<?php if (isset($_SESSION['last_class_seen_directo'])) { ?> 
					<button class="btn btn-secondary"  style="margin:0rem" onclick="window.location.href='<?php echo $_SESSION['last_class_seen_directo']; ?>'">Tu última clase directo</button>
					<?php } else { ?>
						<button class="btn btn-secondary"  style="margin:0rem"  onclick="window.location.href='<?php echo home_url()?>/clases-directo'">Ver clases directo</button>
					<?php } ?>	
										
				</li>     
		</ul>
		<br/>
		<br/>
		<p>Tienes clases <span class="clase-plan alert-success">Gratis</span> y clases <span class="clase-plan alert-primary">Premium</span> para las que tienes que <a href="<?php home_url()?>/completar-alta/">completar tu alta</a>.</p>
		<br/>
		<?php } ?>  
          
		<p style="text-align:center; margin-top:0rem;">¿Te encanta THECLASSyoga? <a href="<?php home_url()?>/afiliado/">Regala 10€ a un amigo. Y además gana 10€ para ti</a> </p>
		<br/>

		<p style="text-align:left;">¿Necesitas ayuda? Lee en <a href="https://preguntasfrecuentes.groovehq.com/help">preguntas frecuentas </a>o escríbenos a <a href="mailto:soporte@theclassyoga.com">soporte@theclassyoga.com</a>  </p>
		<br/>
		<p>Quizás quieres <a class="linkColorNoUnderline" href="<?php echo esc_url( wc_logout_url() ); ?>">cerrar sesión.</a> </p> 


</div>





<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
