<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
class bm_ws_translate{
  public $current_language;
  public $default_language;
  public $csv='';
  public $data=array();
  
  public function __construct($curent_langauge,$default_lang,$path){
	  $this->curent_langauge=$curent_langauge;
	  $this->default_language=$default_lang;
	  $this->csv=$path.'lang/bm_ws_'.$this->curent_langauge.'.csv';
	  $this->prepareData();
  }
  
  private function prepareData(){
	  if($this->curent_langauge==$this->default_language){
		  return FALSE;
	  }
	   
	  if(!file_exists($this->csv)){
	     
		  return FALSE;
	   }
		$fh = @fopen($this->csv, "r");
		if (!$fh) {
          return FALSE;
		}
		while (!feof($fh)) {
			  $line =explode('","',fgets($fh));
			  if(strlen(trim($line[0]))>0){
			    $this->data[str_replace('"','',$line[0])]=str_replace('"','',$line[1]);
			  }
			  
		}
		
		fclose($fh);
 }
 
 public function _print($word){
	 if(is_array($this->data) && array_key_exists($word , $this->data)){
		 return trim($this->data[$word]);
	 }
	 else{
		  return $word;
	 }
 }
 
 
}

?>