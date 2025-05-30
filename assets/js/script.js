$(function () {

  var delay=0, setTimeoutConst;
  $('.site-navigation:not(.onclick) .navbar-nav>li.dropdown, .site-navigation:not(.onclick) li.dropdown>ul>li.dropdown').hover(
  function(){
    var $this = $(this);
    setTimeoutConst = setTimeout(function(){
      $this.addClass('open').slideDown();
      $this.find('.dropdown-toggle').addClass('disabled');
    }, delay);

  },  function(){ 
    clearTimeout(setTimeoutConst );
    $(this).removeClass('open');
    $(this).find('.dropdown-toggle').removeClass('disabled');
  });

  $('.navbar-nav').slicknav({
      allowParentLinks: true,
      label: "",
      appendTo: "#masthead",
      closedSymbol: '<i class="fa fa-caret-down" aria-hidden="true"></i>',
      openedSymbol :'<i class="fa fa-caret-up" aria-hidden="true"></i>'
  });


  $('.slicknav_btn').click(function() {
    $(this).toggleClass('act');
      if($(this).hasClass('act')) {
        $('.slicknav_menu').addClass('act');
      }
      else {
        $('.slicknav_menu').removeClass('act');
      }
  });

 
  if ($(".counter-item [data-to]").length>0) {
      $(".counter-item [data-to]").each(function() {
          var stat_item = $(this),
          offset = stat_item.offset().top;
          if($(window).scrollTop() > (offset - 800) && !(stat_item.hasClass('counting'))) {
              stat_item.addClass('counting');
              stat_item.countTo();
          };
          $(window).scroll(function() {
              if($(window).scrollTop() > (offset - 800) && !(stat_item.hasClass('counting'))) {
                  stat_item.addClass('counting');
                  stat_item.countTo();
              }
          });
      });
  };

 
  var shuffleme = (function( $ ) {
    'use strict';
        var $grid = $('#grid'),
        $filterOptions = $('.portfolio-sorting li'),

    init = function() {

  
      setTimeout(function() {
        listen();
        setupFilters();
      }, 100);

      $("#grid .col-md-4").slice(0, 4).show();

      $("#loadMore").on('click', function(e) {
        e.preventDefault();
        $("#grid .col-md-4:hidden")
          .slice(0, 4)
          .fadeIn()
          .each(function() {
            $grid.shuffle('appended', $(this));
          });

        if($("#grid .col-md-4:hidden").length == 0){
          $("#loadMore").addClass("disabled").html("No more to Load");
        } 
      });


      $grid.shuffle({
        itemSelector: '[class*="col-"]', 
         group: Shuffle.ALL_ITEMS, 
      });
    },

      

    setupFilters = function() {
      var $btns = $filterOptions.children();
      $btns.on('click', function(e) {
        e.preventDefault();
        var $this = $(this),
            isActive = $this.hasClass( 'active' ),
            group = isActive ? 'all' : $this.data('group');


        if ( !isActive ) {
          $('.portfolio-sorting li a').removeClass('active');
        }

        $this.toggleClass('active');


        $grid.shuffle( 'shuffle', group );
      });

      $btns = null;
    },


    listen = function() {
      var debouncedLayout = $.throttle( 300, function() {
        $grid.shuffle('update');
      });

      $grid.find('img').each(function() {
        var proxyImage;

        if ( this.complete && this.naturalWidth !== undefined ) {
          return;
        }

        proxyImage = new Image();
        $( proxyImage ).on('load', function() {
          $(this).off('load');
          debouncedLayout();
        });

        proxyImage.src = this.src;
      });

      setTimeout(function() {
        debouncedLayout();
      }, 500);
    };      

    return {
      init: init
    };
  }( jQuery ));

  if($('#grid').length >0 ) { 
    shuffleme.init();
  };

}());

if($('#map').length >0 ) {
  var google;
  function init() {
      
      var myLatlng = new google.maps.LatLng(40.69847032728747, -73.9514422416687);

      var mapOptions = {
         
          zoom: 7,
          
          center: myLatlng,
          
          scrollwheel: false,
          styles: [{"featureType":"administrative.land_parcel","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape.man_made","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"simplified"},{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"hue":"#f49935"}]},{"featureType":"road.highway","elementType":"labels","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"hue":"#fad959"}]},{"featureType":"road.arterial","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"labels","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#a1cdfc"},{"saturation":30},{"lightness":49}]}]
      };

     
      var mapElement = document.getElementById('map');
     
      var map = new google.maps.Map(mapElement, mapOptions);
      var addresses = ['Brooklyn'];
      for (var x = 0; x < addresses.length; x++) {
          $.getJSON('http://maps.googleapis.com/maps/api/geocode/json?address='+addresses[x]+'&sensor=false', null, function (data) {
              var p = data.results[0].geometry.location
              var latlng = new google.maps.LatLng(p.lat, p.lng);
              new google.maps.Marker({
                  position: latlng,
                  map: map,
                  icon: 'images/loc.png'
              });

          });
      }
      
  }
  google.maps.event.addDomListener(window, 'load', init);
}




















       






      








