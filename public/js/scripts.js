$(document).ready(function(){
	$(".dropdown-trigger").dropdown();
	$('select').formSelect();	 
});

/**
 *************************** Ajax ***************************
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
	
	ajax_request(url,data,'reply_comment',element,verif); 
	
	return false;
}

const displayReply	= element => {
	if(!$(element).data('url'))
		return false;
	let url 	= $(element).data('url');
	ajax_request(url,{},'display_reply',element); 
	return false;
}	
	
const ajax_request = (url,data={},action,element,verif='',type='POST') =>	{
	$.ajax({
		url: url,
		type: type,
		data: data,
		success: function(response, status){
			if(response == "0")
				return false
			
			if(action == 'reply_comment'){
				if(verif == '' || verif == undefined)
					return false;
				// cas : repondre à une commentaire
				// insert html response dans le dernier reponse d'une commentaire
				if($(element).closest('ul[id*=content_reply]').length){
					$(response).insertBefore($(element).closest('form'));
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
						
			}
		}
	});

	return false;
} 

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
	})
}