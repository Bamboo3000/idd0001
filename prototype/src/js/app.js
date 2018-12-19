'use strict';

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookieMessage()
{
	if(getCookie('cookieConfirm') !== 'yes') {
		document.getElementById('cookieMessage').classList.add('show');
	}
}

function cookieAgree()
{
	setCookie('cookieConfirm', 'yes', 365);
	document.getElementById('cookieMessage').classList.remove('show');
}

function hasClass(el, cls) 
{
	return el.className && new RegExp("(\\s|^)" + cls + "(\\s|$)").test(el.className);
}

function jobsLoadingIndicator()
{
	var loader = document.getElementsByClassName('job-listing__list-container')[0];
	if(hasClass(loader, 'loading')) {
		loader.classList.remove('loading');
	} else {
		loader.classList.add('loading');
	}
}

function checkboxLabel($el) 
{
	if($el.getElementsByTagName('input')[0].checked === true) {
		$el.classList.remove('active');
		$el.getElementsByTagName('input')[0].checked = false;
	} else {
		$el.classList.add('active');
		$el.getElementsByTagName('input')[0].checked = true;
	}
}

var freezeVp = function(e) {
    e.preventDefault();
};

function stopBodyScrolling (bool) {
    if (bool === true) {
        document.body.addEventListener("touchmove", freezeVp, false);
    } else {
        document.body.removeEventListener("touchmove", freezeVp, false);
    }
}

function menuScroll()
{
	var $nav = $('header.navigation');
	var $scroll = $(window).scrollTop();
	if($(window).width() > 767) {
		if($scroll >= 100) {
			if(!$nav.hasClass('scrolled')) {
				$nav.addClass('scrolled');
			}
		} else {
			if($nav.hasClass('scrolled')) {
				$nav.removeClass('scrolled');
			}
		}
	}
}

function getFileName($input, $el)
{
	$text = $input.value;
	document.getElementById($el).innerHTML = $text.split('\\')[2];
}

function unblurApplication() {
	$('form.application-form').removeClass('application-sent');
}
function appCaptchaCallback()
{
	$('form.application-form').find('input[type="submit"]').attr("disabled", false).removeClass('disabled');
}

/**
 * Parse url
 */
function urlParser($url)
{	
	var parser = document.createElement('a');
	parser.href = $url;

	var $result = parser.hostname;

	return $result;
}

/**
 * Get referrer address
 */
function getReferrer()
{
	var $url = document.referrer;

	/**
	 * exmaples for testing
	 */

	//var $url = 'https://www.thealphamen.nl/bossman-jelly-beard-oil-gold.html?source=googlebase&gclid=EAIaIQobChMI457A8fHz2wIVQ4jVCh1UUAqPEAMYAiAAEgJ7b_D_BwE';
	// var $url = 'https://www.googleadservices.com/pagead/aclk?sa=L&ai=DChcSEwj42t_l6oXcAhXvFdMKHZ9DBYAYABABGgJ3Yg&ohost=www.google.com&cid=CAASE-RoHW0b46McN-5DuPA1fgm8bws&sig=AOD64_1Qih4UGA3O5HwP4tWD-GT7ZmoccA&q=&ved=0ahUKEwi149vl6oXcAhVQ2KQKHewwA9oQ0QwINQ&adurl=';
	//var $url = '.com/?parameter=1&parameter=1&gclid=TeSter-123#bookmark&parameter=1&';

	if(getCookie('referrerURL')) {
		var $oldURL = getCookie('referrerURL');
	}

	if($url.length > 0 || $oldURL) {
		
		if(typeof($oldURL) != "undefined" && $oldURL !== null) {
			var $hostname = urlParser($oldURL);
			var $search = $oldURL.split("?")[1];
		} else {
			var $hostname = urlParser($url);
			var $search = $url.split("?")[1];
		}
		console.log($search);

		var $searchAdwords = false;
		var $host = $hostname;
		
		if(typeof($search) != "undefined" && $search !== null) {
			var $searchParts = $search.split("&");
			var $searchPhrase = 'gclid';
			var $searchPartsArr = [];

			for(var $i = 0; $i < $searchParts.length; $i++) {
				$searchPartsArr.push($searchParts[$i].split("="));
				//console.log($searchPartsArr[$i]);
			}
			console.log($searchPartsArr);
			for(var $i = 0; $i < $searchPartsArr.length; $i++) {
				//console.log($searchPartsArr[$i]);
				var $part = $searchPartsArr[$i];
				for(var $j = 0; $j < $part.length; $j++) {
					
					if( $part[$j].match($searchPhrase) !== null ) {
						console.log($part[$j]);
						$searchAdwords = true;
						console.log($searchAdwords);
						break;
					}
				}
				// if( $searchPartsArr[$i].match($searchPhrase) !== null ) {
				// 	$searchAdwords = true;
				// 	break;
				// }
			}
		}

		if($hostname !== window.location.hostname) {

			console.log('from:'+ $host +' yay!');

			if(!$oldURL) {
				setCookie('referrerURL', $url, '7');
			}
			
			if( $searchAdwords === true ) {
				console.log('selecting Adwords!');
				$('#jobFormModal').find('select[name="applicant-find"]').val('Google Adwords');
				console.log($('#jobFormModal').find('select[name="applicant-find"]').find(":selected"));
			} else {
				console.log('appending an option!' + $host);
				$('#jobFormModal').find('select[name="applicant-find"]').append($('<option>', {
					value: $host,
					text: $host
				}));
				$('#jobFormModal').find('select[name="applicant-find"]').val($host);
				console.log($('#jobFormModal').find('select[name="applicant-find"]').find(":selected"));
			}
			
		} else {
			$('#jobFormModal').find('select[name="applicant-find"]').val('Website SIR');
			console.log('from: here, yay!');
			console.log($('#jobFormModal').find('select[name="applicant-find"]').find(":selected"));
		}

	}

}

function onFormSubmit()
{
	$(document).on('submit', 'form.application-form, form.cv-form', function() {
		$(this).addClass('disabled').find('input[type="submit"]').attr("disabled", true).addClass('disabled');
		if($(this).hasClass('application-form')) {
			setCookie('jobid_'+$(this).find('input.job-id').val(), $(this).find('input.job-id').val(), 365);
		} else if($(this).hasClass('cv-form')) {
			setCookie('cvform', 'sent', 365);
		}
	});
}

function onFormLoad()
{
	var $jobid = $('form.application-form').find('input.job-id').val();
	var $cookie = getCookie('jobid_'+$jobid);

	if(getCookie('cvform')) {
		$('form.cv-form').addClass('cv-sent');
	}

	if($jobid !== 188 && $jobid == $cookie) {
		$('form.application-form').addClass('application-sent');
	}

}

function showForm()
{
	document.getElementById('jobFormModal').style.display = 'flex';
	setTimeout(function() {
		document.getElementById('jobFormModal').classList.add('active');
		document.getElementsByTagName('html')[0].classList.add('modal-open');
		document.getElementsByTagName('html')[0].classList.add('menu-active');
		document.getElementsByTagName('body')[0].classList.add('menu-active');
	}, 50);
	stopBodyScrolling(true);
}

function hideForm() 
{
	document.getElementById('jobFormModal').classList.remove('active');
	document.getElementsByTagName('html')[0].classList.remove('modal-open');
	document.getElementsByTagName('html')[0].classList.remove('menu-active');
	document.getElementsByTagName('body')[0].classList.remove('menu-active');
	setTimeout(function() {
		document.getElementById('jobFormModal').style.display = 'none';
	}, 500);
	stopBodyScrolling(false);
}

function lazyImages()
{
	$('.lazy').each(function() {
		var $src = $(this).data('src');
		$(this).attr('src', $src).removeAttr('data-src');	
	});
}

function clearForm(oForm) 
{
	console.log(oForm);
	var elements = oForm.elements; 
	oForm.reset();
	for(var i=0; i<elements.length; i++) {
		var field_type = elements[i].type.toLowerCase();
		switch(field_type) {

			case "text": 
			case "password": 
			case "textarea":
			case "hidden":   
				elements[i].value = ""; 
			break;
				
			case "radio":
			case "checkbox":
				if (elements[i].checked) {
					elements[i].checked = false; 
				}
			break;

			case "select-one":
			case "select-multi":
				elements[i].selectedIndex = -1;
			break;

			default: 
			break;
		}
	}
}

function subMenuMobile()
{
	$('.mobile-sub-menu').on('click', function(){
		$(this).toggleClass('active').next('ul.sub-menu').toggleClass('active');
	});
}

function menuMobile()
{
	$('.mobile-btn').on('click', function(){
		$(this).toggleClass('active').next('.navigation__menu').toggleClass('active');
	});
}

// Trigger close form modal window when click on overlay
if(document.getElementById('jobFormModal')) {
	document.getElementById('jobFormModal').addEventListener('click', function(e) {
		if (e.target === document.getElementById('jobFormModal')) {
			hideForm();
		}
	}, false);
}

$(document).ready(function() {
	menuMobile();
	subMenuMobile();
	getReferrer();
	onFormSubmit();
	onFormLoad();
});

$(window).on('load scroll', function() {
	menuScroll();
});

$(window).on('load', function() {
	lazyImages();
});