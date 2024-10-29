<?php
if ( ! defined( 'ABSPATH' ) ) exit;
//call the wp head so  you can get most of your wordpress
get_header();

$user_info='bm_checkout_'.$lang;
$user_html=json_decode(get_site_option($user_info,false,true));
require_once(plugin_dir_path( __DIR__ ).'scripts/translate.php');
$translate=new bm_ws_translate($lang,'it',plugin_dir_path( __DIR__ ));
?>

<div class="bm_ws_preloader">
 <div class="bm_spinnerContainer">
   <div class="loader"></div>
 </div>
</div>

<div class="bm_ws_container">

<?php 
 if(isset($order_placed_ok)){
     $page = get_page_by_title( 'database' );
     $the_excerpt = $page->post_excerpt;
     $page_data = get_page( $page );
     $title = $page_data->post_title;
?>
<div class="bm_top_container">
 <div class="bm_wrapper bm_ws_ordine_confermato">
  <div class="bm_container" style="width:100%;">
   <h1><?php echo $translate->_print('Ordine confermato!');?></h1>
   <hr>
   <p><?php echo $translate->_print('Grazie per il tuo ordine! Ti confermiamo che il tuo ordine è stato effettuato con successo.');?></p>
   <p><?php echo $translate->_print('Controlla la tua Email, ti abbiamo inviato un’email di conferma.');?></p>
   <p><strong><?php echo $translate->_print('Istruzioni per il pagamento:');?></strong></p>
   <?php $bonifico=json_decode($this->user_info['bm_bonifico']); echo html_entity_decode($bonifico->bm_bonifico_instructions->$lang);?>
   <hr>
   <p>
    <?php $go_back_url = (isset($_GET['page_id']) && intval($_GET['page_id'])) ? '&lang=' : '?lang=';?>
    <a href="<?php echo str_replace('__trashed','',esc_url( get_permalink(get_page_by_title('database')))); ?><?php echo $go_back_url.urlencode(sanitize_text_field($_POST['lang'])); ?>">&laquo; <?php echo $translate->_print('Continua le spese');?></a> |
    <a href="javascript:" onClick="window.print();"><?php echo $translate->_print('Stampa questa pagina');?></a>
   </p>
  </div>  
 </div>
</div>
<?php } else{?>

<?php if($user_html->bm_checkout_header!=''){?>
      <div class="bm_wrapper" style="display:block">
       <div class="bm_container">
        <?php echo html_entity_decode($user_html->bm_checkout_header);?>
       </div>
      </div>
<?php } ?>

 <?php if(isset($addToCartResponse)){?>
  <div class="bm_top_container">
   <div class="bm_wrapper" onClick="javascript:this.style.display='none';">
    <div class="bm_container alert_box alert_<?php echo $addToCartResponse['status']; ?>">
      
       <?php echo $translate->_print($addToCartResponse['msg']); ?>
       <img src="<?php echo plugins_url( 'images/cancel.png', __DIR__ );?>" title="<?php echo $translate->_print('Chiudi');?>"  width="10" class="f-right pointer" />
    
    </div>
   </div>
  </div> 
 <?php } ?>
 
 <div class="bm_top_container checkoutContainer">
 <div class="bm_wrapper bm_ws_riepilogo">
     <div class="bm_item cartContainer">
    <div class="bm_container">
     <h4 class="bm_title"><?php echo $translate->_print('Riepilogo carrello');?>:</h4>
     <?php if(isset($getCart)){?>
     <form name="bm_cart_list" class="bm_cart_list" action="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post">
      <input type="hidden" name="bm_remove_item" value=""/>
      <ul class="bm_cart">
       <?php
        $total_records=$getCart->YourCart->total_records;
        $item='';
        $total_no_iva=0;
        $cat_naz='name_'.$lang;
        if(is_array($getCart->YourCart->cli_package)){
           for($i=0;$i<sizeof($getCart->YourCart->cli_package); $i++) {
                 if($getCart->YourCart->cli_package[$i]->discount > 0){
                  $discounted_item_price=($getCart->YourCart->cli_package[$i]->price_no_iva - ($getCart->YourCart->cli_package[$i]->price_no_iva * $getCart->YourCart->cli_package[$i]->discount) /100);
                  $item_price = ($discounted_item_price + ($discounted_item_price * $this->user_info['bm_user_tax']) /100);
                 }
                 else{
                  $item_price = ($getCart->YourCart->cli_package[$i]->price_no_iva + ($getCart->YourCart->cli_package[$i]->price_no_iva * $this->user_info['bm_user_tax']) /100);
                 }
                 $item.='<li>';
                 $item.='<strong class="text-right inline-block">';
                 $item_name=$lang.'_name';
                 $item_region_name=$getCart->YourCart->cli_package[$i]->region->name!='' ? ' - '.$getCart->YourCart->cli_package[$i]->region->name : '';
                 $item.=' '.$getCart->YourCart->cli_package[$i]->macrocategory->$item_name.' - '.$getCart->YourCart->cli_package[$i]->nation->$cat_naz. $item_region_name.'</strong>';
                 $item.='<span class="inline-block full">'.$translate->_print('anagrafiche').':<span class="f-right">'.$getCart->YourCart->cli_package[$i]->tot_anag.'</span></span>';
                 $item.='<span class="inline-block full" data-prezzo-bm="'.$getCart->YourCart->cli_package[$i]->price_no_iva.'">'.$translate->_print('prezzo').':<span class="f-right">'.number_format($item_price,2,',','.').' &euro;</span></span>';
                 $item.='</li>';
                 $total_no_iva+=$item_price;
              
           }
        }
        else{
            if($getCart->YourCart->cli_package->discount > 0){
                $discounted_item_price=($getCart->YourCart->cli_package->price_no_iva - ($getCart->YourCart->cli_package->price_no_iva * $getCart->YourCart->cli_package->discount) /100);
                $item_price = ($discounted_item_price + ($discounted_item_price * $this->user_info['bm_user_tax']) /100);
            }
            else{
                $item_price = ($getCart->YourCart->cli_package->price_no_iva + ($getCart->YourCart->cli_package->price_no_iva * $this->user_info['bm_user_tax']) /100);
            }
            $item.='<li>';
            $item_name=$lang.'_name';
            $item_region_name=$getCart->YourCart->cli_package->region->name!='' ? ' - '.$getCart->YourCart->cli_package->region->name : '';
            $item.='<strong class="text-right inline-block">';
            $item.=' '.$getCart->YourCart->cli_package->macrocategory->$item_name.' - '.$getCart->YourCart->cli_package->nation->$cat_naz.$item_region_name.'</strong>';
            $item.='<span class="inline-block full">'.$translate->_print('anagrafiche').':<span class="f-right">'.$getCart->YourCart->cli_package->tot_anag.'</span></span>';
            $item.='<span class="inline-block full" data-prezzo-bm="'.$getCart->YourCart->cli_package->price_no_iva.'">'.$translate->_print('prezzo').':<span class="f-right">'.number_format($item_price,2,',','.').' &euro;</span></span>';
            $item.='</li>';
            $total_no_iva+=$item_price;
        }
        echo $item;
       ?>
      </ul>
      <div class="total-box">
        <?php 
         $iva=(($total_no_iva * $this->user_info['bm_user_iva']) /100);
        ?>
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Anagrafiche');?>:</span><?php echo $total_records;?></p>
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Totale IVA escl.');?>:</span> <?php echo number_format($total_no_iva,2,',','.');?> &euro;</p>
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Importo IVA');?>(<?php echo $this->user_info['bm_user_iva']; ?>%):</span> <?php echo number_format($iva,2,',','.');?> &euro;</p>
        <p class="text-right"><strong class="f-left"><?php echo $translate->_print('Totale IVA incl.');?>:</strong> <strong><?php echo number_format(($total_no_iva+$iva),2,',','.');?> &euro;</strong></p>
      </div>
      </form>
      <?php  }?>
      <div id="backLinkHolder">
       <a href="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" onmouseup="showPreloader();"><img src="<?php echo plugins_url( 'images/left-arrow-white.png', __DIR__ );?>" alt="left-arrow" width="12" class="back_link"/> <?php echo $translate->_print('Torna indietro');?></a>
      </div>
    </div>
   </div> 
   
   <div class="bm_item confermaOrdineContainer">
    <div class="">
     <h4 class="bm_title"><?php echo $translate->_print("Conferma l'ordine");?>:</h4>
     <p><?php echo $translate->_print("Compila il form con i tuoi dati e clicca il pulsante 'Acquista'");?></p>
     <form name="confirmOrder" action="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="javascript:return false;">
      <input type="hidden" name="checkout" value="<?php echo $client_id;?>"/>
      <input type="hidden" name="lang" value="<?php echo $lang;?>"/>
      <input type="hidden" name="total_records" value="<?php echo $getCart->YourCart->total_records;?>"/>
      <input type="hidden" name="bm_price_novat_nodiscount" value="<?php echo $getCart->YourCart->price_novat_nodiscount;?>"/>
      <input type="hidden" name="bm_applied_discount" value="<?php echo $getCart->YourCart->applied_discount;?>"/>
      <input type="hidden" name="bm_price_novat" value="<?php echo $getCart->YourCart->price_novat;?>"/>
      <input type="hidden" name="bm_price_vat" value="<?php echo $getCart->YourCart->price_vat;?>"/>
      <input type="hidden" name="client_price_novat" value="<?php echo $total_no_iva;?>"/>
      <input type="hidden" name="client_price_vat" value="<?php echo $total_no_iva+$iva;?>"/>
      <div class=confirmOrder>
          <input type="hidden" name="client_id" value="<?php echo $client_id;?>"/> 
          <div class="bm_cont">
           <label for="bm_ws_client_name" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Nome e cognome");?>:</label><br/>
           <input type="text" name="bm_ws_client_name" id="bm_ws_client_name" value="" maxlength="60" onfocus="clearField('bm_ws_client_name');"/>
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_rsoc" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Ragione sociale");?>:</label><br/>
           <input type="text" name="bm_ws_client_rsoc" id="bm_ws_client_rsoc" value="" maxlength="60" onfocus="clearField('bm_ws_client_rsoc');"/>
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_city" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Città");?>:</label><br/>
           <input type="text" name="bm_ws_client_city" id="bm_ws_client_city" value="" maxlength="60" onfocus="clearField('bm_ws_client_city');"/>
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_province"><?php echo $translate->_print("Provincia");?>:</label><br/>
           <input type="text" name="bm_ws_client_province" id="bm_ws_client_province" value="" maxlength="60"  onfocus="clearField('bm_ws_client_province');"/>
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_nation" title="<?php echo $translate->_print('Questo campo è obligatorio');?>"><?php echo $translate->_print("*Nazione");?>:</label><br/>
           <input type="text" name="bm_ws_client_nation" id="bm_ws_client_nation" value="" maxlength="60" onfocus="clearField('bm_ws_client_nation');"/>
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_phone_number"><?php echo $translate->_print("Telefono");?>:</label><br/>
           <input type="text" name="bm_ws_client_phone_number" id="bm_ws_client_phone_number" value="" maxlength="10" />
          </div>
          <div class="bm_cont">
           <label for="bm_ws_client_email" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Email");?>:</label><br/>
           <input type="email" name="bm_ws_client_email" id="bm_ws_client_email" value="" maxlength="80" onfocus="clearField('bm_ws_client_email');"/>
          </div>
           <div class="bm_cont">
           <label for="bm_ws_client_repeat_email" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Ripeti email");?>:</label><br/>
           <input type="email" name="bm_ws_client_repeat_email" id="bm_ws_client_repeat_email" value="" maxlength="80"  onfocus="clearField('bm_ws_client_repeat_email');"/>
          </div>
          <div class="bm_cont" id="ghostTrap">
           <input type="text" name="_name" value="" />
          </div>
           <div class="bm_cont">
           <p id="select_payment_gateway" title="<?php echo $translate->_print('Questo campo è obligatorio');?>">*<?php echo $translate->_print("Seleziona modalità di pagamento");?>:</p>
           <?php 
            $this->user_info['bm_bonifico']=json_decode($this->user_info['bm_bonifico']);
           ?>
           <?php if($this->user_info['bm_bonifico']->bm_bonifico_option==1){?>
           <input type="radio" name="bm_ws_client_payment_method" id="bm_ws_client_bonifico" value="bonifico" onfocus="clearField('bm_ws_client_bonifico');" checked="checked"/>
           <label for="bm_ws_client_bonifico"><?php echo $translate->_print($this->user_info['bm_bonifico']->bm_bonifico_user_defined_name);?></label>
           <?php } ?>
           <br/>
           <?php $this->user_info['bm_paypal_standard']=json_decode($this->user_info['bm_paypal_standard']);?>
           <?php if($this->user_info['bm_paypal_standard']->bm_paypal_standard_option==1){?>
           <input type="radio" name="bm_ws_client_payment_method"  id="bm_ws_client_paypal" value="paypal" onfocus="clearField('bm_ws_client_paypal');" <?php if($this->user_info['bm_bonifico']->bm_bonifico_option!=1){ echo 'checked="checked"';}?>/>
           <label for="bm_ws_client_paypal"><?php echo $translate->_print($this->user_info['bm_paypal_standard']->bm_paypal_standard_user_defined_name);?></label>
           <?php } ?>
          </div>
        </div>
        
        <div class="buyBtnHolder full"> 
        <?php if($user_html->bm_checkout_privacy != '') { ?>
         <input type="checkbox" name="bm_ws_client_privacy_ok" id="bm_ws_client_privacy_ok" value="ok" onchange="clearField('bm_ws_client_privacy_ok');"/>
         <label for="bm_ws_client_privacy_ok"><?php echo $translate->_print("Accetto le");?> <a href="javascript:" class="underlined" onClick="showPrivacy();"><?php echo $translate->_print("condizioni di vendita");?></a> *</label>
         <div class="bm_ws_view_privacy">
          <?php echo html_entity_decode($user_html->bm_checkout_privacy);?>
        </div>
        <?php } ?>
        <?php if($user_html->bm_data_treatment != '') { ?>
         <input type="checkbox" name="bm_ws_gdpr_ok" id="bm_ws_gdpr_ok" value="ok" onchange="clearField('bm_ws_gdpr_ok');"/>
         <label for="bm_ws_gdpr_ok"><?php echo $translate->_print("Accetto");?> 
         <a href="javascript:" class="underlined" onClick="showGdpr();"><?php echo $translate->_print("l'informativa");?></a> <?php echo $translate->_print("sul trattamento dei dati");?>*</label>
         <div class="bm_ws_view_gdpr">
          <?php echo html_entity_decode($user_html->bm_data_treatment);?>
         </div> 
        <?php } ?>
        <?php if($this->user_info['bm_bonifico']->bm_bonifico_option==1 || $this->user_info['bm_paypal_standard']->bm_paypal_standard_option==1){?>
         <div class="full submit-btn-holder">
          <button name="acquista" value="<?php echo $client_id;?>" onClick="buyNow();"><?php echo $translate->_print("ACQUISTA");?></button>
         </div>
        <?php } ?>
        </div> 
         
     </form>
 
 
    </div> 
   </div>
 </div>
  <div class="clearfix"></div>
 </div>
 <?php } ?>
 
 <?php if($user_html->bm_checkout_footer!=''){?>
  
   <div class="bm_top_container">
      <div class="bm_wrapper user_footer" style="display:block">
       <div class="bm_container">
        <?php echo html_entity_decode($user_html->bm_checkout_footer);?>
       </div>
      </div>
    </div>  
<?php } ?>
 
   <script type="text/javascript">
     //hide preloader
     document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader hidden');
   </script>
   
</div><!-- //end of bm_ws_container -->
   
 <?php get_footer(); ?>