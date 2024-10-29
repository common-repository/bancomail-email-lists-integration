function setFilters(){
    //show preloader
	document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader');
	if(document.querySelector('.categoryFilter').value==''){
		document.querySelector('.categoryFilter').removeAttribute('name');
	}
	if(document.querySelector('.nationFilter').value==''){
		document.querySelector('.nationFilter').removeAttribute('name');
	}
	if(document.querySelector('.regionFilter').value==''){
		document.querySelector('.regionFilter').removeAttribute('name');
	}
	//remove any active page
	if (typeof(Storage) !== "undefined" && sessionStorage.currentPage) {
	  sessionStorage.removeItem('currentPage');
	}
	document.querySelector('.bm_ws_filters').submit();
}


function changeLanguage(lang){
	if(lang!=''){
	  document.querySelector('input[name="lang"]').value=lang;
	  setFilters();
	}
	else{
		 return false;
	}
}

function removeItem(item){ 
	 if(item!=''){
		 //show preloader
		 document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader');
		 
		 document.querySelector('input[name="bm_remove_item"]').value=item;
		 document.querySelector('form[name="bm_cart_list"]').submit();
	 }
	 else{
		 return false;
	 }
}

//pagination
function changePage(page, trigger){
	//Safari compatible
	var start_page=(parseInt(page-1)*20)+1;
	var stop_page=start_page + 20;
	var bm_item = document.body.querySelectorAll('.bm_elenchi_email tbody tr');
	var old_items=document.body.querySelectorAll('.bm_elenchi_email tbody tr.bm_active');
	for(var z=0; z < old_items.length; z++ ) {
		old_items[z].setAttribute('class','');
	}
	var index = 0;
	for( index=0; index < bm_item.length; index++ ) {
		 if(index >=start_page && index<=stop_page && bm_item[index]!=undefined){
			 bm_item[index].setAttribute('class','bm_active');
		 }
	}
	
	//set active page index
	if(document.body.querySelector('.bm_page_list li a.is_active')!=undefined){
	 document.body.querySelector('.bm_page_list li a.is_active').setAttribute('class','');
	 document.body.querySelectorAll('.bm_page_list li a')[(page-1)].setAttribute('class','is_active');
	}
	//scroll to top
	if(trigger==false){
	 document.querySelector('.emailListsContainerHeader').scrollIntoView(true);
	}
	
	//memorize current page
	if(typeof(Storage) !== "undefined") {
		sessionStorage.currentPage = page;
	}
}

//checkout

function buyNow(){
	var error=0;
	document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader hidden');
	if(document.getElementById('bm_ws_client_name').value==''){
		error++;
		document.getElementById('bm_ws_client_name').setAttribute('class','error');
	}
	if(document.getElementById('bm_ws_client_rsoc').value==''){
		error++;
		document.getElementById('bm_ws_client_rsoc').setAttribute('class','error');
	}
	if(document.getElementById('bm_ws_client_city').value==''){
		error++;
		document.getElementById('bm_ws_client_city').setAttribute('class','error');
	}
	
	if(document.getElementById('bm_ws_client_nation').value==''){
		error++;
		document.getElementById('bm_ws_client_nation').setAttribute('class','error');
	}
	
	if(document.getElementById('bm_ws_client_email').value=='' || document.getElementById('bm_ws_client_email').value.indexOf('@')==-1 || document.getElementById('bm_ws_client_email').value.indexOf('.')==-1){
		error++;
		document.getElementById('bm_ws_client_email').setAttribute('class','error');
	}
	
	if(document.getElementById('bm_ws_client_email').value!=document.getElementById('bm_ws_client_repeat_email').value){
		error++;
		document.getElementById('bm_ws_client_email').setAttribute('class','error');
		document.getElementById('bm_ws_client_repeat_email').setAttribute('class','error');
	}
	
	var payment_gateway=0;
	
	if(document.getElementById('bm_ws_client_bonifico')!=null && document.getElementById('bm_ws_client_bonifico').checked==true){
		payment_gateway++;
	}
	if(document.getElementById('bm_ws_client_paypal')!=null && document.getElementById('bm_ws_client_paypal').checked==true){
		payment_gateway++;
	}
	if(payment_gateway==0){
		error++;
		document.getElementById('select_payment_gateway').setAttribute('class','error');
	}
	
	if(typeof(document.getElementById('bm_ws_client_privacy_ok')) != 'undefined' && document.getElementById('bm_ws_client_privacy_ok') != null  && document.getElementById('bm_ws_client_privacy_ok').checked==false){
		error++;
		document.querySelector('label[for="bm_ws_client_privacy_ok"]').setAttribute('class','error');
	}

	if(typeof(document.getElementById('bm_ws_gdpr_ok')) != 'undefined' && document.getElementById('bm_ws_gdpr_ok') != null  && document.getElementById('bm_ws_gdpr_ok').checked==false){
		error++;
		document.querySelector('label[for="bm_ws_gdpr_ok"]').setAttribute('class','error');
	}
	
	if(document.querySelector('#ghostTrap input').value.length > 0){
		error++;
	}
	
	//submit the form
	if(error==0){
		document.querySelector('form[name="confirmOrder"]').removeAttribute('onsubmit');
		document.querySelector('form[name="confirmOrder"]').submit();
	}
	else{
		
		return false;
	}
}

function clearField(field){
  try{
	if(document.getElementById(field).hasAttribute('class')){
	   document.getElementById(field).removeAttribute('class');
	}
  }
  catch(error){
  	console.log(error+'---'+field);
  }
}


function showPrivacy(){
	var element=document.querySelector('.bm_ws_view_privacy');
	if(element.getAttribute('class')=='bm_ws_view_privacy show'){
	    document.querySelector('.bm_ws_view_privacy').setAttribute('class','bm_ws_view_privacy');
	}
	else{
		document.querySelector('.bm_ws_view_privacy').setAttribute('class','bm_ws_view_privacy show');
	}
	document.querySelector('label[for="bm_ws_client_privacy_ok"]').setAttribute('class','');
}

function showGdpr(){
	var element=document.querySelector('.bm_ws_view_gdpr');
	if(element.getAttribute('class')=='bm_ws_view_gdpr show'){
	    document.querySelector('.bm_ws_view_gdpr').setAttribute('class','bm_ws_view_gdpr');
	}
	else{
		document.querySelector('.bm_ws_view_gdpr').setAttribute('class','bm_ws_view_gdpr show');
	}
	document.querySelector('label[for="bm_ws_gdpr_ok"]').setAttribute('class','');
}


function showCart(){
	var element=document.querySelector('#bm_ws_main .cartContainer');
	if(element.className.indexOf('expand')==-1){
		element.setAttribute('class', 'bm_item cartContainer expand');
	}
	else{
		element.setAttribute('class', 'bm_item cartContainer');
	}
}

function showPreloader(){
	 document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader');
	 setTimeout(function(){document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader hidden');},15000);
}

