# LEX API: Search & Filter

Returns a JSON Array containing the results of the search query

	GET /api/<version>/search

## Authentication
None

## Parameters

### Main parameters
| Parameter | Type | Possibilities | Sort/Get |
| --------- | ---- | --- | --- |
start | Integer | N/A | start at this result
amount | Integer | N/A | show this many results
| order_by | String | <br>download<br>popular<br>update<br>recent | **order by:**<br>number of downloads<br>number of downloads<br>update date<br>release date
order | String | <br>asc<br>desc | **sort:**<br>ascending<br>descending
concise | Boolean | <br>true<br>false | **return:**<br>only lotname/lotid<br>everything
dependencies | String | <br>full<br>concise | **return:**<br>full dependency list<br>concise dependency list


### Filtering parameters
| Parameter | Type | Possibilities | Filter |
| --- | --- | --- | --- |
creator | Integer | N/A | **userid** of the lot/file author
broad_category | String | <br>250_MX_Agric.gif<br>250_MX_Civic.gif<br>250_MX_Comm.gif<br>250_MX_Ind.gif<br>250_MX_Landmark.gif<br>250_MX_Parks.gif<br>250_MX_Res.gif<br>250_MX_Reward.gif<br>250_MX_Transport.gif<br>250_MX_Utility.gif<br>250_MX_Dependency.gif<br>250_MX_Maps.gif<br>250_MX_Modd.gif<br>250_MX_Tools.gif<br>250_MX_WFK-Canals.gif<br>250_MX_Military.gif<br>250_MX_FilesDocs.gif | **broad category** of the lot/file<br>Agriculture<br>Civic<br>Commercial<br>Industrial<br>Landmark<br>Park<br>Residential<br>Reward<br>Transportation<br>Utility<br>Dependency<br>Map<br>Modd<br>Tool<br>WFK & Canals<br>Military<br>Files & Documentation
lex_category | Integer | N/A | **catid** of the lot/file LEX category
lex_type | Integer | N/A | **typeid** of the lot/file LEX type
broad_type | String | <br>lotbat<br>dependency<br>map<br>mod<br>other | **broad type** of the lot<br>Lots & BATs<br>Dependencies<br>Maps<br>Mods<br>Files, Tools, etc.
group | Integer | N/A | **groupid** of the lot/file LEX group
query | String | N/A | (part of) the name of the file to search for
exclude_notcert | Boolean | <br>true<br>false | **exclude** files that are **not LEX Certified** from the results<br>yes<br>no
exclude_locked | Boolean | <br>true<br>false | **exclude** files that are **locked** from the results<br>yes<br>no

## Response
- 400 Bad Request: Bad criteria, no criteria
- 404 Not Found: no results for your query
- 200 OK: list of results

### Concise
```javascript
[
   {
      "id":2947,
      "name":"Diggis Streams Grass Base Set BSC"
   },
   {
      "id":2939,
      "name":"JENXFAUNA Crocodiles"
   }
]
```

### All
```javascript
[
   {
      "id":2947,
      "name":"Diggis Streams Grass Base Set BSC",
      "version":"1.0",
      "num_downloads":730,
      "author":"diggis",
      "is_exclusive":true,
      "maxis_category":"250_MXC_WFK-Canals.gif",
      "description":"This is the second of a series of ploppable Stream lots to match in with the SG\/CAL Canals water texture.",
      "images":{
         "primary":"http:\/\/sc4devotion.com\/csxlex\/images\/beximg\/thumbs\/Diggis Streams Grass Base Set BSC.jpg",
         "secondary":"http:\/\/sc4devotion.com\/csxlex\/images\/beximg\/DiggisStreamsGrassBaseSet_Image2.jpg",
         "extra":"http:\/\/sc4devotion.com\/csxlex\/images\/beximg\/DiggisStreamsGrassBaseSet_Extra.jpg"
      },
      "link":"http:\/\/sc4devotion.com\/csxlex\/lex_filedesc.php?lotGET=2947",
      "is_certified":true,
      "is_active":true,
      "upload_date":"20130302",
      "update_date":null
   },
   {
      "id":2939,
      "name":"JENXFAUNA Crocodiles",
      "version":"1.0",
      "num_downloads":297,
      "author":"xannepan",
      "is_exclusive":false,
      "maxis_category":"250_MXC_Modd.gif",
      "description":"This mod adds crocodiles to the game, as ploppable LOT (park menu) and an animal brush (god menu).\r\nIt will take some time before the crocodiles appear on the lot. Hold down the shift key when using the brush to create more crocodiles.\r\nBoth the lot and the brush can be used on land and on water.\r\n\r\nINSTALLATION\r\nUnzip this file in your plugins folder or (preferably) a subfolder.\r\n\r\nDEPENDENCIES\r\nNone",
      "images":{
         "primary":"http:\/\/sc4devotion.com\/csxlex\/images\/beximg\/thumbs\/JENXFAUNA_Crocodiles_v1.jpg"
      },
      "link":"http:\/\/sc4devotion.com\/csxlex\/lex_filedesc.php?lotGET=2939",
      "is_certified":true,
      "is_active":true,
      "upload_date":"20130223",
      "update_date":null
   }
]
```
