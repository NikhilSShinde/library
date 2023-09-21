$(document).ready(function(){
	
	fullSize();
	applyOrientation();
	
	$('#screens').owlCarousel({
		loop:true,
		margin:30,
		nav:false,
		dots:true,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:3
			},
			1000:{
				items:5			
			},
			1400:{
				items:7			
			}
		}
	});
	
	
	$(function () { 
	  $('[data-toggle="tooltip"]').tooltip({trigger: 'manual'}).tooltip('show');
	});  
	
	
	
	function moved() {
		alert('in');
		var owl = $(".owl-carousel").data('owlCarousel');
		if (owl.currentItem + 1 === owl.itemsAmount) {
			alert('THE END');
		}
	}
	
$(document).ready(function () {       
      if ($('html').hasClass('desktop')) {
        new WOW().init();
      }
});

$.scrollIt({
		upKey: 40,             // key code to navigate to the next section
		downKey: 40,           // key code to navigate to the previous section
		easing: 'ease-in-out',      // the easing function for animation
		scrollTime: 1500,       // how long (in ms) the animation takes
		activeClass: 'active', // class given to the active nav element
		onPageChange: null,    // function(pageIndex) that is called when page is changed
		topOffset:0           // offste (in px) for fixed top navigation
	});
});

$(window).load(function(){
	if (window.innerWidth > 1024 ) {
		var s = skrollr.init();
	}
});

$( window ).resize(function() {
	fullSize();
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

function fullSize() {
    var heights = window.innerHeight;
    jQuery(".fullHt").css('min-height', (heights + 0) + "px");
}

function applyOrientation() {
    if (window.innerHeight > window.innerWidth) {
        $("body").addClass("potrait");
        $("body").removeClass("landscape");
    } else {
        $("body").addClass("landscape");
        $("body").removeClass("potrait");
    }
}

var banner_Ht = window.innerHeight - $('header').innerHeight();	
	$(window).scroll(function(){
	  var sticky = $('body'),
		  scroll = $(window).scrollTop();
	
	  if (scroll >= 300) sticky.addClass('sticky-header');
	  else sticky.removeClass('sticky-header');
});

$('.nav-open-btn').click(function () {
	$('body').toggleClass('log-dash-open')
	$(this).toggleClass('cross-icon')
});

$(function(){
	$('a[title]').tooltip();
});

$('body').append('<div id="toTop" class="btn"><span class="fa fa-angle-up"></span></div>');
	$(window).scroll(function () {
		if ($(this).scrollTop() != 0) {
			$('#toTop').fadeIn();
		} else {
			$('#toTop').fadeOut();
		}
	}); 
$('#toTop').click(function(){
	$("html, body").animate({ scrollTop: 0 }, 1500);
	return false;
});

/*----------------------------------------------------*/
/*    Accordians FAQ
 /*----------------------------------------------------*/
$('.accordion').on('shown.bs.collapse', function (e) {
	$(e.target).parent().addClass('active_acc');
	$(e.target).prev().find('.switch').removeClass('fa-plus');
	$(e.target).prev().find('.switch').addClass('fa-minus');
});
$('.accordion').on('hidden.bs.collapse', function (e) {
	$(e.target).parent().removeClass('active_acc');
	$(e.target).prev().find('.switch').addClass('fa-plus');
	$(e.target).prev().find('.switch').removeClass('fa-minus');
}); 