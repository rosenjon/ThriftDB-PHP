This is a convenience class to access ThriftDB using PHP. 


It provides convenience methods to:
1) generate a ThriftDB schema based on an existing PHP object
2) Create ThriftDB buckets
3) Create ThriftDB collections using the schema generated in step 1
4) retrieve items from ThriftDB by item id
5) retrieve items from ThriftDB using search
--more information about the ThriftDB search syntax can be found here: http://www.thriftdb.com/documentation/rest-api/search-api


Some things that are currently missing:
1) ThriftDB lists require that all objects be of the same type. This creates a mismatch with PHP arrays, since PHP arrays can contain different object types.
Therefore, all arrays are converted to the StructType in ThriftDB, with non-associative arrays assigned integer keys based on the position of the object in the array.
In future updates, a list type could be created, with a method to type check that all list members are of the same type.

2) Date type in PHP has not been converted to its ThriftDB equivalent yet. This has been stubbed out in code, but not implemented yet.

3) If you remove properties from an object in php, then regenerata a schema against that object and push the collection to ThriftDB, it appears that the new schema upload will delete those properties in ThriftDB, so be careful. (This behavior could also be desired, just watch out for data loss).

4) Please add to this project as you see fit to improve it. ThriftDB is pretty cool for semi-structured data.

5) This version requires PHP 5.4 or greater. This has to do with PHP 5.3 stripping out references to $this within closures, and then adding methods in 5.4 to deal with this.
I have some non-OO structured code that works with <= PHP 5.3 if people request that.

6) This code is licensed under the MIT License, which is attached.