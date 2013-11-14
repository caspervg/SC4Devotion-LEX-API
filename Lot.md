# LEX API: Lot/File

## Lot Details

Returns a JSON Object containing all the information about one lot/file

	GET /api/<version>/lot/:lotid:

### Authentication
No

### Parameters
None

### Response
- 404 Not Found: no (active) lot with that id
- 200 OK: lot/file information

```javascript
{
   "id":950,
   "name":"CAM Commercial Offices BSC",
   "version":"1.0",
   "num_downloads":38532,
   "author":"barbyw",
   "is_exclusive":true,
   "maxis_category":"250_MX_Comm.gif",
   "description":"Updated 23rd August to correct an OG error on the CO$$$ Stage 13\/14 set. There is a separate update if you have already downloaded this set.In the zip there are 10 individual zips containing a total of 53 lots. These cover CO$$ and CO$$$ and each individual installer has a loose Readme. Each Readme has details of the stats and Dependencies for the lots in the installer.\n These will not grow without the CAM which will be released in about 10 to 14 days.",
   "images":{
      "primary":"images\/beximg\/thumbs\/camCO$$.jpg"
   },
   "link":"lex_filedesc.php?lotGET=950",
   "is_certified":true,
   "is_active":true,
   "upload_date":"20070706",
   "update_date":"20070825",
   "dependencies":{
      "status":"ok",
      "count":2,
      "list":[
         {
            "internal":false,
            "link":"http:\/\/www.simtropolis.com\/forum\/files\/file\/11421-porkie-props-vol1-european-street-accessories\/",
            "name":"Porkie Props Vol 01"
         },
         {
            "internal":true,
            "id":1263,
            "name":"BSC Mega Props - JES Vol05",
            "status":{
               "ok":true,
               "deleted":false,
               "superceded":false,
               "superceded_by":-1,
               "locked":false
            }
         }
      ]
   }
}
```

## All Lots

Returns a JSON Array containing IDs and Names of all active lots

	GET /api/<version>/lot/all

### Authentication
No

### Parameters
None

### Response
- 200 OK: list of lots

```javascript
[
   {
      "id":2,
      "name":"CSX Farm SF - Veronique"
   },
   {
      "id":3,
      "name":"BLS Farm Jacky's Coach House Farm"
   },
   {
      "id":5,
      "name":"BRT Coal Mine BSC"
   },
   {
      "id":6,
      "name":"CSX Civic - National Library"
   },
   {
      "id":7,
      "name":"MBEAR Palazzo Bufalini BSC"
   }
]
```

## Download Lot/File

Returns a ZIP file containing the requested file, if the user has not gone beyond his daily download limits

	GET /api/<version>/lot/:lotid:/download

### Authentication
Yes, Basic HTTP

### Parameters
None

### Response
- 404 Not Found: no (active) lot with that id
- 401 Unauthorized: authentication failed
- 429 Too Many Requests: user has reached his/her daily download limit
- 200 OK: requested lot as a ZIP-file

## Add Lot to Download List

Adds the requested lot to the user's "download later" list

	GET /api/<version>/lot/:lotid:/download-list

### Authentication
Yes, Basic HTTP

### Parameters
None

### Response
- 403 Forbidden: requested lot/file is already on the user's download list or it does not exist
- 200 OK: file has been added to the user's download list

## Get Lot/File Comments

Returns a JSON Array containing a list of all comments for the request lot/file

	GET /api/<version>/lot/:lotid:/comment

### Authentication
No

### Parameters
None

### Response
- 404 Not Found: no (active) lot wit that id
- 200 OK: list of comments

```javascript
[
   {
      "id":23597,
      "user":"test_admin1",
      "text":"thank you  beautiful work and another reward challenge",
      "date":"20110227",
      "by_author":false,
      "by_admin":true
   },
   {
      "id":864,
      "user":"test_user2",
      "text":"Thanks everyone for the supportive comments.",
      "date":"20070215",
      "by_author":true,
      "by_admin":false
   },
   {
      "id":163,
      "user":"ifyoureadthis_youareamazing",
      "text":"Another beautiful BSC reward!",
      "date":"20070117",
      "by_author":false,
      "by_admin":false
   },
   {
      "id":65,
      "user":"test_user4",
      "text":"I like this one very much. ALso like the readme to be visible in the description area! ",
      "date":"20130114",
      "by_author":false,
      "by_admin":false
   }
]
```

## Post New Comment

Adds a new comment and/or rating to the requested lot

	POST /api/<version>/lot/:lotid:/comment

### Authentication
Yes, Basic HTTP

### Parameters
- rating: Integer - rating for the file. should be >= 1 and <= 3, small is better
- comment: String/Blob - comment for the file

### Response
- 401 Unauthorized: authentication failed
- 404 Not Found: no (active) file with that id
- 400 Bad Request: rating was < 1 or > 3
- 200 OK: what was added

```javascript
[
   "comment",
   "rating"
]
```

## Lot/File Dependencies

Retrieves a JSON Object with the list of dependencies (both internal and external) for the requested lot/file

	GET /api/<version>/lot/:lotid:/dependency

### Authentication
No

### Parameters
None

### Response
- 404 Not Found: no (active) file with that id
- 200 OK: requested file **does not support** the dependency tracker

```javascript
{
   "status":"not-available",
   "count":-1,
   "list":null
}
```

200 OK: requested file **does support** the dependency tracker

```javascript
{
  "status":"ok",
  "count":2,
  "list":[
     {
        "internal":false,
        "link":"http:\/\/www.simtropolis.com\/forum\/files\/file\/11421-porkie-props-vol1-european-street-accessories\/",
        "name":"Porkie Props Vol 01"
     },
     {
        "internal":true,
        "id":1263,
        "name":"BSC Mega Props - JES Vol05",
        "status":{
           "ok":true,
           "deleted":false,
           "superceded":false,
           "superceded_by":-1,
           "locked":false
        }
     }
  ]
}
```
