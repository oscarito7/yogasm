<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>The Class Yoga</title>


    <link rel="apple-touch-icon" sizes="57x57" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php bloginfo('template_url'); ?>/img/favicon//apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php bloginfo('template_url'); ?>/img/favicon//apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php bloginfo('template_url'); ?>/img/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?php bloginfo('template_url'); ?>/img/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php bloginfo('template_url'); ?>/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php bloginfo('template_url'); ?>/img/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php bloginfo('template_url'); ?>/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php bloginfo('template_url'); ?>/img/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php bloginfo('template_url'); ?>/img/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

   <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-164701274-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-164701274-1');
  </script>



<!-- Hotjar Tracking Code for www.theclassyoga.com -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:1989301,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>

<!-- Amplitude -->

<script type="text/javascript">
  (function(e,t){var n=e.amplitude||{_q:[],_iq:{}};var r=t.createElement("script")
  ;r.type="text/javascript"
  ;r.integrity="sha384-girahbTbYZ9tT03PWWj0mEVgyxtZoyDF9KVZdL+R53PP5wCY0PiVUKq0jeRlMx9M"
  ;r.crossOrigin="anonymous";r.async=true
  ;r.src="https://cdn.amplitude.com/libs/amplitude-7.2.1-min.gz.js"
  ;r.onload=function(){if(!e.amplitude.runQueuedFunctions){
  console.log("[Amplitude] Error: could not load SDK")}}
  ;var i=t.getElementsByTagName("script")[0];i.parentNode.insertBefore(r,i)
  ;function s(e,t){e.prototype[t]=function(){
  this._q.push([t].concat(Array.prototype.slice.call(arguments,0)));return this}}
  var o=function(){this._q=[];return this}
  ;var a=["add","append","clearAll","prepend","set","setOnce","unset"]
  ;for(var u=0;u<a.length;u++){s(o,a[u])}n.Identify=o;var c=function(){this._q=[]
  ;return this}
  ;var l=["setProductId","setQuantity","setPrice","setRevenueType","setEventProperties"]
  ;for(var p=0;p<l.length;p++){s(c,l[p])}n.Revenue=c
  ;var d=["init","logEvent","logRevenue","setUserId","setUserProperties","setOptOut","setVersionName","setDomain","setDeviceId", "enableTracking", "setGlobalUserProperties","identify","clearUserProperties","setGroup","logRevenueV2","regenerateDeviceId","groupIdentify","onInit","logEventWithTimestamp","logEventWithGroups","setSessionId","resetSessionId"]
  ;function v(e){function t(t){e[t]=function(){
  e._q.push([t].concat(Array.prototype.slice.call(arguments,0)))}}
  for(var n=0;n<d.length;n++){t(d[n])}}v(n);n.getInstance=function(e){
  e=(!e||e.length===0?"$default_instance":e).toLowerCase()
  ;if(!n._iq.hasOwnProperty(e)){n._iq[e]={_q:[]};v(n._iq[e])}return n._iq[e]}
  ;e.amplitude=n})(window,document);

  amplitude.getInstance().init("c4949b481687c1603b7e753b65ba249b");
  

  /*
  
  EVENTO
  amplitude.getInstance().logEvent('play song');

  PROPIEDADES DE EVENTO
  var eventProperties = {
    'color': 'blue',
    'age': 20,
    'key': 'value'
  };
  amplitude.getInstance().logEvent('EVENT_TYPE', eventProperties);

  USUARIO
  amplitude.getInstance().setUserId('USER_ID');

  PROPIEDADES DE USUARIO
  var identify = new amplitude.Identify().set('gender', 'female').set('age', 20);
  amplitude.getInstance().identify(identify);


  BORRAR USUARIO
  amplitude.getInstance().setUserId(null); // not string 'null'
  amplitude.getInstance().regenerateDeviceId();

  RETENCIÓN
  SIGN UP & PLAY VIDEO 80%

  CALIDAD VIDEO
  PLAY VIDEOS 20% OF PLAY VIDEO 80%

  */

</script>

    
<?php 

wp_head();
  
?>

<script>
  !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on","addSourceMiddleware","addIntegrationMiddleware","setAnonymousId","addDestinationMiddleware"];analytics.factory=function(e){return function(){var t=Array.prototype.slice.call(arguments);t.unshift(e);analytics.push(t);return analytics}};for(var e=0;e<analytics.methods.length;e++){var key=analytics.methods[e];analytics[key]=analytics.factory(key)}analytics.load=function(key,e){var t=document.createElement("script");t.type="text/javascript";t.async=!0;t.src="https://cdn.segment.com/analytics.js/v1/" + key + "/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);analytics._loadOptions=e};analytics.SNIPPET_VERSION="4.13.1";
  analytics.load("eIO4AqLAFLivaYNQmrUolQPaUjs5uQvg");
  analytics.page();
  }}();
</script>

  </head>
  <body>
      <?php
      /* Propieades para evento de amplitude */      
      $url_actual = home_url( add_query_arg( array(), $wp->request ) );
      $current_user = wp_get_current_user();
      $current_user_email = $current_user->user_email;
      $current_user_id = $current_user->ID;
      $current_user_es_regalo =  esc_attr(get_the_author_meta('user_es_regalo', $current_user_id ));

      if (wcs_user_has_subscription($user_id, '', 'active')) { 
				$user_plan = "active";
			} else if (wcs_user_has_subscription($user_id, '', 'pending-cancel')) { 
        $user_plan = "pending-cancel";
			} else if (wcs_user_has_subscription($user_id, '', 'cancelled')) { 
        $user_plan = "cancelled";
			} else if (wcs_user_has_subscription($user_id, '', 'on-hold')) {  
        $user_plan = "on-hold";
			} else if (wcs_user_has_subscription($user_id, '', 'expired')) {
        $user_plan = "expired";
			} else {
        $user_plan = "free";
      }
      ?>



    <script>
      var identify = new amplitude.Identify().set('plan', '<?php echo $user_plan; ?>').set('email', '<?php echo $current_user_email; ?>').set('es_regalo', '<?php echo $current_user_es_regalo; ?>');
      amplitude.getInstance().identify(identify);

      var eventProperties = {
        'URL': '<?php echo $url_actual; ?>',
        'USER_PLAN': '<?php echo $user_plan; ?>',
        'USER_EMAIL': '<?php echo $current_user_email; ?>',
        'USER_ES_REGALO': '<?php echo $current_user_es_regalo; ?>'
      };
      amplitude.getInstance().logEvent('PAGE LOADED', eventProperties);

      
      var current_email = '<?php echo $current_user_email;?>';
      if (current_email != '') {
         amplitude.getInstance().setUserId(current_email);
      }
        
    </script>

    <?php 
    if ($_GET['registro'] == 'ok') {
    ?>
    <script>
        amplitude.getInstance().logEvent('USER REGISTRO', eventProperties);
    </script>

    <?php
    } 

    if (strpos($url_actual, 'cuenta-gracias') !== false) { 
    ?>
    <script>
        amplitude.getInstance().logEvent('USER TARJETA', eventProperties);
      </script>
    <?php
    }
  
    ?>   



    <header class="my-header" id="js-header">
      <a id="js-logo-link" href="<?php echo home_url('/'); ?>">
        <div class="logo-wrapper">
          <img class="logo-img" id="js-logo-img" src="<?php bloginfo('template_url'); ?>/img/logo-theclassyoga-black.png" alt="Logo" />
          <div class="logo-text">
            <span class="logo-text-theclass">THECLASS</span>
            <span class="logo-text-move">yoga</span>
          </div>
        </div>
      </a>
      

      <button class="hamburger" id="js-hamburger">
        <div class="line" id="js-line1"></div>
        <div class="line line2" id="js-line2"></div>
        <div class="line line3" id="js-line3"></div>
      </button>

      <?php


if ($url_actual == home_url()) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'programas-yoga') !== false) {
  $programas_active = "navigation-link-active";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'clases-grabadas') !== false) {
  $programas_active = "";
  $clases_active = "navigation-link-active";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'clases-directo') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "navigation-link-active";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'clases-directo') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "navigation-link-active";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'quienes-somos') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "navigation-link-active";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'tipos-yoga-clase') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "navigation-link-active";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'profesores-yoga') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "navigation-link-active";
  $faq_active = "";
  $lee_active = "";
}
else if (strpos($url_actual, 'faq') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "navigation-link-active";
  $lee_active = "";
}

else if (strpos($url_actual, 'blog') !== false) {
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "navigation-link-active";
}

else{
  $programas_active = "";
  $clases_active = "";
  $directo_active = "";
  $quienes_active = "";
  $tipos_active = "";
  $profesores_active = "";
  $faq_active = "";
  $lee_active = "";
}
?> 


      <nav class="navigation-wrapper" id="js-navigation-wrapper">
        <ul class="navigation-list">
          <li class="navigation-item">
            <a class="navigation-link <?php echo $programas_active?>" href="<?php echo home_url()?>/programas-yoga">PROGRAMAS DE YOGA</a>
          </li>
          <li class="navigation-item">
            <a class="navigation-link <?php echo $clases_active?>" href="<?php echo home_url()?>/clases-grabadas">CLASES GRABADAS</a>
          </li>
          <li class="navigation-item">
            <a class="navigation-link <?php echo $directo_active?>" href="<?php echo home_url()?>/clases-directo">CLASES DIRECTO</a>
          </li>
        

          <li class="navigation-item">
            <a class="navigation-link <?php echo $lee_active?>" href="<?php echo home_url()?>/blog">BLOG</a>
          </li> 

          <li class="navigation-item">

          <?php 
                if (is_user_logged_in()) {  
                    if (wcs_user_has_subscription( '', '', '')) {
                      $linkButton = home_url()."/cuenta/";
                      $textButton = "Hola, ".get_name_or_display_name_without_id();
                    } 
                    else {
                      $linkButton = home_url()."/cuenta/";
                      $textButton = "Hola, ".get_name_or_display_name_without_id();
                    } 
                } else {
                    $linkButton = home_url()."/entrar/";
                    $textButton = "Login / Darme de alta";
                }
          ?>           

            <button
              class="btn btn-secondary btn-sign-in"
              onclick="window.location.href='<?php echo $linkButton ?>'">
              <?php echo $textButton ?>
          </button>
        </ul>
      </nav>


    </header>

    <div class="session-variable">
    
     <?php //echo '<p>' . print_r($_SESSION, TRUE) . '</p>'; ?>

    </div>

    
    <?php /*
           $text_shortocode_tomas = '<div class="my-notice"> <div class="my-coupon alert alert-success">[coupon_description] </div></div>';
           echo do_shortcode( '[coupon_is_applied code="*"]' . $text_shortocode_tomas. '[/coupon_is_applied]' ); */
    ?>


<script>
    /**
 *
 * ANIMACION LOGO
 *
 */

var logoLink = document.getElementById("js-logo-link");

var logoImage = document.getElementById("js-logo-img");


var logoAfterYoga = function () {
  logoImage.src = "<?php bloginfo('template_url'); ?>/img/logo-theclassyoga-ocre.png";
};


var logoBeforeYoga = function () {
  logoImage.src = "<?php bloginfo('template_url'); ?>/img/logo-theclassyoga-black.png";
};

logoLink.addEventListener("mouseover", logoAfterYoga);
logoLink.addEventListener("mouseout", logoBeforeYoga);

</script>