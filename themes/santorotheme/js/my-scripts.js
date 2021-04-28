/**
 *
 * ANIMACIÓN DEL HAMBURGER
 *
 */

var hamburger = document.getElementById("js-hamburger");
var line1 = document.getElementById("js-line1");
var line2 = document.getElementById("js-line2");
var line3 = document.getElementById("js-line3");
var navigation = document.getElementById("js-navigation-wrapper");

var toggleHamburger = () => {
  line1.classList.toggle("line1-rotate");
  line2.classList.toggle("line2-hidden");
  line3.classList.toggle("line3-rotate");
  navigation.classList.toggle("navigation-wrapper-visible");
};

hamburger.addEventListener("click", toggleHamburger);

/**
 *
 * ANIMACIÓN DE LA CABECERA
 *
 */

var doc = document.documentElement;
var w = window;

var prevScroll = w.scrollY || doc.scrollTop;
var curScroll;
var direction = 0;
var prevDirection = 0;

var header = document.getElementById("js-header");
var nav = document.getElementById("js-navigation-wrapper");

var checkScroll = function () {
  /* Para que al desplegarse el menu y hacer scroll como se mueve todo lo de detrás que no se esconda el header*/
  if (nav.classList.contains("navigation-wrapper-visible")) return;

  /*
   ** Find the direction of scroll
   ** 0 - initial, 1 - up, 2 - down
   */

  curScroll = w.scrollY || doc.scrollTop;

  if (curScroll < 1) header.classList.remove("header-shadow");
  else header.classList.add("header-shadow");

  if (curScroll > prevScroll) {
    //scrolled up
    direction = 2;
  } else if (curScroll < prevScroll) {
    //scrolled down
    direction = 1;
  }

  if (direction !== prevDirection) {
    toggleHeader(direction, curScroll);
  }

  prevScroll = curScroll;
};

var toggleHeader = function (direction, curScroll) {
  if (direction === 2 && curScroll > 80) {
    //replace with the height of your header in px

    header.classList.add("header-hide");
    prevDirection = direction;
  } else if (direction === 1) {
    header.classList.remove("header-hide");
    prevDirection = direction;
  }
};

window.addEventListener("scroll", checkScroll);

/**
 *
 * MOSTRAR SUBFILTROS
 *
 */

//funcion para cualquier clic en el documento.

/*
document.addEventListener(
  "click",
  function(e) {
    var clic = e.target;

    // CONTROL DE FILTROS
    const subfilterListTipo = document.getElementById("js-subfilter-list-tipo");
    const subfilterListDuracion = document.getElementById(
      "js-subfilter-list-duracion"
    );
    const subfilterListNivel = document.getElementById(
      "js-subfilter-list-nivel"
    );
    const subfilterListFoco = document.getElementById("js-subfilter-list-foco");
    const subfilterListIntensidad = document.getElementById(
      "js-subfilter-list-intensidad"
    );
    const subfilterListProfesor = document.getElementById(
      "js-subfilter-list-profesor"
    );

    // Si se hace clic en un filtro concreto muestro u oculto ese subfiltro y oculto el resto de subfiltros. Y si pincha fuera también cierro todo.
    if (clic.id == "js-filter-link-tipo") {
      e.preventDefault();

      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListTipo.classList.toggle("subfilter-list-active");
      //oculto el resto si hubiera alguno abierto.
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    } else if (clic.id == "js-filter-link-duracion") {
      e.preventDefault();

      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListDuracion.classList.toggle("subfilter-list-active");
      //oculto el resto si hubiera alguno abierto.
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    } else if (clic.id == "js-filter-link-nivel") {
      e.preventDefault();

      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListNivel.classList.toggle("subfilter-list-active");
      //oculto el resto si hubiera alguno abierto.
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    } else if (clic.id == "js-filter-link-foco") {
      e.preventDefault();

      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListFoco.classList.toggle("subfilter-list-active");
      //oculto el resto si hubiera alguno abierto.
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    } else if (clic.id == "js-filter-link-intensidad") {
      e.preventDefault();
      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListIntensidad.classList.toggle("subfilter-list-active");
      //oculto el resto si hubiera alguno abierto.
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    } else if (clic.id == "js-filter-link-profesor") {
      e.preventDefault();
      //con esto abro y cierro el subfiltro haciendo clic en el filtro
      subfilterListProfesor.classList.toggle("subfilter-list-active");
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
    } else if (!clic.classList.contains("subfilter-link")) {
      // TODO ESTO ES POR PINCHA EN CUALQUIER SITIO FUERA, CIERRO TODOS LOS SUBFILTROS
      subfilterListTipo.classList.remove("subfilter-list-active");
      subfilterListDuracion.classList.remove("subfilter-list-active");
      subfilterListNivel.classList.remove("subfilter-list-active");
      subfilterListFoco.classList.remove("subfilter-list-active");
      subfilterListIntensidad.classList.remove("subfilter-list-active");
      subfilterListProfesor.classList.remove("subfilter-list-active");
    }
  },
  false
);*/

function sumaVisualizaciones(claseId, content){ // function that initialize when the user left the the box, sending the object element.
  var postId = claseId; 
  var fieldName = 'visualizaciones'; // get the name of the element, because it will be the identity who we gonna change.
  var fieldValue = content; // get the value of the element.
  
  jQuery(document).ready(function($) {
     var elem = fieldName; //get the fieldName value
     var containValue = fieldValue; // get the contain value who will be updated 
     jQuery.ajax({ // We use jQuery instead $ sign, because Wordpress convention.
      url : '/wp-admin/admin-ajax.php', // This addres will redirect the query to the functions.php file, where we coded the function that we need.
      type : 'POST',
    
      data : {
          action : 'sumar_visualizaciones', 
          fieldname : elem,
          fieldvalue : containValue,
          postid : postId
      },
      beforeSend: function() {
             console.log('Updating Field');
      },
      success : function( response ) {
           console.log('Success');
      },
      complete: function(){
          console.log( "Field updated");
      }
     });
  });
 }


 var leerClaseDescription = () => {

   var leerMas = document.getElementById("js-clase-description"); 
   leerMas.classList.toggle("clase-description-visible");


 }
