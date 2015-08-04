# LEX API: Search & Filter

Returns a JSON Array containing the results of the search query

	GET /api/<version>/search

## Authentication
None

## Parameters

### Main parameters

These parameters put limitations on the result, but do not filter certain files. Using only these parameters (and no filtering parameters) will result in a HTTP 400 - Bad Request.

| Parameter | Type | Possibilities | Sort/Get |
| --------- | ---- | --- | --- |
start | Integer | N/A | start at this result
amount | Integer | N/A | show this many results
order | String | <br>asc<br>desc | **sort:**<br>ascending<br>descending
concise | Boolean | <br>true<br>false | **return:**<br>only lotname/lotid<br>everything
user | Boolean | set/not set | extra information relevant to the user (last downloaded)
dependencies | Boolean | set/not set | show dependency information for each result
comments | Boolean | set/not set | add list of comments to each result
votes | Boolean | set/not set | add the vote overview to each result


### Filtering parameters
| Parameter | Type | Possibilities | Filter |
| --- | --- | --- | --- |
creator | Integer | N/A | **userid** of the lot/file author
broad_category | String | <br>250_MX_Agric.gif<br>250_MX_Civic.gif<br>250_MX_Comm.gif<br>250_MX_Ind.gif<br>250_MX_Landmark.gif<br>250_MX_Parks.gif<br>250_MX_Res.gif<br>250_MX_Reward.gif<br>250_MX_Transport.gif<br>250_MX_Utility.gif<br>250_MX_Dependency.gif<br>250_MX_Maps.gif<br>250_MX_Modd.gif<br>250_MX_Tools.gif<br>250_MX_WFK-Canals.gif<br>250_MX_Military.gif<br>250_MX_FilesDocs.gif | **broad category** of the lot/file<br>Agriculture<br>Civic<br>Commercial<br>Industrial<br>Landmark<br>Park<br>Residential<br>Reward<br>Transportation<br>Utility<br>Dependency<br>Map<br>Modd<br>Tool<br>WFK & Canals<br>Military<br>Files & Documentation
lex_category | Integer | N/A | **catid** of the lot/file LEX category
lex_type | Integer | N/A | **typeid** of the lot/file LEX type
broad_type | String | <br>lotbat<br>dependency<br>map<br>mod<br>other | **broad type** of the lot<br>Lots & BATs<br>Dependencies<br>Maps<br>Mods<br>Files, Tools, etc.
group | Integer | N/A | **groupid** of the lot/file LEX group
| order_by | String | <br>download<br>popular<br>update<br>recent | **order by:**<br>number of downloads<br>number of downloads<br>update date<br>release date
query | String | N/A | (part of) the name of the file to search for
exclude_notcert | Boolean | <br>true<br>false | **exclude** files that are **not LEX Certified** from the results<br>yes<br>no
exclude_locked | Boolean | <br>true<br>false | **exclude** files that are **locked** from the results<br>yes<br>no

## Response
- 400 Bad Request: Bad criteria, no criteria
- 200 OK: list of results

### No results
```
[]
```

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
    "id": 2423,
    "name": "BLS Farm - Porkies",
    "version": "1.0",
    "num_downloads": 1759,
    "author": "barbyw",
    "is_exclusive": false,
    "description": "The Real Porkies farm is owned by Frankie who was the goalie for the local football team. He was not a very good goalie and rather ungainly so was affectionately call the Flying Pig by his friends. He raises the finest pure bred Gloucestershire Old Spots for the local market and restaurant trade. He is also a producer of fine smoked hams and bacon and supplies the local baker with meat for Porkie Pies. Included in the download is a field which reflects Frankie's ethical attitude to looking after his pigs. They have open air pens with shelters and are not factory farmed at all.Dependencies \r\n\r\n\t \r\n\t\r\n\t\tBSCEssentials\r\n\t\r\n\t \r\n\t\r\n\t\tBSCMEGA Props - SG Vol 01\r\n\t\r\n\t \r\n\t\r\n\t\tBSCMEGA Props - MJB Vol02\r\n\t\r\n\t \r\n\t\r\n\t\tBSCMEGA Props - NewmanInc Vol01\r\n\t\r\n\t \r\n\t\r\n\t\tCSXMEGA Props - Vol01\r\n\t\r\n\t \r\n\t\r\n\t\tPigFamily.dat - included\r\n\t\r\n\t \r\n\t\r\n\t\tDD_PigPen.dat - included \r\n\t\r\n\t \r\n\t\r\n\t\tBSC_TexturePack_Cycledogg_V01\r\n\t\r\n",
    "images": {
      "primary": "http:\/\/mydomain.com\/file_exchange\/images\/porkies_s.jpg",
      "secondary": "http:\/\/mydomain.com\/file_exchange\/images\/porkies_sn.jpg"
    },
    "link": "http:\/\/mydomain.com\/file_exchange\/lex_filedesc.php?lotGET=2423",
    "is_certified": true,
    "is_active": true,
    "upload_date": "2010-07-06T00:00:00+0000",
    "update_date": "2010-07-08T00:00:00+0000",
    "filesize": "0.00",
    "comments": [
      {
        "id": 31575,
        "user": "akirmira",
        "text": "thx ",
        "date": "2012-11-18T00:00:00+0000",
        "by_author": false,
        "by_admin": false
      },
      {
        "id": 25468,
        "user": "jeffabes",
        "text": "Is this SPAMpatible?",
        "date": "2011-07-20T00:00:00+0000",
        "by_author": false,
        "by_admin": false
      }
    ],
    "votes": {
      "1": 0,
      "2": 0,
      "3": 0
    },
    "dependencies": {
      "status": "ok",
      "count": 6,
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
        },
        {
          "internal": true,
          "id": 746,
          "name": "BSC MEGA Props - SG Vol 01",
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
  },
  {
    "id": 2327,
    "name": "BLS Centre Georges Pompidou BSC",
    "version": "1.0",
    "num_downloads": 3403,
    "author": "barbyw",
    "is_exclusive": false,
    "description": "This strange looking inside out building is a museum in Paris built in the 1970s. It contrasts strongly with the surrounding Hausmann style buildings. This version is a very expensive item for your city so please only use it if you have unlimited funding.The original BAT is by choucass but the model is not included in this upload. The link to the model is in the Dependency List. Dependencies: \r\n\r\n\tBeauborg- essential to avoid an enormous brown box\r\n\r\n \r\n\r\n\tBSCEssentials\r\n\r\n \r\n\r\n\tPorkieProps_Vol01\r\n\r\n \r\n\r\n\tPorkieProps_Vol02\r\n\r\n \r\n\r\n\tBSCMEGA Props DBSSYMN Vol02\r\n\r\n \r\n\r\n\tBgCafe - included\r\n\r\n \r\n\r\n\tOrangeTSCMegaProp V.01\r\n\r\n \r\n\r\n\tBSCMEGA Props - RT Vol01\r\n",
    "images": {
      "primary": "http:\/\/mydomain.com\/file_exchange\/images\/centrepompidou_red.jpg",
      "secondary": "http:\/\/mydomain.com\/file_exchange\/images\/centrepompidou_ren.jpg"
    },
    "link": "http:\/\/mydomain.com\/file_exchange\/lex_filedesc.php?lotGET=2327",
    "is_certified": true,
    "is_active": true,
    "upload_date": "2010-01-11T00:00:00+0000",
    "update_date": null,
    "filesize": "0.00",
    "comments": [
      {
        "id": 23321,
        "user": "sejr99999",
        "text": "thank you  it would be impossible to create a SimParis without this incredible bldg",
        "date": "2011-02-06T00:00:00+0000",
        "by_author": false,
        "by_admin": false
      },
      {
        "id": 22116,
        "user": "erazer32",
        "text": "that looks really great, thx a lot! :))",
        "date": "2010-11-26T00:00:00+0000",
        "by_author": false,
        "by_admin": false
      }
    ],
    "votes": {
      "1": 0,
      "2": 0,
      "3": 0
    },
    "dependencies": {
      "status": "ok",
      "count": 7,
      "list": [
        {
          "internal": false,
          "link": "http:\/\/www.toutsimcities.com\/downloads.php?view=1510",
          "name": "Beaubourg"
        },
        {
          "internal": true,
          "id": 398,
          "name": "BSC MEGA Props - RT Vol01",
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
]
```