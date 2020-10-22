$(document).ready(function(){
	$('.second-button').on('click', function () {
		$('.animated-icon2').toggleClass('open');
	});
	
	// ajax: show the form register if user is not log on
	$(window).scroll(function(){
		if(!$('div[id*=register]').length && $("#zone-commentaire").length)
		 	display_form_register();
	});
	// ajax scroll infini using InfiniteAjaxScroll v2.3.1
	scroll_infini();
});
/*
 ***************************** Ajax ***************************
*/

/**
 * ajax scroll infini
 */
const scroll_infini = () => {
	jQuery.ias({
		item:'.article',
		container:'#list-articles',
		next:'.next > a',
		pagination:'.pagination',
		delay: 1600		
	}).extension(new IASSpinnerExtension({
		//src:'../images/spinner3.gif' // optionally
		html: '<div class="ias-spinner mx-auto d-block"><img src="../images/spinner4.gif"/></div>'
	}));		
}


/**
 * 
 * @param {*} element 
 */
const replyComment 	= element => { 
	let data 	= {};	
	let url 	= location.href;
	let $elt_id = $(element).attr('id');
	let verif 	= $elt_id.substr(0,2);
	
	if(verif == 'ID'){
		url = $(element).data('url');
	}else if(verif == 'FM'){// 
		data 	= $(element).closest('form').serializeArray();
		url 	= $(element).attr('action');
	}else{
		return false;		
	} 	
	
	ajax_request(url, 'reply_comment', element, data, verif); 
	
	return false;
};
/**
 * 
 * @param {*} element 
 */
const displayReply	= element => {
	if(!$(element).data('url'))
		return false;
	let url 	= $(element).data('url');
	ajax_request(url, 'display_reply', element, {}); 
	return false;
};	
/**
 * 
 * @param {*} element 
 */
const commendArticle = element => {
	if(!$(element).closest('form').length && $(element).closest('form').attr('action') == '')
	  return false;
	const url 	= $(element).closest('form').attr('action');
	const data 	= $(element).closest('form').serializeArray(); 
	ajax_request(`${url}?ajax=comment_save`, 'comment_save', element, data);  
	return false;
};
/**
 * 
 * @param {*} url 
 * @param {*} action 
 * @param {*} element 
 * @param {*} data 
 * @param {*} verif 
 * @param {*} type 
 */
const ajax_request = (url, action, element, data = {},verif='',type='POST') =>	{
	$.ajax({
		url: url,
		type: type,
		data: data,
		success: function(response, status){
			if(response == "0")
				return false;
			
			if(action == 'reply_comment'){
				if(verif == '' || verif == undefined)
					return false;
				// cas : repondre à une commentaire
				// insert html response dans le dernier reponse d'une commentaire
				if($(element).closest('ul[id*=content_reply]').length){
					$(response).insertBefore($(element).closest('form')); 
					$(element).find("#reply_reply").val('');
					return false;
				}
				// insert html response dans le DOM: $('ul[id*=content_reply]')
				if($(element).next('ul[id*=content_reply]').length && verif == 'FM'){
					let _elt  = $(element).next('ul[id*=content_reply]');
					let _html = $($.parseHTML(response)).find('li.content-form-reply').html();
					$(_elt).append(_html);
					return false;
				}
				if(verif == 'ID' && $(element).next('ul[id*=content_reply]').length){
					return false;	
				} 
				// insert html response après le DOM: $(element) 
				$(response).insertAfter(element);		
			}else if (action == 'display_reply') {
				// cache l'element repondre
				if($(element).siblings('[id*=ID]').length){
					$(element).siblings('[id*=ID]').hide();
				}
				// si le conteneur reply existe déjà
				// alors on insert juste les reponses
				if($(element).siblings('[id*=content_reply]').length){
					let _elt  = $(element).siblings('ul[id*=content_reply]');
					let _html = $($.parseHTML(response)).children();
					$(_elt).prepend(_html);
				}else{ // si non, on insert le conteneur avec les reponses
					$(response).insertBefore(element);
				}
				// ajax pour afficher formulaire reponse
				display_only_form_reply(element,url); 
				$(element).hide();
				// ajax formulaire reponse
						
			}else if(action == 'comment_save'){
				let zone_commentaire  = $(element).closest('#zone-commentaire');
				$(zone_commentaire).find('#comment_comment').val('');
				$(zone_commentaire).find('#comment_list').remove();
				$(zone_commentaire).prepend(response);
			} 
		}
	});

	return false;
};
/**
 * 
 * @param {*} element 
 * @param {*} url 
 * @param {*} data 
 */
const display_only_form_reply = (element, url, data = {}) => {
	
	if($(element).siblings('ul[id*=content_reply]').find('.content-form-reply').length)
		return false;
	
	$.post({
		url:url,
		data: {'display_only_form':'display_only_form'},
		success: function(response,status){			
			let _elt  = $(element).siblings('ul[id*=content_reply]'); 
			let _html = $($.parseHTML(response)).children('.content-form-reply');
			$(_elt).append(_html);
		}
	});

	return false;
};
/**
 * 
 */
const display_form_register = () => {
	// ($(window).scrollTop() + 150) >= $("#zone-commentaire").offset().top;
	let verif = $(window).scrollTop() + $(window).height() + 50 >= $(document).height();
	if ( !$("form#comment").length && (verif) ){
		
		if($("div#register").length)
			return false;
		
		$.post({
			url: '/register',
			data: {'action':'display_form'},
			success: function(response,status){
				
				if(response == "0" || $('div[id*=register]').length)
					return false;

				$("#zone-commentaire").append($(response).filter('div[id*=register]'));
				
				if($("#zone-commentaire").length){
					$('div[id*=register]')
						.removeClass('row')
						.addClass('collection col s12 m12 l9 offset-l2')
						.find('.input-field').removeClass('l6 offset-l3')
						.addClass('l12');
				
				}
			}
		});
		return false;
	}
};