<?php

class snmp  {
	 public function __construct($host, $objects, $comunity='public') {
   }
   
   static public function factory($args) { 
   	
   	  $objects = array();
   	  
   	  foreach ($args as $name=>$value ) {
   	  	switch ($name) {
   	  		case 'host': 
   	  			$host = $value;
   	  			break;
   	  		case 'comunity':
   	  			$comunity = $value;		
   	  			break;
   	  		default:
   	  			$objects[$name] = $value;
   	  	}
   	  }
   	  
   		return new snmp($host, $objects, $comunity);
   }
}


class mem {
	 public function __construct() {
	 		print('mem');
   }

   static public function factory() { 
   		return new mem();
   }
}


class cpu  {
	 public function __construct() {
	 	print('cpu');
   }

   static public function factory() { 
   		return new cpu();
   }
 
}

class xmlconfig extends domDocument {

   public function __construct() {
           parent::__construct();
   }
   
   public function __destruct() {
           
   }

   private function __traverseParams($node, &$config, &$host='') {
  	
  	foreach ($node->childNodes as $child) {
  		if ($child->nodeType!=1) continue;
  			
  			switch ($child->nodeName) {
  				case 'host':
  					$this->__traverseParams($child, $config, $child->getAttribute('id') );		
  					break;
  				case 'graph':
						$this->__traverseParams($child, $config[ $host ]['graphs'][], $host);		
						break;
					case 'source':
						$class = $child->getAttribute('type');
						$args = array();
						
						$this->__traverseParams($child, $args, $host);		
											
						if (class_exists($class)) {
							$args['host'] = $host;
							$config [ $host ]['module'][] = call_user_func(array($class,'factory'), $args); 				
						}
						
												
						break;
					case 'param':							
						$name = $child->getAttribute('name');
						if ($name=='') $name = count($config);
						
						if ($child->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance','type')=='xsd:array') {
							$this->__traverseParams($child, $config[ $name ], $host);
						} else {
							$config[ $name ] = $child->nodeValue;
							$this->__traverseParams($child, $config, $host);
						}
						break;

					default:
						/*print $child->nodeName;*/
						$this->__traverseParams($child, $config, $host);		
						break;
						
  			}
  	}

	}


  public function parse() {
 		 $result = array(); 	
 		  
 		 $this->__traverseParams ($this->documentElement, $result );
     return $result;
  }

}

?>