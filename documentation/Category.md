# LEX API: Category

## LEX Categories
Returns a JSON Array containing all the LEX categories

	GET /api/<version>/category/lex-category

### Authentication
No

### Parameters
None

### Response
- 200 OK: list of LEX categories

```javascript

[
   {
      "id":66,
      "name":"00 Locked"
   },
   {
      "id":65,
      "name":"00 Outdated"
   },
   {
      "id":35,
      "name":"Utilities - Power"
   },
   {
      "id":34,
      "name":"Utilities - Water"
   }
]
```

## Broad Categories
Returns a JSON Array containing all the broad categories

	GET /api/<version>/category/broad-category

### Authentication
No

### Parameters
None

### Response
- 200 OK: list of broad categories

```javascript
[
   {
      "id":1,
      "name":"Agriculture",
      "image":"250_MX_Agric.gif"
   },
   {
      "id":2,
      "name":"Civic",
      "image":"250_MX_Civic.gif"
   },
   {
      "id":10,
      "name":"Utilities",
      "image":"250_MX_Utility.gif"
   },
   {
      "id":15,
      "name":"WFK - Canals",
      "image":"250_MXC_WFK-Canals.gif"
   }
]
```

## LEX Types
Returns a JSON Array containing all the LEX types

	GET /api/<version>/category/lex-type

### Authentication
No

### Parameters
None

### Response
- 200 OK: list of LEX types

```javascript
[
   {
      "id":5,
      "name":"BTE",
      "description":"Use for lots that depend or contribute to BSC Tracking Enabled Rewards."
   },
   {
      "id":19,
      "name":"CAM files",
      "description":"All basic CAM files"
   },
   {
      "id":2,
      "name":"W2W",
      "description":"All Wall to Wall types of buildings"
   },
   {
      "id":7,
      "name":"Water",
      "description":"Water Mods"
   }
]
```

## Lot Groups
Returns a JSON Array containing the Lot Groups

	GET /api/<version>/category/group

### Authentication
No

### Parameters
None

### Response
- 200 OK: list of lot groups

```javascript
[
   {
      "id":4,
      "name":"BSC - VIP girafe flora",
      "author":"girafe"
   },
   {
      "id":2,
      "name":"CAL Canals",
      "author":"callagrafx"
   },
   {
      "id":6,
      "name":"Sea- and Retaining Walls",
      "author":"ADMIN"
   },
   {
      "id":5,
      "name":"Ships",
      "author":"ADMIN"
   }
]
```

## Lot Authors
Returns a JSON Array containing the Lot Authors (users who have released at least 1 file on the LEX)

	GET /api/<version>/category/author

### Authentication
No

### Parameters
No

### Response
- 200 OK: list of lot authors

```javascript
[
   {
      "id":1,
      "name":"ADMIN"
   },
   {
      "id":6509,
      "name":"andisart"
   },
   {
      "id":15381,
      "name":"z"
   },
   {
      "id":5275,
      "name":"zero7"
   }
]
```

## Overview
Returns a JSON Object containing an overview of all the types of categories.

	GET /api/<version>/category/all

### Authentication
No

### Parameters
None

### Response
- 200 OK: overview of all categories

```javascript
{
   "broad_category":[
      {
         "id":1,
         "name":"Agriculture",
         "image":"250_MX_Agric.gif"
      },
      {
         "id":2,
         "name":"Civic",
         "image":"250_MX_Civic.gif"
      },
      {
         "id":15,
         "name":"WFK - Canals",
         "image":"250_MXC_WFK-Canals.gif"
      }
   ],
   "lex_category":[
      {
         "id":66,
         "name":"00 Locked"
      },
      {
         "id":65,
         "name":"00 Outdated"
      },
      {
         "id":35,
         "name":"Utilities - Power"
      },
      {
         "id":34,
         "name":"Utilities - Water"
      }
   ],
   "lex_type":[
      {
         "id":5,
         "name":"BTE",
         "description":"Use for lots that depend or contribute to BSC Tracking Enabled Rewards."
      },
      {
         "id":19,
         "name":"CAM files",
         "description":"All basic CAM files"
      },
      {
         "id":2,
         "name":"W2W",
         "description":"All Wall to Wall types of buildings"
      },
      {
         "id":7,
         "name":"Water",
         "description":"Water Mods"
      }
   ],
   "group":[
      {
         "id":4,
         "name":"BSC - VIP girafe flora",
         "author":"girafe"
      },
      {
         "id":2,
         "name":"CAL Canals",
         "author":"callagrafx"
      },
      {
         "id":6,
         "name":"Sea- and Retaining Walls",
         "author":"ADMIN"
      },
      {
         "id":5,
         "name":"Ships",
         "author":"ADMIN"
      }
   ],
   "author":[
      {
         "id":1,
         "name":"ADMIN"
      },
      {
         "id":6509,
         "name":"andisart"
      },
      {
         "id":15381,
         "name":"z"
      },
      {
         "id":5275,
         "name":"zero7"
      }
   ]
}
```
