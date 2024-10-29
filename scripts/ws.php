<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class BMWS_Bancomail_Ws {
  protected $client;
  protected $url='https://wbx.bancopress.com/BancoMailWS/BancoWs?wsdl';
 
  public function __construct($user, $password){
    
     $config = array('soap_version' => SOAP_1_2, 'login' => $user, 'password' => $password);
     try{
      $this->client = new SoapClient($this->url, $config);
     
     } catch (Exception $e) {
         echo $e->getMessage();
     }
  }
  
  public function BM_WS_test_connection(){
      try{
          $this->client->hello();
          return 'Connection OK';
      } catch (Exception $e) {
          $response = "Errore nella connessione al WebService, riprova a breve oppure contattaci";
          if(strpos($e->getMessage(), "SOAP-ERROR: Parsing WSDL: Could") !== false) {
              $response = "Il servizio non è disponibile, riprova a breve oppure contattaci";
          }
          if($e->getMessage() == 'Unauthorized') {
              $response = "Connessione non riuscita: verifica le tue credenziali";
          }
           
          return $response;
      }
  }
  
  /* Isl metodo restituisce un xml contenete tutte le macro-categorie (id e nome)*/
  public function BM_WS_get_categories($lang){
      try{
       $categories = $this->client->getCategories(array('lang' => $lang));
       return $categories;
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  /*Il metodo restituisce un xml contenete tutte le nazioni a cui corrisponda almeno un pacchetto nel database Bancomail*/
  public function BM_WS_get_all_nations($lang){
      try{
       return $this->client->getNoEmptyNations(array('lang' => $lang));
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  /*Il metodo restituisce tutte le regioni censite nel database Bancomail*/
  public function BM_WS_get_all_regions($nationcode){
      return $this->client->getAllRegions(array('nationcode' => $nationcode));
  }
  
  public function BM_WS_get_catgories_by_nation($lang, $nationcode){
      try{
        $nations=$this->client->getCategoriesByNation(array('lang' => $lang, 'nationcode' => $nationcode));
        return $nations;
      } catch (Exception $e) {
         return false;
      }
  }
  
  public function BM_WS_get_nation_by_category($lang, $macro){
      try{
        return $this->client->getNationsByCategory(array('lang' => $lang, 'macro' => $macro));
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  public function BM_WS_get_region_by_category_and_nation($nationcode, $macro){
      return $this->client->getRegionsByCategoryAndNation(array('nationcode' => $nationcode, 'macro' => $macro));
  }
  
  public function BM_WS_get_pricelist_selection($parameters){
      try{
       $pck=$this->client->getPriceListSelection($parameters);
       $lists=$pck->package;
       return $lists;
      }
      catch (Exception $e) {
          return false;
      }
   }
  
  
  public function BM_WS_get_promo_lists($lang){
      try{
       return $this->client->getPromoPackages(array('lang' => $lang));
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  
  public function BM_WS_get_cart_at_glance($client_identifier){
      try{
        return $this->client->getCartAtGlance(array('client_identifier' => $client_identifier));
      }
      catch (Exception $e) {
           return false;
       }
  }
  

  /*Il metodo aggiunge un pacchetto al carrello*/
  public function BM_WS_addPackage($pkgid, $client_id=false){
      try{
      $parameters= $client_id!=false ?array('pkgid' => $pkgid, 'client_identifier'=>$client_id) : array('pkgid' => $pkgid);
      $result = $this->client->addToCart($parameters);
      return $result->OperationResult;
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  public function BM_WS_check_out_cart($lang, $client_id){
      try{
       return $this->client->checkOutCart(array('lang' => $lang, 'client_identifier'=>$client_id));
      }
       catch (Exception $e) {
           return false;
       }
  }
  
  /* Il metodo mostra il contenuto del carrello*/
  public function BM_WS_showCart($lang){
      try{
       $result = $this->client->checkOutCart(array('lang' => $lang));
       return $result->YourCart;
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  /* Il metodo rimuove un oggetto dal carrello*/
  public function BM_WS_removeItem($itemId, $client_id){
      try{
       $result = $this->client->deleteCartItem(array('itemid' => $itemId, 'client_identifier'=>$client_id));
       return $result->OperationResult;
      }
      catch (Exception $e) {
          return false;
      }
  }
  
  public function BM_WS_confirm_order($client_id, $rsoc, $city, $province, $nation, $custom_param1='', $custom_param2=''){
      try{
       $result = $this->client->confirmOrder(array('buyer_is_client'=>'Y', 'warranty_extension'=>'Y','client_identifier'=>$client_id,'client_rsoc'=>$rsoc,'city'=>$city, 'province'=>$province, 'nation'=>$nation, 'custom_param1'=>$custom_param1, 'custom_param2'=>$custom_param2));
       return $result->OperationResult;
      }
      catch (Exception $e) {
          return false;
      }
  }
  
}

?>