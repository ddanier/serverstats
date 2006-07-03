<?php

class xmlconfig {

   public static function read ($xml) {
	$result = array();
        xmlconfig::__traverseParams ($xml->documentElement, $result );
	return $result; 
   }

   private static function __traverseParams($node, &$config) {
  	foreach ($node->childNodes as $child) {
  		if ($child->nodeType!=1) continue;
  			
  			switch ($child->nodeName) {
  				case 'graph':
						self::__traverseParams($child, $config[ $host ]['graphs'][], $host);		
						break;
					case 'source':
						$id = $child->getAttribute('id');
						$class = $child->getAttribute('type');
						$args = null;
						
						self::__traverseParams($child, $args);		
											
						if (class_exists($class)) {
							/*$args['host'] = $host;*/
							$config[ $id ] = call_user_func(array($class,'factory'), $args); 				
						}
						
												
						break;
					case 'param':							
						$name = $child->getAttribute('name');
						if ($name=='') $name = count($config);
						
					if ($child->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance','type')=='xsd:array') {
							self::__traverseParams($child, $config[ $name ]);
						} else {
							$config[ $name ] = $child->nodeValue;
							self::__traverseParams($child, $config);

						}
						break;

					default:
						print ($child->nodeName);
						self::__traverseParams($child, $config);		
						break;
						
  			}
  	}

	}


public static function sources(&$config, $xmlconfig)
        {
                if (!$xmlconfig['used'])
                {
                        return;
                }

		
		foreach ($xmlconfig['modules'] as $module => $modconf)
		{
			$config[ $module ]['module'] = $modconf;
			//print_r ($modconf);

		}
	}

	
}

?>
