<?php
//Copyright (c) 2012 Jonathan Rosen
//requires php >= v5.4
class ThriftDB
{
		private $username;
		private $password;
		
		function __construct($username, $password) {
			$this->username = $username;
			$this->password = $password;
		
		}

		function createAttribute($index, $type, $is_searchable = true) {
			$intType = new stdClass;
			$intType->{'__class__'} = "IntegerType";
			
			$doubleType = new stdClass;
			$doubleType->{'__class__'} = "DoubleType";
			
			$boolType = new stdClass;
			$boolType->{'__class__'} = "BoolType";
			
			$dateTimeType = new stdClass;
			$dateTimeType->{'__class__'} = "DateTimeType";
			
			$stringType = new stdClass;
			$stringType->{'__class__'} = "StringType";
			
			$textType = new stdClass;
			$textType->{'__class__'} = "TextType";
			
			$structSchemaType = new stdClass;
			$structSchemaType->{'__class__'} = "StructSchema";
			
			$listType = new stdClass;
			$listType->{'__class__'} = "ListType";
			
			$object = new stdClass;
			$object->{'__class__'} = "AttributeDescriptor";	
			
			if($is_searchable == true) {
				$object->{'is_searchable'} = true;
		
			} else {
				$object->{'is_searchable'} = false;
			}
			switch($type) {
				case "integer":
					$object->datatype = $intType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "double":
					$object->datatype = $doubleType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "boolean":
					$object->datatype = $boolType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "datetime":
					$object->datatype = $dateTimeType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "string":
					$object->datatype = $stringType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "text":
					$object->datatype = $textType;
					$object->{'thrift_index'} = $index;
		
				break;
				
				case "object":
					$object->datatype = $structSchemaType;
		
				break;
				
				case "array:":
					$object->datatype = $listType;	
				break;
				
				default:
					$object->datatype = $stringType;
					$object->{'thrift_index'} = $index;
		
				break;
			
			}
			
			return $object;
		
		}
		
		function createStruct($structSchema) {
			
			$structType = new stdClass;
			$structType->{'__class__'} = "StructType";
			$structType->schema = $structSchema;
			
			return $structType;
		
			
		}
		
		function createDataSchema ($obj) {
			$topObject = new stdClass;
			$topObject->{'__class__'} = 'StructSchema';
			$counter = 0;
			//simplify php structure to simple json form
			$objArray = new stdClass;
			$objArray->top = $obj;

			$cl = function($count, $object) {
				$countNow = function($c) {return $c;};
				return $this->createAttribute($countNow($count), gettype($object), true);
			};


			$closure = Closure::bind($cl, $this, 'static');
			
			$objEncoded = json_decode(json_encode($objArray, JSON_FORCE_OBJECT));
			foreach($objEncoded as $k=>$v) {
				//callback function to assign attributes by recursively walking the object with walk_recursive function

				
				$encodedObjectSchema = $this->walk_recursive($v, $closure, $counter);
				$topObject->$k = $encodedObjectSchema;	
			}
			
			$finalschema = $topObject->top->datatype->schema;

			return $finalschema;
		
		}
		
		function createBucket($bucketname) {
		
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s", $this->username, $this->password, $bucketname); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			}
		
		
		}
		
		function createCollection($bucketname, $cname, $schema) {
			$schema_data = json_encode($schema);
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s", $this->username, $this->password, $bucketname, $cname); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS,$schema_data);
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			}
		
		}
		
		function commitMItemsToDB($bucketname, $cname, $array_data) {
		
			//expects array of objects here ... don't JSON_FORCE_OBJECT
			$json_encoded = json_encode($array_data);
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s/_bulk/put_multi", $this->username, $this->password, $bucketname, $cname); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_encoded);
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			}
		
		
		}
		
		function commitItemToDB($bucketname, $cname, $itemid, $item) {
		
			$json_encoded = json_encode($item, JSON_FORCE_OBJECT);
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s/%s", $this->username, $this->password, $bucketname, $cname, $itemid); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_encoded);
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			}
			
			
		}
		
		function getItem($bucketname, $cname, $itemid) {
		
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s/%s", $this->username, $this->password, $bucketname, $cname, $itemid); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
			
				return json_decode($response);
			
			}
				
		}
		
		function searchItems($bucketname, $cname, $queryString) {
		
			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s/_search?%s", $this->username, $this->password, $bucketname, $cname, $queryString); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			
			}
				
		}
		
		function reindexCollection($bucketname, $cname) {

			$url = sprintf("http://%s:%s@api.thriftdb.com/%s/%s/_bulk/reindex", $this->username, $this->password, $bucketname, $cname); 
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			
			$response = curl_exec($ch);
			if(!$response) {
				return false;
			} else {
				return json_decode($response);
			
			}		
		
		}
		
		
		function walk_recursive($obj, $closure, &$counter) {
			
			$mycount = function($c) { return $c;};
		
			if ( is_object($obj) ) {
				$newObjSchema = new stdClass;
				$newObjSchema->{'__class__'} = "StructSchema";
				
				foreach ($obj as $property => $value) {
					$newObjSchema->$property = $this->walk_recursive($value, $closure, $counter);
					
				}
				$counter++;
				$topObj = new stdClass;
				$topObj->{'thrift_index'} = $mycount($counter);
				$topObj->{'__class__'} = "AttributeDescriptor";
				$topObj->datatype = $this->createStruct($newObjSchema);
		
					return $topObj;
				} else if ( is_array($obj) ) {
					$newArrSchema->{'__class__'} = "StructSchema";
					foreach ($obj as $key => $value) {
						$newArray[$key] = walk_recursive($value, $closure, $counter);
						
					}
					$counter++;
					$arrObj->{'thrift_index'} = $mycount($counter);
					$arrObj->{'__class__'} = "AttributeDescriptor";
					$arrObj->datatype = createStruct($newArrSchema);
					return $newArray;
		
				} else {
					$counter++;
					return $closure($mycount($counter), $obj);
				}
		}

}

?>