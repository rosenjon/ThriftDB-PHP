<?php
//Copyright (c) 2012 Jonathan Rosen

include('thrift_db.php');

	$thriftdb_username = '';
	$thriftdb_password = '';

	$bucketname = 'locationdata';
	$cname = 'cities';


	//create a few test objects
	$testObject = new stdClass;
	$testObject->{'_id'} = 'SEA';
	$testObject->location = new stdClass;
	$testObject->location->name = 'Seattle, WA';
	$testObject->location->coords = new stdClass;
	$testObject->location->coords->lat = 47.6097;
	$testObject->location->coords->long = 122.3331;
	$testObject->location->population = 620778;	

	
	$testObject2 = new stdClass;
	$testObject2->{'_id'} = 'CHI';
	$testObject2->location = new stdClass;
	$testObject2->location->name = 'Chicago, IL';
	$testObject2->location->coords = new stdClass;
	$testObject2->location->coords->lat = 41.8500;
	$testObject2->location->coords->long = 87.6500;
	$testObject2->location->population = 2707120;	
	
	//set a couple query values for getItem and searchItems
	$itemid = 'SEA';
	$queryString = 'q=Seattle';

	//create an instance of the ThriftDB class
	$thriftDB = new ThriftDB($thriftdb_username, $thriftdb_password);
	
	//create a bucket
	$result = $thriftDB->createBucket($bucketname);
	
	//navigate the object and generate a schema
	$schema = $thriftDB->createDataSchema($testObject);

	//create/update a collection based on the generated schema
	$result = $thriftDB->createCollection($bucketname, $cname, $schema);
	echo '<pre>'; var_dump($result); echo '</pre>';

	//commit multiple cities to the db
		//put items in an array for sending to bulk item add
		$item_array = array($testObject, $testObject2);
	$result = $thriftDB->commitMItemsToDB($bucketname, $cname, $item_array);

	//get individual item by id
	$result = $thriftDB->getItem($bucketname, $cname, $itemid);
	echo '<pre>'; var_dump($result); echo '</pre>';


/*	


	$result = $thriftDB->createCollection($bucketname, $cname, $schema);

	$result = $thriftDB->commitMItemsToDB($bucketname, $cname, $item_array);

	$result = $thriftDB->commitItemToDB($bucketname, $cname, $itemid, $product);

	$result = $thriftDB->searchItems($bucketname, $cname, $queryString);

	$result = $thriftDB->reindexCollection($bucketname, $cname);

*/

?>