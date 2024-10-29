function addEvent(elm, evType, fn, useCapture){
	    if(elm==null){
		  return false;
	    }
        if (elm.addEventListener) {
          elm.addEventListener(evType, fn, useCapture); 
          return true; 
        } else if (elm.attachEvent) {
          var r = elm.attachEvent('on' + evType, fn); 
          return r; 
        } else {
          elm['on' + evType] = fn;
        }
}
function wsGeneralSettings(){
	var error=0;
	if(document.querySelector('input[name="bm_email"]').value==''){
		error++;
		document.querySelector('input[name="bm_email"]').setAttribute('class','bm_email error');
	}
	
	if(document.querySelector('input[name="bm_password"]').value==''){
		error++;
		document.querySelector('input[name="bm_password"]').setAttribute('class','bm_password error');
	}
	
	if(document.querySelector('input[name="bm_user_tax"]').value=='' || parseInt(document.querySelector('input[name="bm_user_tax"]').value) > 99){
		error++;
		document.querySelector('input[name="bm_user_tax"]').setAttribute('class','buser_tax error');
	}
	
	if(document.querySelector('input[name="bm_user_iva"]').value=='' || parseInt(document.querySelector('input[name="bm_user_iva"]').value) > 100){
		error++;
		document.querySelector('input[name="bm_user_iva"]').setAttribute('class','bm_user_iva error');
	}
	
	var activeLang=[];
	Array.prototype.filter.call(document.querySelectorAll('input.bm_active_lang'), function(inputElement){
		if(inputElement.checked==true){
			activeLang.push(inputElement.value);
		}
		
	});
	
	
	
	if(activeLang.length==0){
		error++;
		document.querySelector('.bm_active_lang_error').style.display='block';
	}
	
	var defaultLang='';
	Array.prototype.filter.call(document.querySelectorAll('input.bm_default_lang'), function(inputElement){
		if(inputElement.checked==true){
			defaultLang=inputElement.value;
		}
	});
	
	if(activeLang.length > 0 && activeLang.indexOf(defaultLang)==-1){
		error++;
		document.querySelector('.bm_default_lang_error').style.display='block';
	}
	
	if(error==0){
		document.getElementById('bm_ws_settings').removeAttribute('onsubmit');
		document.getElementById('bm_ws_settings').submit();
	}
}

function changeOrderStatus(id, status){
	document.querySelector('.bm_mask').setAttribute('class','bm_mask on');
	document.querySelector('#bm_ws_updateOrderStatus').setAttribute('class','show');
	document.querySelector('.order_status_cont h4 strong').innerHTML=id;
	document.querySelector('#order_id').value=id;
}

function updateOrder(){ 
 if(document.querySelector('select[name="bm_ws_order_status"]').value=!''){
	document.querySelector('form[name="bm_ws_updateOrderStatus"]').removeAttribute('onsubmit');
	alert(document.querySelector('select[name="bm_ws_order_status"]').value);
	document.querySelector('form[name="bm_ws_updateOrderStatus"]').submit();
	return true;
 }
 else{
	 return false;
 }
}

function hideMask(){
	document.querySelector('.bm_mask').setAttribute('class','bm_mask');
	document.querySelector('#bm_ws_updateOrderStatus').removeAttribute('class');
	document.querySelector('.order_status_cont h4 strong').innerHTML='';
}


function setCheckoutLanguage(lang){
	var getUrl=document.querySelector('#bm_ws_checkout_settings').getAttribute('action').split('&lang');
	var setUrl=getUrl[0]+'&lang='+lang;
	window.location=setUrl;
}

function setFrontEndLanguage(lang){
	var getUrl=document.querySelector('#bm_ws_frontend_settings').getAttribute('action').split('&lang');
	var setUrl=getUrl[0]+'&lang='+lang;
	window.location=setUrl+'#bm_ws_frontend_settings';
}

function setStorage(name, value){
	if (typeof(Storage) !== "undefined"){
	 localStorage.setItem(name,value);
	}
}

function openPaymentSettings(gateway,url, state){
	 if(window.location.href.indexOf(gateway)==-1){
		var state=state==false ? '&checked=0' : '&checked=1';
		url+=state;
		window.location=url;  
	 }
}

//show first time notice box
addEvent(window, 'load', function(){
	if (typeof(Storage) !== "undefined" && localStorage.getItem('notice-box-1')==undefined) {
		   document.querySelector('.first-time-notice').style.display='block';
		}
}, false);

