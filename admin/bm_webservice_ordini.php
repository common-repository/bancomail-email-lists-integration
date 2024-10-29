<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
 <div class="wrap">
  <?php  echo "<h1>" . __( 'Bancomail Email Lists Integration - Ordini', 'bancomail-email-lists-integration') . "</h1><hr>"; ?>
  <div class="cont">
   <p><?php echo __("In questa pagina puoi gestire lo stato delle tue vendite.","bancomail-email-lists-integration");?><br/>
   <span class="dashicons dashicons-warning"></span> <?php echo __("Attenzione: per richiedere la fornitura dei database acquistati dai tuoi clienti, dovrai effettuare a Neosoft Srl - Bancomail il pagamento del tuo prezzo Partner. Puoi farlo attraverso il pulsante Paga nella colonna Azioni.","bancomail-email-lists-integration");?></p>
  </div>
  <div class="bm_order_list_container">
  <table id="bm_order_list" class="widefat page fixed ui-sortable" cellspacing="0">
	<thead>
		<tr>
		 <th scope="col" class="manage-column column-name" width="100">Id</th>
		 <th scope="col" class="manage-column column-name"><?php echo __("Data","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-type"><?php echo __("Cliente","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-unique_name"><?php echo __("Ragione sociale","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-unique_name"><?php echo __("Contatti","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-mandatory"><?php echo __("La tua vendita<br/>(iva incl.)","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-display" onClick="javascript:document.getElementById('orderStatusInfo').scrollIntoView(true);"><?php echo __("Il tuo prezzo Partner","bancomail-email-lists-integration");?><br/><?php echo __("(iva incl.)","bancomail-email-lists-integration");?>*</th>
		 <th scope="col" class="manage-column column-actions"><?php echo __("Metodo di pagamento","bancomail-email-lists-integration");?></th>
		 <th scope="col" class="manage-column column-actions pointer" onClick="javascript:document.getElementById('orderStatusInfo').scrollIntoView(true);"><?php echo __("Stato del ordine","bancomail-email-lists-integration");?>**</th>
		 <th scope="col" class="manage-column column-actions"><?php echo __("Azioni","bancomail-email-lists-integration");?></th>				
		</tr>
	</thead>
	<tbody id="wpsc_checkout_list_body">
	 <tr>
		
			
			<?php 
				//var_dump($this->orders);
			    $p_link=isset($_GET['p']) && intval($_GET['p']) ? '&p='.urlencode(sanitize_text_field($_GET['p'])) : '';
				$total_orders=count($this->orders);
			if($total_orders > 0){
				$row='';
				for($i=0;$i<$total_orders;$i++){
				     $location_=esc_html(stripslashes($this->orders[$i]['client_city'])).'-';
				     $location_.=$this->orders[$i]['client_province']!='NO PROVINCE' ? esc_html(stripslashes($this->orders[$i]['client_province'])).'-' : '';
				     $location_.=esc_html(stripslashes($this->orders[$i]['client_nation']));
				     $row.='<tr class="main" id="client_'.esc_html($this->orders[$i]['id']).'">'; 
				     $row.='<td class="data">'.esc_html($this->orders[$i]['id']);
				     if($this->orders[$i]['is_test']==1){
				        $row.='<br/>'.__("(ordine di test)","bancomail-email-lists-integration"); 
				     }
				     $row.='</td>';
				     $row.='<td class="data">'.esc_html($this->orders[$i]['data']).'</td>';
				     $row.='<td class="client_name" title="Client identifier: '.esc_html($this->orders[$i]['client_identifier']).'" onClick="javascript:alert(\'Client identifier: '.$this->orders[$i]['client_identifier'].'\');">'.stripslashes($this->orders[$i]['client_name']).'<br/><i>'.$location_.'</i></td>';
				     $row.='<td class="client_rsoc">'.esc_html(stripslashes($this->orders[$i]['client_rsoc'])).'</td>';
				     $row.='<td class="client_rsoc"><a href="mailto:'.$this->orders[$i]['client_email'].'">'.$this->orders[$i]['client_email'].'</a><br/>Tel. '.$this->orders[$i]['client_phone_number'].'</td>';
				     $row.='<td class="importo">&euro; '.esc_html(number_format($this->orders[$i]['client_price_vat'],2,',','.')).'</td>';
				     $row.='<td class="bm_price_vat">&euro; '.esc_html(number_format($this->orders[$i]['bm_price_vat'],2,',','.')).'</td>';
				     $row.='<td class="payment_method">'.ucfirst(strtolower(__($this->orders[$i]['payment_method'],'bancomail-email-lists-integration'))).'</td>';
				     $row.='<td class="status">'.__($this->orders[$i]['status'],'bancomail-email-lists-integration');
				     $row.='<a class="dashicons dashicons-welcome-write-blog" href="javascript:" title="Modifica" data-clientid="'.$this->orders[$i]['client_idetifier'].'" onClick="changeOrderStatus('.$this->orders[$i]['id'].',\''.$this->orders[$i]['status'].'\');"><a>';
		             $row.='</td>';
				     $row.='<td class="url"><a href="?page=bancomail-email-lists-integration-ordini&order_id='.urlencode($this->orders[$i]['id']).$p_link.'#client_'.esc_html($this->orders[$i]['id']).'">'.__("Visualizza dettagli","bancomail-email-lists-integration").'</a> | <a target="_blank" href="'.$this->orders[$i]['partner_payment_url'].'">'.__("Paga","bancomail-email-lists-integration").'</a></td>';
				     $row.='</tr>';
				     
				     if(isset($_GET['order_id']) && intval($_GET['order_id']) && count($this->cart_details)>0 && $this->orders[$i]['bm_order_id']==$this->cart_details[0]['order_id']){
				       
				       $row.='<tr>'; 
				       $row.='<td colspan="10">';
				       $row.='<p><strong>'.__('Riepilogo Carrello','bancomail-email-lists-integration').' ('.__('Bancomail order id:','bancomail-email-lists-integration').' '.esc_html($this->orders[$i]['bm_order_id']).')</strong></p>';
				       for($z=0;$z<count($this->cart_details);$z++){
				           $row.='<p>[id: '.$this->cart_details[$z]['package_id'].'] '.esc_html($this->cart_details[$z]['macrocategory']).' - '.esc_html($this->cart_details[$z]['location']).'<br/>';
				           $row.='<i>'.__('totale anagrafiche','bancomail-email-lists-integration').': '.esc_html($this->cart_details[$z]['totale_anagrafiche']).'</i><br/>';
				           $row.='<i>'.__('prezzo listino (iva escl.)','bancomail-email-lists-integration').': &euro; '.number_format($this->cart_details[$z]['bm_price_novat'],2,',','.').'</i><br/>';
				           $row.='<i>'.__('sconto di listino','bancomail-email-lists-integration').': - '.esc_html($this->cart_details[$z]['bm_discount']).'%</i><br/>';
				           $row.='<i>'.__('ricarico','bancomail-email-lists-integration').': + '.esc_html($this->cart_details[$z]['ricarico_partner']).'%</i><br/>';
				           
				           if($this->cart_details[$z]['bm_discount']>0){
				              $discounted_price=($this->cart_details[$z]['bm_price_novat'] - ($this->cart_details[$z]['bm_price_novat'] * $this->cart_details[$z]['bm_discount']) /100);
				              
				           }
				           else{
				               $discounted_price=$this->cart_details[$z]['bm_price_novat'];
				           }
				           
				           $total_price=($discounted_price + ($discounted_price * $this->cart_details[$z]['ricarico_partner']) /100);
				           $row.='<i>'.__('Totale (iva escl.)','bancomail-email-lists-integration').': &euro;'.number_format($total_price,2,',','.').'</i><br/>';
				           $row.='<i>'.__('iva applicata dal tuo negozio:','bancomail-email-lists-integration').' +'.esc_html($this->orders[$i]['my_iva']).'%</i><br/>';
				           $total_cart_iva=($total_price+($total_price*$this->orders[$i]['my_iva'])/100);
				           $row.='<strong>'.__('Totale (iva incl.)','bancomail-email-lists-integration').': &euro;'.number_format($total_cart_iva,2,',','.').'</strong></p><hr>';
				       }
				       $row.='<p>'.__("Totale anagrafiche:","bancomail-email-lists-integration").' '.esc_html($this->orders[$i]['total_records']).' | '.__("La tua vendita","bancomail-email-lists-integration").'* &euro; '.number_format($this->orders[$i]['client_price_vat'],2,',','.').' '.__("(iva incl.)","bancomail-email-lists-integration").' |  '.__("Prezzo di listino:","bancomail-email-lists-integration").' &euro; '.number_format($this->orders[$i]['bm_price_novat_nodiscount'],2,',','.').' '.__("(iva escl.)","bancomail-email-lists-integration");
				       $row.=' | '.__("Sconto partner:","bancomail-email-lists-integration").' &euro; '.number_format($this->orders[$i]['bm_applied_discount'],2,',','.').' '.__("(iva escl.)","bancomail-email-lists-integration").' | '.__("Importo partner","bancomail-email-lists-integration").'**: &euro; '.number_format($this->orders[$i]['bm_price_vat'],2,',','.').' '.__("(iva incl.)","bancomail-email-lists-integration").'<br/>';
				       $row.=' <span style="color:#a6a6a6;">* '.__("Il totale della tua vendita è ottenuto dal calcolo  [(prezzo di listino - sconto di listino) + ricarico] + iva applicata dal tuo negozio","bancomail-email-lists-integration").'</span><br/><span style="color:#a6a6a6;">** '.__("L'importo Partner è ottenuto dal calcolo [(prezzo listino - sconto di listino - sconto partner) + iva applicata da Bancomail]","bancomail-email-lists-integration").'</span></p>';
				       $row.='</td>';
				       $row.='</tr>';
				       
				     }
				}
				echo $row;
				
			}
			else{
			    
			?>
			<tr>
			 <td colspan="10"><?php echo __("Nessun ordine da mostrare!","bancomail-email-lists-integration");?></td>
			</tr>
			<?php } ?>								
	</tbody>
	<tfoot>
	 <tr>
	 <th colspan="10" id="orderStatusInfo" style="text-align:left;font-weight:normal;">
	  <p>*<?php echo __("Se applicata dal tuo profilo di fatturazione","bancomail-email-lists-integration");?></p>
	   <p class="howto">
	    **<?php echo __("PAGATO = Se il tuo cliente paga con Paypal e ritorna sul tuo sito allora l’acquisto viene segnato automaticamente come Pagato. Puoi comunque definire manualmente come Pagato qualunque altro stato in cui si trovi un ordine, ad esempio quando il tuo cliente paga con Bonifico.","bancomail-email-lists-integration");?><br/>
	    <?php echo __("NON PAGATO = Identifica un pagamento con PayPal non confermato. Ricordati di controllare il tuo saldo PayPal e confermare il pagamento, una volta ricevuta la somma.","bancomail-email-lists-integration");?><br/>
	    <?php echo __("IN ATTESA = Identifica un pagamento tramite Bonifico. Ricordati di confermare il pagamento, una volta ricevuta la somma.","bancomail-email-lists-integration");?><br/>
	    <?php echo __("ANNULLATO = Identifica un pagamento con PayPal annullato dal tuo cliente.","bancomail-email-lists-integration");?><br/>
	   </p>
	 </th>
	 </tr>
	</tfoot>
   </table>
   <?php 
   if($this->total_orders!=''){
       require_once(plugin_dir_path( __FILE__ ).'functions.php');
       $page_range=5;
       $total_pages=ceil($this->total_orders[0]['total_orders']/20);
       $current_page=isset($_GET['p']) && intval($_GET['p']) ? intval($_GET['p']) : 1;
       $current_page_set=ceil($current_page/$page_range);
       $page_set=ceil($total_pages/$page_range);
       $pagination=BMWS_set_pagination($total_pages, $current_page, $page_range);
   }?>
   <ul class="paginator">
        <?php
         if(count($pagination) > 1 ){
    		
    		$pagination_current_page=$current_page > 0 ? $current_page : '-';
    		$html='';
    		foreach($pagination as $key => $value){
    		    if($value==0){
    		        $value=1;
    		    }
    			if($key=='previous'){
    				$html.='<li><a rel="nofollow" href="?page=bancomail-email-lists-integration-ordini&p='.urlencode($value).'">&laquo;</a></li>';
    			}
    			elseif($key=='next'){
    				$html.='<li><a rel="nofollow" href="?page=bancomail-email-lists-integration-ordini&p='.urlencode($value).'">&raquo;</a></li>';
    			}
    			elseif($value=='-'){
    				$html.='<li><a rel="nofollow" href="?page=bancomail-email-lists-integration-ordini&p='.urlencode($value).'"';
    				$html.=$pagination_current_page == '-' ? ' class="active">1</a></li>' : '>1</a></li>';
    			}
    			else{
    				$html.='<li><a rel="nofollow" href="?page=bancomail-email-lists-integration-ordini&p='.urlencode($value).'"';
    				$html.=$pagination_current_page==intval($key) ? ' class="active">'.$key.'</a></li>' : '>'.$key.'</a></li>';
    			}
    		}
    		echo $html;
         }
        ?>
         </ul><!--end pagination lidt-->
 </div>  
 <div class="bm_mask" onclick="hideMask();"></div>
 <div id="bm_ws_updateOrderStatus">
  <form name="bm_ws_updateOrderStatus" action="?page=bancomail-email-lists-integration-ordini<?php echo $p_link;?>" method="post">
   <input type="hidden" value="" id="order_id" name="order_id"/>
    <div class="bm_con title">
     <span class="dashicons dashicons-no-alt f-right" onclick="hideMask();"></span>
    </div>
    <div class="cont order_status_cont">
     <h4><?php echo __("Stai per cambiare lo stato dell'ordine:","bancomail-email-lists-integration");?> <strong></strong></h4>
     <select name="bm_ws_order_status" required>
      <option value=""><?php echo __("Seleziona lo stato","bancomail-email-lists-integration");?></option>
      <option value="PAGATO"><?php echo __(strtoupper("Pagato"),"bancomail-email-lists-integration");?></option>
      <option value="NON PAGATO"><?php echo __(strtoupper("Non pagato"),"bancomail-email-lists-integration");?></option>
      <option value="IN ATTESA"><?php echo __(strtoupper("In attesa"),"bancomail-email-lists-integration");?></option>
      <option value="ANNULLATO"><?php echo __(strtoupper("Annullato"),"bancomail-email-lists-integration");?></option>
     </select>
    </div>
    <div class="cont">
     <button class="button button-primary button-large" name="update_order"><?php echo __("Salva","bancomail-email-lists-integration");?></button>
    </div>
  </form>
  </div> 
 </div>