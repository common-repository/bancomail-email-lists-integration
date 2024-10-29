<?php
if ( ! defined( 'ABSPATH' ) ) exit;
//call the wp head so  you can get most of your wordpress
get_header();

$frontend_info='bm_frontend_'.$lang;
$user_frontend_html=json_decode(get_site_option($frontend_info,false,true));
require_once(plugin_dir_path( __DIR__ ).'scripts/translate.php');
$translate=new bm_ws_translate($lang, 'it',plugin_dir_path( __DIR__ ));
?>

<div class="bm_ws_preloader">
 <div class="bm_spinnerContainer">
   <div class="loader"></div>
 </div>
</div>

<div class="bm_ws_container">

<?php if($user_frontend_html->bm_frontend_header!=''){?>
   <div class="bm_top_container">
      <div class="bm_wrapper" style="display:block">
       <div class="bm_container">
        <?php echo html_entity_decode($user_frontend_html->bm_frontend_header);?>
       </div>
      </div>
   </div>   
<?php } ?>



 <?php if(isset($addToCartResponse)){?>
 <div class="bm_top_container">
   <div class="bm_wrapper" <?php if(!isset($_GET['paypal'])){?>onClick="javascript:this.style.display='none';"<?php } else{?>onClick="javascript:window.location='<?php echo get_permalink( $this->user_info['bm_ws_front_page_postid'] ).'?lang='.urlencode(sanitize_text_field($_GET['lang']));?>';"<?php }?>>
    <div class="bm_container alert_box alert_<?php echo $addToCartResponse['status']; ?>">
      
       <?php echo $translate->_print($addToCartResponse['msg']); ?>
       <img src="<?php echo plugins_url( 'images/cancel.png', __DIR__ );?>" title="<?php echo $translate->_print('Chiudi');?>"  width="10" class="f-right pointer" />
    
    </div>
   </div>
 </div>  
 <?php } ?>

<div class="bm_top_container">

 <div class="bm_wrapper" id="bm_ws_top">
   <div class="bm_item filtersContainerHeader" id="filtersContainerHeader"> 
    <p>
     <!-- <strong><?php echo $translate->_print('Lingua')?>:</strong> -->
     <?php
      $active_languages=explode('|',$this->user_info['bm_languages']);
      if(count($active_languages) > 1){
         foreach ($active_languages as $values){
             $active_lang=$values==$lang ? 'active' : '';
     ?>
     <a href="javascript:>" onClick="changeLanguage('<?php echo $values?>');" class="<?php echo $active_lang;?>"><span style="background-image:url('<?php echo plugins_url( 'images/sprites.png', __DIR__ ); ?>');" class="bm-flag bm-flag-<?php echo $values?>"></span> <?php echo strtoupper($values)?></a> 
     <?php 
         }
      } 
     ?>
    
    </p>
   </div>
 
   <div class="bm_item emailListsContainerHeader">
     <p>
      <strong><?php if(!isset($_GET['naz']) && !isset($_GET['macro'])){echo $translate->_print('Pacchetti in evidenza:');} else{echo $translate->_print('Pacchetti trovati');?>: <?php echo $this->total_results;}?></strong>
     </p>
   
   </div>
   <div class="bm_item cartContainer"></div>
   
 </div>
</div>

 <div class="bm_wrapper" id="bm_ws_main">
   <div class="bm_item filtersContainer">
   <form name="bm_ws_filters" class="bm_ws_filters" method="get" action="<?php echo get_permalink( $this->user_info['bm_ws_front_page_postid'] );?>">
    <input type="hidden" name="lang" value="<?php echo $lang;?>"/>
	<?php if(isset($_GET['page_id']) && intval($_GET['page_id'])){?>
     <input type="hidden" name="page_id" value="<?php $post = get_post(); echo $post->ID;?>"/>
    <?php }?>
    <div class="bm_container">
      <h4 class="bm_title"><?php echo $translate->_print('Categorie');?>:</h4>
      <select class="bm_fliter categoryFilter" name="macro" onchange="setFilters();">
       <option value=""><?php echo $translate->_print('Seleziona');?></option>
       <?php
        $nationFilter='';
        if(is_array($getCategories->category)){
            foreach ($getCategories as $key => $value) {
                foreach($value as $obj){
                $categoryFilter.='<option value="'.$obj->id.'"';
                if(isset($_GET['macro']) && intval($_GET['macro'])==$obj->id){
                    $categoryFilter.=' selected="selected"';
                }
                $categoryFilter.='>'.$obj->name.'</option>';
                }
            };
        }
        else{
            $categoryFilter.='<option value="'.$getCategories->category->id.'" selected="selected">'.$getCategories->category->name.'</option>';
        }
        echo $categoryFilter;
       ?>
      </select>
    </div>
    <div class="bm_container">
     <h4 class="bm_title"><?php echo $translate->_print('Nazioni');?>:</h4>
     <select class="bm_fliter nationFilter" name="naz" onchange="setFilters();">
       <option value=""><?php echo $translate->_print('Seleziona');?></option>
       <?php
        $nationFilter='';
        foreach ($getNations as $key => $value) {
            foreach($value as $obj){
             $nationFilter.='<option value="'.$obj->iso_code.'"';
             if(isset($_GET['naz']) && sanitize_text_field($_GET['naz'])==$obj->iso_code){
                  $nationFilter.=' selected="selected"';
             }
             $nationFilter.='>'.$obj->name.'</option>';
            }
        };
        echo $nationFilter;
       ?>
       <option value=""><?php echo $translate->_print('Tutte le nazioni');?></option>
      </select>
    </div>
    
    <div class="bm_container">
     <h4 class="bm_title"><?php echo $translate->_print('Regioni');?>:</h4>
     <select class="bm_fliter regionFilter" name="region" <?php if(!isset($getRegions) || !is_array($getRegions->regions)){?>disabled<?php }?> onchange="setFilters();">
       <option value=""><?php echo $translate->_print('Seleziona');?></option>
       <?php
       if(isset($getRegions)){
            $regionsFilter='';
            foreach ($getRegions as $key => $value) {
                foreach($value as $obj){
                 $regionsFilter.='<option value="'.$obj->id.'"';
                 if(isset($_GET['region']) && intval($_GET['region'])==$obj->id){
                   $regionsFilter.=' selected="selected"';
                 }   
                 $regionsFilter.='>'.$obj->name.'</option>';
                }
            };
            echo $regionsFilter;
       }
       ?>
      </select>
    </div>
    
   </form>
  </div>
   
   <div class="bm_item emailListsContainer">
    <form name="bm_product_list" class="bm_product_list" action="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post">
    <table class="bm_elenchi_email">
     <thead>
      <tr>
       <th><?php echo $translate->_print('Categoria');?></th>
       <th><?php echo $translate->_print('Nazione');?></th>
       <th style="width:50px;"><?php echo $translate->_print('Anagrafiche');?></th>
       <th style="width:100px;"><?php echo $translate->_print('Prezzo');?></th>
       <th><?php echo $translate->_print('Azioni');?></th>
      </tr>
     </thead>
     <tbody>
      <?php 
      
          $row='';
          
          if($getEmailLists->package!=null){
              $getEmailLists=$getEmailLists->package;
          }
          if($getEmailLists==null && $getEmailLists->package==null){
              $row.='<tr class="bm_active"><td colspan="5">'.$translate->_print('La tua ricerca non ha prodotto nessun risultato!').'</td></tr>';
          }
         
          if(is_array($getEmailLists)){
         
              $tr_index=0;
             foreach($getEmailLists as $obj){
                $tr_index++;
                $in_cart='';
                if(isset($getCart)){
                 if(is_array($getCart->YourCart->cli_package)){   
                    for($x=0;$x<sizeof($getCart->YourCart->cli_package); $x++) {
                        if($getCart->YourCart->cli_package[$x]->package_id == $obj->id){
                            $in_cart=' in-cart';
                            break;
                        }
                    }
                  }
                  else if($getCart->YourCart->cli_package->package_id == $obj->id){
                          $in_cart=' in-cart';
                  }
                }
                $row.=($tr_index <= 20) ? '<tr class="bm_active'.$in_cart.'">' : '<tr class="'.$in_cart.'">';
                
                $row.='<td class="bm_ws_macro_td" title="'.$obj->macrocategory.'">'.$obj->macrocategory;
                if($obj->discount > 0){
                    $row.='<span class="discount-buble">-'.$obj->discount.'%</span>';
                }
                $row.='</td>';
                $row.='<td class="bm_naz_container"><span class="flag-al" style="background: url('.plugins_url( 'images/flags/'.strtolower($obj->nation).'.png', __DIR__ ).');"></span>'.$obj->nation_name;
                if($obj->region!=''){
                    $row.=' - '.$obj->region;
                }
                if($obj->province!=''){
                    $row.=' - '.$obj->province;
                }
                $row.='</td>';
                $row.='<td>'.$obj->total_records.'</td>';
                if($obj->discount > 0){
                    
                    $prezzo_pieno_novat_usrtax=($obj->price_no_vat + ($obj->price_no_vat * $this->user_info['bm_user_tax']) /100);
                    $discounted_price=($obj->price_no_vat - ($obj->price_no_vat * $obj->discount) /100);
                    $user_price=($discounted_price + ($discounted_price * $this->user_info['bm_user_tax']) /100);
                    $row.='<td data-discount="'.$obj->discount.'" data-price-novat="'.$obj->price_no_vat.'">';
                    $row.='<span class="line-through">'.number_format($prezzo_pieno_novat_usrtax, 2, ',','.').' &euro;</span><br/>';
                    $row.=number_format($user_price, 2, ',','.').' &euro;</td>';
                }
                else{
                 $user_price=($obj->price_no_vat + ($obj->price_no_vat * $this->user_info['bm_user_tax']) /100);
                 $row.='<td data-price-novat="'.$obj->price_no_vat.'">'.number_format($user_price, 2, ',','.').' &euro;</td>';
                }
                if($in_cart==''){
                 $row.='<td><button name="bm_product" value="'.$obj->id.'" title="'.$translate->_print('Aggiungi al carrello').'" onmouseup="showPreloader();"><img src="'.plugins_url( 'images/shopping-cart.png', __DIR__ ).'" alt="cart" width="12" /> '.$translate->_print('Acquista').'</button></td>';
                }
                else{
                    $row.='<td title="'.$translate->_print('Il prodotto &egrave; stato aggiunto al carrello con successo!').'"><img src="'.plugins_url( 'images/checked.png', __DIR__ ).'" alt="product in cart" width="12" /></td>';
                }
                $row.='</tr>';
             }
             
             
            
          }
          elseif(!is_array($getEmailLists) && $getEmailLists!=null){
              
              $in_cart='';
              if(isset($getCart)){
                  if(is_array($getCart->YourCart->cli_package)){
                      for($x=0;$x<sizeof($getCart->YourCart->cli_package); $x++) {
                          if($getCart->YourCart->cli_package[$x]->package_id == $getEmailLists->id){
                              $in_cart=' in-cart';
                              break;
                          }
                      }
                  }
                  else if($getCart->YourCart->cli_package->package_id == $getEmailLists->id){
                      $in_cart=' in-cart';
                  }
              }
              
              $row.='<tr class="bm_active'.$in_cart.'">';
              $row.='<td title="'.$getEmailLists->macrocategory.'">'.$getEmailLists->macrocategory;
              if($getEmailLists->discount > 0){
                  $row.='<span class="discount-buble">-'.$getEmailLists->discount.'%</span>';
              }
              $row.='</td>';
              $row.='<td><span class="flag-al" style="background: url('.plugins_url( 'images/flags/'.strtolower($getEmailLists->nation).'.png', __DIR__ ).');"></span> '.$getEmailLists->nation_name;
              if($getEmailLists->region!=''){
                  $row.=' - '.$getEmailLists->region;
              }
              if($getEmailLists->province!=''){
                  $row.=' - '.$getEmailLists->province;
              }
              $row.='</td>';
              $row.='<td>'.$getEmailLists->total_records.'</td>';
              if($getEmailLists->discount > 0){
                  $discounted_price=($getEmailLists->price_no_vat - ($getEmailLists->price_no_vat * $getEmailLists->discount) /100);
                  $user_price=($discounted_price + ($discounted_price * $this->user_info['bm_user_tax']) /100);
                  $row.='<td data-discount="'.$getEmailLists->discount.'" data-price-novat="'.$getEmailLists->price_no_vat.'">'.number_format($user_price, 2, ',','.').' &euro;</td>';
              }
              else{
               $row.='<td>'.number_format(($getEmailLists->price_no_vat+($getEmailLists->price_no_vat * $this->user_info['bm_user_tax']) /100), 2, ',','.').' &euro;</td>';
              }
              if($in_cart==''){
               $row.='<td><button name="bm_product" value="'.$getEmailLists->id.'" title="'.$translate->_print('Aggiungi al carrello').'" onmouseup="showPreloader();"><img src="'.plugins_url( 'images/shopping-cart.png', __DIR__ ).'" alt="cart" width="12" /> '.$translate->_print('Acquista').'</button></td>';
              }
              else{
                  $row.='<td title="'.$translate->_print('Il prodotto &egrave; stato aggiunto al carrello con successo!').'"><img src="'.plugins_url( 'images/checked.png', __DIR__ ).'" alt="product in cart" width="12" /></td>';
              }
              $row.='</tr>';
          }
       
         echo $row;
         ?>
      </tbody>   
     </table>
    </form> 
    <?php 
      if(is_array($getEmailLists) && count($getEmailLists) > 20){
         $total_pages=ceil(count($getEmailLists) / 20);
      }?> 
      <div class="bm_paginator">
       <ul class="bm_page_list">
        <?php for($i=0;$i < $total_pages;$i++){ $is_active=$i==0 ? 'is_active' : '';?>
         <li><a href="javascript:" onClick="changePage(<?php echo ($i+1);?>, false);" class="<?php echo $is_active;?>"><?php echo ($i+1);?></a></li>
        <?php }?>
       </ul>
      </div>
     <?php ?>
   </div>
   
   <div class="bm_item cartContainer">
    <div class="bm_container">
     <h4 class="bm_title">
      <?php echo $translate->_print('Carrello');?> <img src="<?php echo plugins_url( 'images/down-arrow.png', __DIR__ ); ?>" alt="down-arrow" title="Espandi il carrello" width="16"  id="expandCart" onClick="showCart();" />
      <?php if(isset($getCart) && is_array($getCart->YourCart->cli_package)){?><span class="f-right"><?php echo count($getCart->YourCart->cli_package);?> <?php echo $translate->_print('pacchetti');?></span><?php }?>
     </h4>
    <span class="clearfix"></span>
     <?php if(isset($getCart)){?>
     <form name="bm_cart_list" class="bm_cart_list" action="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post">
      <input type="hidden" name="bm_remove_item" value=""/>
      <input type="hidden" name="lang" value="<?php echo $lang;?>"/>
      <ul class="bm_cart">
       <?php
        $item='';
        $total_no_iva=0;
        $cat_naz='name_'.$lang;
        $total_records=$getCart->YourCart->total_records;
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
                 $item.='<strong class="text-right full inline-block" data-discount="'.$getCart->YourCart->cli_package[$i]->discount.'"><img src="'.plugins_url( 'images/cancel.png', __DIR__ ).'" title="'.$translate->_print('Rimuovi').'" onClick="removeItem('.$getCart->YourCart->cli_package[$i]->cart_item_id.');" width="10" class="f-left pointer" />';
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
            $item.='<strong class="text-right full inline-block" data-discount="'.$getCart->YourCart->cli_package->discount.'"><img src="'.plugins_url( 'images/cancel.png', __DIR__ ).'" title="'.$translate->_print('Rimuovi').'" onClick="removeItem('.$getCart->YourCart->cli_package->cart_item_id.');" width="10" class="f-left pointer" />';
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
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Importo IVA');?>(<?php echo $this->user_info['bm_user_iva'];?>%):</span> <?php echo number_format($iva,2,',','.');?> &euro;</p>
        <p class="text-right"><strong class="f-left"><?php echo $translate->_print('Totale IVA incl.');?>:</strong> <strong><?php echo number_format(($total_no_iva+$iva),2,',','.');?> &euro;</strong></p>
      </div>
      <input type="hidden" name="client_price_novat" value="<?php echo number_format($total_no_iva,2,',','.');?>"/>
      <input type="hidden" name="client_price_vat" value="<?php echo number_format(($total_no_iva+$iva),2,',','.');?>"/>
      <button onmouseup="showPreloader();" name="checkout" value="<?php echo $client_id;?>" class="full"><?php echo $translate->_print('Vai alla cassa');?> <img src="<?php echo plugins_url( 'images/right-arrow-white.png', __DIR__ );?>" alt="right-arrow" width="12" /></button>
      </form>
     <?php } else{?>
      <div class="bm_cart_list"> 
       <div class="total-box">
        <br/>
        <p><?php echo $translate->_print('0 prodotti nell carrello.');?></p>
        <br/>
       </div> 
      </div>
     <?php }?>
    </div>
   </div>
 
 </div>
 
 <?php if($total_no_iva>0){?>
 <div class="bm_wrapper total-bottom-box">
  <div class="bm_container">
    <div class="total-box">
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Anagrafiche');?>:</span><?php echo $total_records;?></p>
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Totale IVA escl.');?>:</span> <?php echo number_format($total_no_iva,2,',','.');?> &euro;</p>
        <p class="text-right"><span class="f-left"><?php echo $translate->_print('Importo IVA');?>(<?php echo $this->user_info['bm_user_iva'];?>%):</span> <?php echo number_format($iva,2,',','.');?> &euro;</p>
        <p class="text-right"><strong class="f-left"><?php echo $translate->_print('Totale IVA incl.');?>:</strong> <strong><?php echo number_format(($total_no_iva+$iva),2,',','.');?> &euro;</strong></p>
     </div>
     <form action="<?php echo $this->protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post">
      <input type="hidden" name="checkout" value="<?php echo $client_id;?>" />
      <button onmouseup="showPreloader();" name="go_to_checkout" class="full"><?php echo $translate->_print('Checkout');?> <img src="<?php echo plugins_url( 'images/right-arrow-white.png', __DIR__ );?>" alt="right-arrow" width="12" /></button>
     </form> 
  </div>
 </div>
 <?php } ?>
 
  <?php if($user_frontend_html->bm_frontend_footer!=''){?>
      <div class="bm_wrapper" style="display:block">
       <div class="bm_container">
        <?php echo html_entity_decode($user_frontend_html->bm_frontend_footer);?>
       </div>
      </div>
<?php } ?>


 
 <script type="text/javascript">
    
     if (typeof(Storage) !== "undefined" && sessionStorage.currentPage) {
    		changePage(sessionStorage.currentPage, true);
     }
     //hide preloader
     document.querySelector('.bm_ws_preloader').setAttribute('class','bm_ws_preloader hidden');


 </script>
 
 
</div><!-- //end of bm_ws_container -->


<?php get_footer(); ?>
