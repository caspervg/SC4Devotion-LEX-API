# LEX API: Lot/File

## Lot Details

Returns a JSON Object containing all the information about one lot/file

	GET /api/<version>/lot/:lotid:

### Authentication
No

### Parameters
| Parameter | Type | Possibilities | Explanation |
| --------- | ---- | --- | --- |
user | Boolean | set/not set | extra information relevant to the user (last downloaded)
dependencies | Boolean | set/not set | show dependency information
comments | Boolean | set/not set | add list of comments to the result
votes | Boolean | set/not set | add the vote overview to the result

### Response
- 404 Not Found: no (active) lot with that id
- 200 OK: lot/file information

```javascript
{
  "id": 10,
  "name": "Praiodan Central Subway Station BSC",
  "version": "1.0",
  "num_downloads": 5802,
  "author": "praiodan",
  "is_exclusive": false,
  "description": "You've been looking for a central subway station? You've been looking for one that is representative and fits into your old town district? Now you found it! This neo-classical building was mostly inspired by the Berlin' subway station at Wittenbergplatz (Wittenberg Square) which is just opposite of Berlin's most famous department store, the KaDeWe. This is a remake of one of my older Lots. All lot details and a linked dependency list are in the Readme. UPDATE 8.February 2007: Updated the ZIP-included ReadMe.\r\n\r\n\t\r\n\r\n\r\n\tUpdate 16 November 2014: Updated dependency links in the Dependency Tracker\r\n",
  "images": {
    "primary": "http:\/\/mydomain.com\/file_exchange\/images\/centralsubway_s.jpg",
    "secondary": "http:\/\/mydomain.com\/file_exchange\/images\/centralsubway_sn.jpg"
  },
  "link": "http:\/\/mydomain.com\/file_exchange\/lex_filedesc.php?lotGET=10",
  "is_certified": true,
  "is_active": true,
  "upload_date": "2007-01-12T00:00:00+0000",
  "update_date": "2007-02-08T00:00:00+0000",
  "filesize": "0.00",
  "comments": [
    {
      "id": 35371,
      "user": "nightshadow666",
      "text": "Sehr sch\u00f6ne Arbeit! Passt perfekt f\u00fcr mein Berlin Projekt!!!",
      "date": "2014-06-20T00:00:00+0000",
      "by_author": false,
      "by_admin": false
    },
    {
      "id": 27,
      "user": "blackbeard",
      "text": "Awesome thanx for sharing.",
      "date": "2007-01-12T00:00:00+0000",
      "by_author": false,
      "by_admin": false
    }
  ],
  "votes": {
    "1": 5,
    "2": 1,
    "3": 0
  },
  "dependencies": {
    "status": "ok",
    "count": 1,
    "list": [
      {
        "internal": true,
        "id": 443,
        "name": "BSC Essentials",
        "status": {
          "ok": true,
          "deleted": false,
          "superseded": false,
          "superseded_by": -1,
          "locked": false
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
- 404 Not Found: no (active) lot with that id
- 200 OK: list of comments

```javascript
[
  {
    "id": 35371,
    "user": "nightshadow666",
    "text": "Sehr sch\u00f6ne Arbeit! Passt perfekt f\u00fcr mein Berlin Projekt!!!",
    "date": "2014-06-20T00:00:00+0000",
    "by_author": false,
    "by_admin": false
  },
  {
    "id": 28054,
    "user": "sejr99999",
    "text": "thank you   looks great",
    "date": "2011-12-19T00:00:00+0000",
    "by_author": false,
    "by_admin": false
  },
  {
    "id": 26435,
    "user": "RudeLittleDude",
    "text": "Nice station but, the link to the dependency needs a fix.",
    "date": "2011-09-22T00:00:00+0000",
    "by_author": false,
    "by_admin": false
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
           "superseded":false,
           "superseded_by":-1,
           "locked":false
        }
     }
  ]
}
```
