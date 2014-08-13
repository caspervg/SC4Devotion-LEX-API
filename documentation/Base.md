# LEX API: Base

## Errors and Exceptions

### Database Connection Failure

#### Caused by
Backend database is down, for maintenance or because of a failure

### Response
- 503 Service Not Available

```javascript
{
   "status":503,
   "error":"Could not connect to database",
   "suggestion":"Try again later"
}
```

### Other Failures

#### Caused by
Internal errors because of API bugs

### Response
- 500 Internal Server Error

```javascript
{
   "status":500,
   "error":"Message explaining the exception that occurred",
   "suggestion":"Report to a site administrator"
}
```

## Base

### Endpoint overview

Show an *overview* of the various endpoints supplied by the LEX API

    GET /api/<version>/

#### Authentication
No

#### Parameters
None

#### Response
- 200 OK: Overview of endpoints

```javascript
{
   "basic":{
      "\/":"(GET) retrieves all endpoints for this API",
      "\/version":"(GET) retrieves the current version of this API"
   },
   "user":{
      "\/user":"(GET) retrieves profile information for the user",
      "\/user\/download-history":"(GET) retrieves download history for the user",
      "\/user\/download-list":"(GET) retrieves download list for the user",
      "\/user\/register":"(POST) registers a new user for the LEX",
      "\/user\/activate":"(GET) activates the registration for a LEX user"
   },
   "lot":{
      "\/lot\/all":"(GET) retrieves a list of all lots",
      "\/lot\/:lotid":"(GET) retrieves information about the lot with the supplied ID"
   },
   "search":{
      "\/search":"(GET) retrieves search results - more information: http:\/\/sc4devotion.com\/forums\/index.php?topic=16074.0"
   },
   "interaction":{
      "\/lot\/:lotid\/download":"(POST) retrieves a download link for the lot with the supplied ID - also adds it to download history",
      "\/lot\/:lotid\/download-list":"(POST) adds the lot with the supplied ID to the download-later list"
   }
}
```

### API version

Shows the current *API version*. This can be a useful endpoint to check if the API is online.

    GET /api/<version>/version

#### Authentication
No

#### Parameters
None

#### Response
- 200 OK: Version and API type (public/private/..)

```javascript
{
   "version":"v3",
   "type":"public"
}
```
