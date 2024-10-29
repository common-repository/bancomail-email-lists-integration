<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
 <?php
 require_once(plugin_dir_path( __FILE__ ).'functions.php');
 if(!isset($_COOKIE['wdsl_connection'])){
    $wdsl_connection = BMWS_check_wsdl_connection();
	if(intval($wdsl_connection)!=200){
		  //setcookie('wdsl_connection', 0, time() + 60, "/");//1 min
		  printf('<div class="notice notice-warning"><p>'.__('Attenzione! Non è possibile raggiungere il servizio di WebService. Riprova più tardi o contattaci per verificare lo stato del WebService:').'<a href="mailto:web@bancomail.com">web@bancomail.com</a></p></div>');
	  }
	  else{
		  setcookie('wdsl_connection', 200, time() + (86400 * 30), "/"); //30 days
	  }
	 }
	 if(BMWS_check_php_installed_extensions('openssl')==FALSE){
		 printf('<div class="notice notice-warning"><p>'.__('Attenzione! Per un corretto funzionamento del plugin, assicurati che l\'estensione php_openssl sia installata.').'</p></div>');
	 }
	 if(BMWS_check_php_installed_extensions('soap')==FALSE){
		 printf('<div class="notice notice-warning"><p>'.__('Attenzione! Per un corretto funzionamento del plugin, assicurati che l\'estensione php_soap sia installata.').'</p></div>');
	 }
 ?>
<div class="notice notice-info is-dismissible first-time-notice" onClick="setStorage('notice-box-1',1);"> 
	<p><strong><?php  echo __('Vendi ai tuoi client un Database profilato e garantito!', 'bancomail-email-lists-integration' );?></strong></p>
	<p><?php  echo __('Benvenuto! Per iniziare a vendere devi registrarti come Partner di Bancomail: il programma per coloro che operano nel settore del Web Marketing. Ha molti vantaggi ed è gratuito. Una volta accreditato, abilita le tue credenziali dalla pagina Integrazione della tua Area Personale.', 'bancomail-email-lists-integration');?></p>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text"><?php  echo __('Chiudi!', 'bancomail-email-lists-integration');?></span>
	</button>
</div>


<div class="wrap">

<?php  echo "<h1>" . __( 'Bancomail Email Lists Integration - Impostazioni', 'bancomail-email-lists-integration' )."</h1><hr>"; ?>
 <div class="nav-tab-wrapper">
  <h2 class="nav-tab-wrapper">
	<a data-tab-id="general" class="nav-tab <?php if(!isset($_GET['tab']) || sanitize_text_field($_GET['tab'])=='general_info'){?>nav-tab-active<?php }?>" href="?page=bancomail-email-lists-integration&amp;tab=general_info"><?php echo __('Configura il Web Service', 'bancomail-email-lists-integration');?></a>
	<a data-tab-id="gateway" class="nav-tab <?php if(sanitize_text_field($_GET['tab'])=='gateway'){?>nav-tab-active<?php }?>" href="?page=bancomail-email-lists-integration&amp;tab=gateway"><?php echo __('Pagamenti', 'bancomail-email-lists-integration');?></a>
	<a data-tab-id="frontend" class="nav-tab <?php if(sanitize_text_field($_GET['tab'])=='frontend'){?>nav-tab-active<?php }?>" href="?page=bancomail-email-lists-integration&tab=frontend"><?php echo __('Pagina del tuo negozio', 'bancomail-email-lists-integration');?></a>
	<a data-tab-id="checkout" class="nav-tab <?php if(sanitize_text_field($_GET['tab'])=='checkout'){?>nav-tab-active<?php }?>" href="?page=bancomail-email-lists-integration&amp;tab=checkout"><?php echo __('Checkout', 'bancomail-email-lists-integration');?></a>
   </h2>
 </div>
 
 
  <?php if(isset($_POST['save_user_options']) && $this->quey_status==TRUE){  ?>
   <div class="notice notice-success is-dismissible">
        <p><?php  echo __('I dati sono stati salvati con successo!', 'bancomail-email-lists-integration');?></p>
   </div>
 <?php } else if(isset($_POST['save_user_options']) && $this->quey_status==FALSE){?>
  <div class="notice notice-error is-dismissible">
        <p><?php _e('Si è verificato un errore!','bancomail-email-lists-integration');?></p>
        <?php if($this->error_msg!=''){?>
        <p><?php echo $this->error_msg;?></p>
        <?php } ?>
   </div>
 <?php } ?>
 
 
 <?php if(!isset($_GET['tab']) || sanitize_text_field($_GET['tab'])=='general_info'){?>
 <form method="post" action="?page=bancomail-email-lists-integration" id="bm_ws_settings" name="bm_ws_settings" onsubmit="javascript:return false;">
  <input type="hidden" name="save_user_options" value="set_general_info" />
  <div class="cont">
   <p><?php  echo __("Configura il Web Service di Bancomail inserendo le credenziali ricevute nell'email di conferma attivazione.", 'bancomail-email-lists-integration');?> <a href="https://www.bancomail.it/area-partner/integrazione" target="_blank"><?php  echo __("Non disponi delle credenziali?", "bancomail-email-lists-integration");?></a></p>
  </div>
  <table class="form-table bm-ws-form-table">
   <tr>
    <th><?php  echo __("Username","bancomail-email-lists-integration");?>:</th>
    <td><input name="bm_email"  class="bm_email" size="40" value="<?php echo $this->user_info['bm_email'];?>" placeholder="name@domain.com" type="text" required autocomplete="off" /></td>			
   </tr>
   <tr>
    <th><?php  echo __("Password","bancomail-email-lists-integration");?>:</th>
    <td>
     <input name="bm_password"  class="bm_password" size="40" value="<?php echo $this->user_info['bm_password'];?>" type="text" required autocomplete="off"/>
     <br/>
     <p class="howto"><span class="dashicons dashicons-warning"></span> <i><?php  echo __("Inserisci le credenziali di Test se stai provando il Web Service e non vuoi produrre ordini reali, ma ricordati di utilizzare le credenziali dell'ambiente di Produzione quando sei pronto per vendere!","bancomail-email-lists-integration");?></i></p>
     </td>
   </tr>
    <tr>
    <th><?php  echo __("Attiva lingua","bancomail-email-lists-integration");?>:</th>
    <td>
    <?php $languages=$this->user_info['bm_languages']!='' ? explode('|',$this->user_info['bm_languages']) : array(); ?>
	 <input value="it" name="bm_languages[]" class="bm_active_lang" id="it" type="checkbox" <?php if(in_array('it',$languages)){ echo 'checked="checked"';}?>> <label for="it"><?php  echo __("Italiano", "bancomail-email-lists-integration");?></label><br/>
	 <input value="en" name="bm_languages[]" class="bm_active_lang" id="en" type="checkbox" <?php if(in_array('en',$languages)){ echo 'checked="checked"';}?>> <label for="en"><?php  echo __("Inglese", "bancomail-email-lists-integration");?></label><br/>
	 <input value="fr" name="bm_languages[]" class="bm_active_lang" id="fr" type="checkbox" <?php if(in_array('fr',$languages)){ echo 'checked="checked"';}?>> <label for="fr"><?php  echo __("Francese", "bancomail-email-lists-integration");?></label><br/>
	 <input value="es" name="bm_languages[]" class="bm_active_lang" id="es" type="checkbox" <?php if(in_array('es',$languages)){ echo 'checked="checked"';}?>> <label for="es"><?php  echo __("Spagnolo", "bancomail-email-lists-integration");?></label><br/>
	 <input value="de" name="bm_languages[]" class="bm_active_lang" id="de" type="checkbox" <?php if(in_array('de',$languages)){ echo 'checked="checked"';}?>> <label for="de"><?php  echo __("Tedesco", "bancomail-email-lists-integration");?></label>
     <p class="bm_active_lang_error"><br/><?php  echo __("Seleziona almeno una lingua.","bancomail-email-lists-integration");?></p>
    </td>
   </tr>
   <tr>
    <th><?php  echo __("Lingua default","bancomail-email-lists-integration");?>:</th>
    <td>
	 <input value="it" name="bm_default_language" class="bm_default_lang" id="default_it" type="radio" <?php if($this->user_info['bm_default_language']=='it'){ echo 'checked="checked"';}?>> <label for="default_it"><?php  echo __("Italiano", "bancomail-email-lists-integration");?></label><br/>
	 <input value="en" name="bm_default_language" class="bm_default_lang" id="default_en" type="radio" <?php if($this->user_info['bm_default_language']=='en'){ echo 'checked="checked"';}?>> <label for="default_en"><?php  echo __("Inglese", "bancomail-email-lists-integration");?></label><br/>
	 <input value="fr" name="bm_default_language" class="bm_default_lang" id="default_fr" type="radio" <?php if($this->user_info['bm_default_language']=='fr'){ echo 'checked="checked"';}?>> <label for="default_fr"><?php  echo __("Francese", "bancomail-email-lists-integration");?></label><br/>
	 <input value="es" name="bm_default_language" class="bm_default_lang" id="default_es" type="radio" <?php if($this->user_info['bm_default_language']=='es'){ echo 'checked="checked"';}?>> <label for="default_es"><?php  echo __("Spagnolo", "bancomail-email-lists-integration");?></label><br/>
	 <input value="de" name="bm_default_language" class="bm_default_lang" id="default_de" type="radio" <?php if($this->user_info['bm_default_language']=='de'){ echo 'checked="checked"';}?>> <label for="default_de"><?php  echo __("Tedesco", "bancomail-email-lists-integration");?></label>
	 <p class="bm_default_lang_error"><br/><?php  echo __("La lingua default deve essere una tra le lingue attive.","bancomail-email-lists-integration");?></p>
    </td>
   </tr>
   <tr>
    <th><?php  echo __("Ricarico sulla vendita","bancomail-email-lists-integration");?>:</th>
    <td>
     <input title="<?php  echo __("valore massimo consentito: 99","bancomail-email-lists-integration");?>" name="bm_user_tax" class="bm_user_tax" size="5" maxlength="3" value="<?php echo esc_html($this->user_info['bm_user_tax']);?>" type="number" required autocomplete="off" />%<br/>
     <p class="howto">
      <span class="dashicons dashicons-warning"></span> <strong><?php echo __("Il tuo guadagno","bancomail-email-lists-integration");?></strong>:
      <?php echo __("l’incremento sulle vendite si applica ai prezzi forniti di Bancomail e non viene mostrato al cliente. Al tuo costo invece vengono sempre applicati i tuoi sconti Partner.","bancomail-email-lists-integration");?>
     </p>
    </td>			
   </tr>
   <tr>
    <th><?php  echo __("IVA","bancomail-email-lists-integration");?>:</th>
    <td>
     <input title="<?php  echo __("valore massimo consentito: 100","bancomail-email-lists-integration");?>" name="bm_user_iva" class="bm_user_iva" size="5" placeholder="22" maxlength="2" value="<?php if($this->user_info['bm_user_iva']!=''){ echo esc_html($this->user_info['bm_user_iva']);}?>" type="text" required />%
    </td>			
   </tr>			
  </table>
  <div class="submit">
	<p class="submit">
	 <input name="submit" id="submit-general" class="button button-primary" value="<?php  echo __("Salva","bancomail-email-lists-integration");?>" type="submit" onclick="wsGeneralSettings();">
    </p>									
  </div>
 </form>


 <?php } ?>
 
 
 <?php if(isset($_GET['tab']) && sanitize_text_field($_GET['tab'])=='frontend'){;?> 
 
  <?php if(isset($_POST['submit_frontend_setting']) && $this->quey_status==TRUE){  ?>
   <div class="notice notice-success is-dismissible">
        <p><?php  echo __('I dati sono stati salvati con successo!', 'bancomail-email-lists-integration');?></p>
   </div>
 <?php } else if(isset($_POST['submit_frontend_setting']) && $this->quey_status==FALSE){?>
  <div class="notice notice-error is-dismissible">
        <p><?php _e('Si è verificato un errore!','bancomail-email-lists-integration');?></p>
   </div>
 <?php } ?>
 
 <div class="cont">
    <h3><?php echo __("Personalizza il tuo negozio","bancomail-email-lists-integration");?></h3>
    <p>
     <?php echo __("Per ogni lingua, puoi impostare un header e un footer personalizzato. Le lingue mostrate nell’editor sono quelle abilitate nella sezione","bancomail-email-lists-integration");?>
     <a href="?page=bancomail-email-lists-integration&tab=general_info"><?php echo __(" Configura il Web Service.","bancomail-email-lists-integration");?></a> <?php echo __("Puoi inserire codice html.","bancomail-email-lists-integration");?>
    </p>
 </div>   
 <?php
 $def=get_site_option('bm_default_language')!='' ? get_site_option('bm_default_language') : 'it';
 $default_language=(isset($_GET['lang']) && strlen($_GET['lang'])==2) ? sanitize_text_field($_GET['lang']) : $def;
 $frontend_info='bm_frontend_'.$default_language;
 $user_frontend_html=json_decode(get_site_option($frontend_info,false,true));
 ?>
 <form method="post" action="?page=bancomail-email-lists-integration&tab=frontend&lang=<?php echo urlencode($default_language);?>" id="bm_ws_frontend_settings" name="bm_ws_frontend_settings">
   <table id="wpsc_checkout_list" class="widefat page fixed ui-sortable">
	<thead>
		<tr>
		 <th scope="col" class="manage-column column-name" style="width:150px;"><strong><?php echo __("Titolo","bancomail-email-lists-integration");?>:</strong></th>
		 <th scope="col" class="manage-column column-name"><strong><?php echo __("Valore","bancomail-email-lists-integration");?>:</strong></th>		
		</tr>
	</thead>
	<tbody id="wpsc_checkout_list_body">
	<?php 
    if(get_site_option('bm_password')!='' && get_site_option('bm_email')!=''){
     $page = get_page_by_title( 'database' );
     $the_excerpt = $page->post_excerpt;
     $page_data = get_page( $page );
     $title = $page_data->post_title;
    ?>
   <tr>
    <th><?php echo __(" L’indirizzo del tuo negozio","bancomail-email-lists-integration");?>:</th>
    <td>
     <a href="<?php echo str_replace('__trashed','',esc_url( get_permalink( get_page_by_title('database')))); ?>" target="_blank"><?php echo str_replace('__trashed','',esc_url( get_permalink( get_page_by_title('database')))); ?></a>
    </td>			
   </tr>
   <?php } ?>	
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Seleziona la lingua","bancomail-email-lists-integration");?>:</td>
		<td>
		<?php if(get_site_option('bm_languages')==''){?>
		 <p><?php echo __("Per favore imposta prima le","bancomail-email-lists-integration");?> <a href="?page=bancomail-email-lists-integration"><?php echo __("lingue accettate","bancomail-email-lists-integration");?></a>.</p>
		<?php }?>
		
		 <select name="bm_ws_set_frontend_lang" id="bm_ws_set_frontend_lang" onchange="setFrontEndLanguage(this.value);">
		 <?php
		  $languages=explode('|',get_site_option('bm_languages'));
		  $ln=array('it'=>'Italiano','en'=>'Inglese','fr'=>'Francese','de'=>'Tedesco','es'=>'Spagnolo');
		  foreach($languages as $key => $value){
		    
		      $html.='<option value="'.$value.'"';
		      if($value==$default_language){
		           $html.=' selected="selected"';
		      }
	          $html.='>'.__($ln[$value],"bancomail-email-lists-integration").'</option>';
		  }
		  echo $html;
		 ?>
		 </select>
		</td>
	 </tr>
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Pagina - Header","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_frontend_header_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sopra allo shop.","bancomail-email-lists-integration");?>"><?php echo $user_frontend_html->bm_frontend_header?></textarea><br>
		 <p class="description"><?php echo __("Esempio:","bm-web-service").' '.htmlentities('<h1>'.__('Crea il tuo database','bancomail-email-lists-integration').'</h1><p>').htmlentities('<p>'.__('Scegli la categoria e la locazione del tuo Target e trova i tuoi prossimi clienti!',"bancomail-email-lists-integration").'</p>');?></p>
		</td>
	 </tr>
	  <tr class="new-field checkout_form_field">
		<td><?php echo __("Pagina - Footer","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_frontend_footer_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sotto allo shop.","bancomail-email-lists-integration");?>"><?php echo $user_frontend_html->bm_frontend_footer?></textarea><br>
		 <p class="description"><?php echo __("Esempio:","bancomail-email-lists-integration").' '.htmlentities('<p><i>'.__('Tutti i database sono coperti dalla  garanzia di validità dei dati e contengono anagrafiche complete, sempre comprensive di indirizzo email.',"bancomail-email-lists-integration").'</i></p>');?></p>
		</td>
	 </tr>
	 	
	 <tr class="new-field checkout_form_field">
	  <td colspan="2">
	   <?php if(get_site_option('bm_languages')!=''){?>
	    <button name="submit_frontend_setting" class="button button-primary" value="frontend"><?php echo __("Salva","bancomail-email-lists-integration");?></button>
	   <?php } ?> 
	  </td>
 	 </tr>	
  </tbody>
 </table> 	 
 </form>
   
 <?php } ?>
 
 <?php 
  if(sanitize_text_field($_GET['tab'])=='gateway'){ $open_tab=sanitize_text_field($_GET['payment_gateway_id']) ? '&payment_gateway_id='.sanitize_text_field($_GET['payment_gateway_id']) : ''; 
  if(isset($_GET['lang']) && strlen($_GET['lang'])==2){$open_tab.='&lang='.sanitize_text_field($_GET['lang']).'#gateway_list_item_manual'; }
  ?>
 
 
 
 
 <form method="post" action="?page=bancomail-email-lists-integration&tab=gateway<?php echo $open_tab;?>" id="bm_ws_settings" name="bm_payment_settings" method="post">
  <input type="hidden" name="save_user_options" value="set_payment_info" />
  <div class="cont">
   <h3><?php echo __("Impostazioni metodo di pagamento","bancomail-email-lists-integration");?></h3>
   <p><?php echo __("Seleziona i metodi di pagamento che vuoi attivare per i tuoi clienti","bancomail-email-lists-integration");?>.</p>
   <p class="howto">
    <span class="dashicons dashicons-warning"></span>
    <?php echo __("Nota che questo non identifica il tuo metodo di pagamento con Bancomail. Per completare l’ordine e ricevere la fornitura, potrai scegliere il tuo medoto preferito direttamente dai tuoi Ordini.","bancomail-email-lists-integration");?>
   </p>
  </div>
  <table class="wp-list-table widefat plugins">
	<thead>
		<tr>
			<th scope="col" id="wpsc-gateway-active" class="manage-column"><strong><?php echo __("Attiva","bancomail-email-lists-integration");?></strong></th>
			<th scope="col" id="wpsc-gateway-name" class="manage-column column-name"><strong><?php echo __("Metodo pagamento","bancomail-email-lists-integration");?></strong></th>
			<th scope="col" id="wpsc-gateway-display-name" class="manage-column column-description"><!-- <strong>Display Name</strong>--></th>
		</tr>
	</thead>

	<tbody>

	<tr class="wpsc-select-gateway inactive" data-gateway-id="bm_merchant_paypal_standard" id="gateway_list_item_bm_merchant_paypal_standard">
		<th scope="row" class="check-column">
			<label class="screen-reader-text" for="bm_merchant_paypal_standard_id">Select PayPal Payments Standard</label>
		   
			<input onChange="openPaymentSettings('bm_merchant_paypal_standard','?page=bancomail-email-lists-integration&amp;tab=gateway&amp;payment_gateway_id=bm_merchant_paypal_standard', this.checked);" name="bm_paypal_standard_option" value="active"  type="checkbox" <?php if(($this->user_info['bm_paypal_standard']->bm_paypal_standard_option==1 && (!isset($_GET['checked']) || (isset($_GET['checked']) && $_GET['payment_gateway_id']!='bm_merchant_paypal_standard'))) || (isset($_GET['checked']) && $_GET['checked']=='1' && $_GET['payment_gateway_id']=='bm_merchant_paypal_standard')){?>checked="checked"<?php } ?>>
		</th>
		<td class="plugin-title">
			<label for="bm_merchant_paypal_standard_id"><strong>PayPal Payments Standard</strong></label>
			<div class="row-actions-visible">
				<span class="edit">
					<a class="edit-payment-module" title="Edit this Payment Gateway's Settings" href="?page=bancomail-email-lists-integration&amp;tab=gateway&amp;payment_gateway_id=bm_merchant_paypal_standard"><?php echo __("Impostazioni","bancomail-email-lists-integration");?></a>
					<img src="/wp-admin/images/spinner.gif" class="ajax-feedback" title="<?php echo __("Impostazioni","bancomail-email-lists-integration");?>" alt="">
				</span>
			</div>
		</td>
		<td class="plugin-description"></td>
	</tr>
	
	<tr id="bm_gateway_settings_bm_merchant_paypal_standard" data-gateway-id="bm_merchant_paypal_standard" class="gateway_settings inactive" <?php if(!isset($_GET['payment_gateway_id']) || sanitize_text_field($_GET['payment_gateway_id'])!='bm_merchant_paypal_standard'){?>style="display: none;"<?php } ?>>
	 <td colspan="3" id="bm_gateway_settings_bm_merchant_paypal_standard_container">
	  <div id="gateway_settings_bm_merchant_paypal_standard_form" class="gateway_settings_form">
		<table class="form-table">
		 <tbody>
		 <!--  
			<tr>
				<td width="150">*Display Name</td>
				<td>
					<input name="bm_paypal_standard_user_defined_name" placeholder="PayPal Payments Standard" value="<?php echo $this->user_info['bm_paypal_standard']->bm_paypal_standard_user_defined_name;?>" type="text">
					<p class="description">The text that people see when making a purchase.</p>
				</td>
			</tr>
		-->
	<tr>
		<td>*<?php echo __("Nome utente","bancomail-email-lists-integration");?>:</td>
		<td>
		    <input name="bm_paypal_standard_user_defined_name"  value="<?php echo 'PayPal o carta di credito';//$this->user_info['bm_paypal_standard']->bm_paypal_standard_user_defined_name;?>" type="hidden">
			<input size="40" value="<?php echo esc_html($this->user_info['bm_paypal_standard']->bm_paypal_standard_username); ?>" name="bm_paypal_standard_username" type="email">
			<p class="description">
				<?php echo __("L’indirizzo email associato al tuo account PayPal","bancomail-email-lists-integration");?>
			</p>
		</td>
	</tr>

	<tr>
		<td>*<?php echo __("Tipo di Account","bancomail-email-lists-integration");?>:</td>
		<td>
			<select name="bm_paypal_standard_url">
			  <option value=""><?php echo __("Seleziona","bancomail-email-lists-integration");?></option>
              <option value="https://www.paypal.com/cgi-bin/webscr" <?php if($this->user_info['bm_paypal_standard']->bm_paypal_standard_url=='https://www.paypal.com/cgi-bin/webscr'){?>selected="selected"<?php } ?>>Live Account</option>
              <option value="https://www.sandbox.paypal.com/cgi-bin/webscr" <?php if($this->user_info['bm_paypal_standard']->bm_paypal_standard_url=='https://www.sandbox.paypal.com/cgi-bin/webscr'){?>selected="selected"<?php } ?>>Sandbox Account</option>
			</select>
			<p class="description">
				<?php echo __("Se vuoi testare il funzionamento del Web Service puoi utilizzare un account Sandbox. Ricordati di selezionare Live mode quando il tuo negozio deve ricevere pagamenti reali.","bancomail-email-lists-integration");?>
			</p>
		</td>
	</tr>

				<tr>
				 <td colspan="2">
					<p class="submit inline-edit-save">
					 <!-- <a class="button edit-payment-module-cancel" title="Cancel editing this Payment Gateway's settings">Cancel</a>-->
					 <button name="submit_payments_setting"  class="button button-primary" value="paypal_standard"><?php echo __("Salva","bancomail-email-lists-integration");?></button>
					</p>
				 </td>
				</tr>
			  </tbody>
			</table>
		</div>
	 </td>
	</tr>
	
	<tr class="wpsc-select-gateway inactive" data-gateway-id="manual" id="gateway_list_item_manual">
		<th scope="row" class="check-column">
			<label class="screen-reader-text" for="manual_id">Select Manual Payment Gateway</label>
			<input onChange="openPaymentSettings('manual','?page=bancomail-email-lists-integration&amp;tab=gateway&amp;payment_gateway_id=manual',this.checked);" name="bm_bonifico_option" value="bonifico" id="bm_bonifico_option" type="checkbox" <?php if((intval($this->user_info['bm_bonifico']->bm_bonifico_option)==1 && (!isset($_GET['checked']) || (isset($_GET['checked']) && sanitize_text_field($_GET['payment_gateway_id'])!='manual') )) || (isset($_GET['checked']) && intval($_GET['checked'])==1 && sanitize_text_field($_GET['payment_gateway_id'])=='manual')){ ?>checked="checked"<?php }?>/>
		</th>
		<td class="plugin-title">
			<label for="manual_id"><strong><?php echo __("Bonifico bancario","bancomail-email-lists-integration");?></strong></label>
			<div class="row-actions-visible">
				<span class="edit">
					<a class="edit-payment-module" title="<?php echo __("Impostazioni","bancomail-email-lists-integration");?>" href="?page=bancomail-email-lists-integration&amp;tab=gateway&amp;payment_gateway_id=manual"><?php echo __("Impostazioni","bancomail-email-lists-integration");?></a>
				</span>
			</div>
		</td>
		<td class="plugin-description"></td>
	</tr>
	<tr id="bm_gateway_settings_manual" data-gateway-id="manual" class="gateway_settings inactive" <?php if(!isset($_GET['payment_gateway_id']) || sanitize_text_field($_GET['payment_gateway_id'])!='manual'){?>style="display: none;"<?php } ?>>
	 <td colspan="3" id="bm_gateway_settings_manual_container">
	  <div id="gateway_settings_manual_form" class="gateway_settings_form">
			<table class="form-table">
				<tbody>
	<!-- 		
	<tr>
		<td style="border-top: none;">Nome metodo di pagamento:</td>
		<td style="border-top: none;">
		 <input name="bm_bonifico_user_defined_name" value="<?php echo $this->user_info['bm_bonifico']->bm_bonifico_user_defined_name;?>" placeholder="Bonifico bancario" type="text"><br/>
		 <span class="small description">The text that people see when making a purchase.</span>
		</td>
	</tr>
	-->
	  <?php if(get_site_option('bm_languages')!=''){?>
	   <tr>
	    <td>
	     <label for=""><?php echo __("Seleziona la lingua","bancomail-email-lists-integration");?>:</label>&nbsp;
		 <select name="bm_bonifico_instructions_language" id="bm_bonifico_instructions_language" onchange="javascript:window.location='?page=bancomail-email-lists-integration&tab=gateway&payment_gateway_id=manual&lang='+this.value;">
		 <?php
		  $languages=explode('|',get_site_option('bm_languages'));
		  $default_language=isset($_GET['lang']) && strlen($_GET['lang'])==2 ? sanitize_text_field($_GET['lang']) : get_site_option('bm_default_language');
		  $ln=array('it'=>'Italiano','en'=>'Inglese','fr'=>'Francese','de'=>'Tedesco','es'=>'Spagnolo');
		  foreach($languages as $key => $value){
		    
		      $html.='<option value="'.$value.'"';
		      if($value==$default_language){
		           $html.=' selected="selected"';
		      }
	          $html.='>'.__($ln[$value],"bancomail-email-lists-integration").'</option>';
		  }
		  echo $html;
		 ?>
		 </select>
		</td>
	   </tr>
	   <?php } ?>
			<tr>
			 <td colspan="2">
			  <input name="bm_bonifico_user_defined_name" value="<?php echo 'Bonifico bancario';//$this->user_info['bm_bonifico']->bm_bonifico_user_defined_name;?>" type="hidden">
				<p>
					<label for="wpsc-manual-gateway-setup"><?php echo __("Istruzioni per il pagamento","bancomail-email-lists-integration");?></label><br>
					<textarea id="wpsc-manual-gateway-setup" cols="100" rows="5" name="bm_bonifico_instructions" placeholder="<?php echo __("Beneficiario, Coordinate bancarie, ecc","bancomail-email-lists-integration");?>"><?php echo $this->user_info['bm_bonifico']->bm_bonifico_instructions->$default_language;?></textarea><br>
					<p class="description"><?php echo __("Inserisci le informazioni per il pagamento: saranno mostrate al tuo cliente nella pagina di conferma dell’ordine e nell’email.","bancomail-email-lists-integration");?></p>
					<p class="description"><?php echo __("Ad esempio questo è lo spazio in cui puoi inserire i riferimenti bancari o l’indirizzo della tua sede per i pagamenti manuali.","bancomail-email-lists-integration");?></p>
			</td>
		</tr>
		<tr>
		 <td colspan="2">
		  <p class="submit inline-edit-save">
		   <!-- <a class="button edit-payment-module-cancel" title="Cancel editing this Payment Gateway's settings">Cancel</a>-->
		    <button name="submit_payments_setting" class="button button-primary" value="bonifico"><?php echo __("Salva","bancomail-email-lists-integration");?></button>
		  </p>
		 </td>
		</tr>
		</tbody>
	   </table>
	  </div>
	 </td>
	</tr>
	
   </tbody>
   <tfoot>
		<tr>
			<th scope="col" id="wpsc-gateway-active" class="manage-column">&nbsp;</th>
			<th scope="col" id="wpsc-gateway-name" class="manage-column column-name">&nbsp;</th>
			<th scope="col" id="wpsc-gateway-display-name" class="manage-column column-description">&nbsp;</th>
		</tr>
	</tfoot>
  </table>
 </form>
 <?php } ?>
 
 <?php 
  if(sanitize_text_field($_GET['tab'])=='checkout'){ 
      $available_languages=explode('|',get_site_option('bm_languages',false,true));
      $default_language=isset($_GET['lang']) ? urlencode(sanitize_text_field($_GET['lang'])) : get_site_option('bm_default_language',false,true);
 ?>
 
   <?php if(isset($_POST['submit_checkout_setting']) && $this->quey_status==TRUE){  ?>
   <div class="notice notice-success is-dismissible">
        <p><?php  echo __('I dati sono stati salvati con successo!', 'bancomail-email-lists-integration');?></p>
   </div>
  <?php } else if(isset($_POST['submit_checkout_setting']) && $this->quey_status==FALSE){?>
  <div class="notice notice-error is-dismissible">
        <p><?php _e('Si è verificato un errore!','bancomail-email-lists-integration');?></p>
   </div>
  <?php } ?>
 
 
  <div class="cont">
   <h3><?php echo __("Impostazioni Checkout","bancomail-email-lists-integration");?></h3>
   <p><?php echo __("Qui puoi modificare l’esperienza di checkout modificando la pagina in cui il tuo cliente compila i suoi dati e l’email di conferma acquisto. I dati compilati dal tuo utente e quelli relativi al suo acquisto, saranno visibili nella tua","bancomail-email-lists-integration");?> <a href="?page=bancomail-email-lists-integration-ordini"><?php echo __('pagina "ordini"','bancomail-email-lists-integration');?></a></p>
   <p><?php echo __("Per ogni lingua, puoi personalizzare l’esperienza d’acquisto. Le lingue mostrate nell’editor sono quelle abilitate nella sezione Configura il Web Service. Puoi inserire codice html.","bancomail-email-lists-integration");?></p>
  </div>
  <form action="?page=bancomail-email-lists-integration&tab=checkout&lang=<?php echo urlencode($default_language);?>" id="bm_ws_checkout_settings"  method="post">
   <table id="wpsc_checkout_list" class="widefat page fixed ui-sortable" cellspacing="0" width="100%">
	<thead>
		<tr>
		 <th scope="col" class="manage-column column-name" style="width:150px;">&nbsp;</th>
		 <th scope="col" class="manage-column column-name"><strong>&nbsp;</strong></th>		
		</tr>
	</thead>
	<tbody id="wpsc_checkout_list_body">
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Seleziona la lingua","bancomail-email-lists-integration");?>:</td>
		<td>
		 <?php if(get_site_option('bm_languages')==''){?>
		 <p><?php echo __("Per favore imposta prima le","bancomail-email-lists-integration");?> <a href="?page=bancomail-email-lists-integration"><?php echo __("lingue accettate","bancomail-email-lists-integration");?></a>.</p>
		<?php }?>
		 <select name="bm_ws_set_checkout_lang" id="bm_ws_set_checkout_lang" onchange="setCheckoutLanguage(this.value)">
		 <?php
		  $ln=array('it'=>'Italiano','en'=>'Inglese','fr'=>'Francese','de'=>'Tedesco','es'=>'Spagnolo');
		  foreach($available_languages as $key => $value){
		    
		      $html.='<option value="'.$value.'"';
		      if($value==$default_language){
		           $html.=' selected="selected"';
		      }
	          $html.='>'.__($ln[$value],"bancomail-email-lists-integration").'</option>';
		  }
		  echo $html;
		 ?>
		 </select>
		</td>
	 </tr>
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Header pagina","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_checkout_header_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sopra alla conferma dell'ordine.","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_checkout_header?></textarea><br/>
		  <p class="howto"><?php echo __("Esempio:","bancomail-email-lists-integration").' '.htmlentities('<h1>'.__('Conferma ordine:',"bancomail-email-lists-integration").'</h1>');?></p>
		</td>
	 </tr>
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Footer pagina","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_checkout_footer_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sotto alla conferma dell'ordine.","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_checkout_footer;?></textarea><br/>
		 <p class="howto"><?php echo __("Esempio:","bancomail-email-lists-integration").' '.htmlentities('<p><i>'.__('Riceverai il tuo database entro massimo',"bancomail-email-lists-integration").'</i></p>');?></p>
		</td>
	 </tr>
	 <tr class="new-field checkout_form_field">
		<td><?php echo __("Condizioni di vendita","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_checkout_privacy_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sotto alla form della conferma dell’ordine e prevede l’accettazione da parte del tuo cliente. Puoi inserire qui le condizioni di vendita o la normativa sulla privacy.","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_checkout_privacy?></textarea><br/>
		  <p class="howto"></p>
		</td>
	 </tr>
    <tr class="new-field checkout_form_field">
    <td><?php echo __("Informativa sul Trattamento del Dato","bancomail-email-lists-integration");?>:</td>
    <td>
     <textarea cols="100" rows="5" name="bm_data_treatment_<?php echo $default_language;?>" placeholder="<?php echo __("Questo contenuto viene mostrato sotto alla form della conferma dell’ordine e prevede l’accettazione da parte del tuo cliente. Puoi inserire la normativa GDPR.","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_data_treatment?></textarea><br/>
      <p class="howto"></p>
    </td>
   </tr>
	 <tr>
	  <td colspan="2">
	   <h4><?php echo __("L’email di conferma acquisto è inviata dall’indirizzo email impostato nella pagina di Wordpress","bancomail-email-lists-integration");?>:</h4>
	  </td>
	 </tr>
	 <tr class="new-field checkout_form_field">
		<td>*<?php echo __("Email - Header","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_checkout_email_header_<?php echo $default_language;?>" placeholder="<?php echo __("Il testo d’apertura della tua email","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_checkout_email_header;?></textarea><br/>
		 <p class="howto"><?php echo __("Esempio:","bancomail-email-lists-integration").' '.htmlentities('<p>'.__('Gentile [CLIENT_NAME], grazie per il tuo ordine!',"bancomail-email-lists-integration").'</p>');?></p>
		</td>
	 </tr>
	  <tr class="new-field checkout_form_field">
		<td><?php echo __("Email - Footer","bancomail-email-lists-integration");?>:</td>
		<td>
		 <textarea cols="100" rows="5" name="bm_checkout_email_footer_<?php echo $default_language;?>" placeholder="<?php echo __("Es: Contattaci al numero de telefono +39... o al indirizzo email info@mail.com","bancomail-email-lists-integration");?>"><?php echo $this->user_info->bm_checkout_email_footer;?></textarea><br>
          <p class="howto"><?php echo __("Esempio:","bancomail-email-lists-integration").' '.htmlentities('<p>'.__('Per eventuali chiarimenti restiamo a tua disposizione al numero +39…. oppure via email all’indirizzo info@email.com',"bancomail-email-lists-integration").'</p>');?></p>		
		</td>
	 </tr>
	 <tr class="new-field checkout_form_field">
	  <td colspan="2">
	    <?php if(get_site_option('bm_languages')!=''){?>
	    <button name="submit_checkout_setting" class="button button-primary" value="checkout"><?php echo __("Salva","bancomail-email-lists-integration");?></button>
	    <?php } ?>
	  </td>
 	 </tr>									
	</tbody>
	<tfoot>
	 <tr>
	  <th scope="col" class="manage-column column-name" colspan="2">
	   <p class="howto">
	    *<?php echo __("Per inserire il nome del cliente nella email di conferma, utilizza il tag [CLIENT_NAME]","bancomail-email-lists-integration");?><br/>
	    <?php echo __("Per inserire la ragione sociale del cliente nella email di conferma, utilizza il tag [CLIENT_COMPANY]","bancomail-email-lists-integration");?><br/>
	    <?php echo __("Per inserire il l'indirizzo del cliente nella email di conferma, utilizza il tag [CLIENT_LOCATION]","bancomail-email-lists-integration");?><br/>
	    <?php echo __("Per inserire il numero di telefono del cliente nella email di conferma, utilizza il tag [CLIENT_PHONE]","bancomail-email-lists-integration");?><br/>
	   </p>
	  </th>
	 </tr>
	</tfoot>
   </table>
 </form>	
 <?php } ?>
</div>