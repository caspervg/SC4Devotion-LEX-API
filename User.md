# LEX API: User

## Registration

Registers a *new user account* on the LEX. Will send a confirmation e-mail to the address.
    
	POST api/<version>/user/register

### Authentication
No

### Parameters
- username: String - username of the new account
- password_1: String - password of the new account
- password_2: String - password of the new account (confirmation)
- email: String - e-mail address for the new account
- fullname: String - full name for the new account (can be empty)

### Response
- 400 Bad Request: not all parameters were present
- 401 Unauthorized: password and confirmation did not match
- 409 Conflict: user with this username or e-mail already exists
- 403 Forbidden: user is currently banned from the LEX
- 200 OK: user has been registered and a activation link has been sent

## Activation

Activates a *new user account* on the LEX, based on a URL with a confirmation code that is e-mailed to the new user.
	
	GET api/<version>/user/activate

### Authentication
No

### Parameters
- activation_key: String - secret key that is used to activate the account

### Response
- 403 Forbidden: not a valid activation key or the user is already activate
- 200 OK: user has been activated

## Profile / User information

Returns a JSON Object containing all information for the authenticated user.

	GET api/<version>/user

### Authentication
Yes, Basic HTTP

### Parameters
None

### Response
- 401 Unauthorized: authentication failed
- 200 OK: user information

```javascript
{
   "id":1,
   "fullname":"Test McTester",
   "username":"test_account",
   "registered":"20070602",
   "last_login":"20131113090110",
   "is_active":true,
   "user_level":1,
   "email":"example@domain.com",
   "login_count":949,
   "is_donator":true,
   "is_rater":true,
   "is_uploader":true,
   "is_author":false,
   "is_admin":false
}
```

## User Download List

Returns a JSON Array containing the download list for the authenticated user.

	GET api/<version>/user/download-list

### Authentication
Yes, Basic HTTP

### Parameters
None

### Response
- 401 Unauthorized: authentication failed
- 200 OK: user download list

```javascript
[
   {
      "record":{
         "id":13099621
      },
      "lot":{
         "id":4,
         "name":"JRJ Props Vol4  Rural Walls",
         "update_date":null,
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":13117158
      },
      "lot":{
         "id":930,
         "name":"WTC Prop Pack 1 The Plaza Base",
         "update_date":null,
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":13120289
      },
      "lot":{
         "id":100,
         "name":"CAL Old English Harbour set",
         "update_date":null,
         "version":"1.0"
      }
   }
]
```

## User Download History

Returns a JSON Array containing the download history for the authenticated user.

	GET api/<version>/user/download-history

### Authentication
Yes, Basic HTTP

### Parameters
None

### Response
- 401 Unauthorized: authentication failed
- 200 OK: user download history

```javascript
[
   {
      "record":{
         "id":12850359,
         "last_downloaded":"20130812",
         "last_version":"1.0",
         "download_count":1
      },
      "lot":{
         "id":746,
         "name":"BSC MEGA Props - SG Vol 01",
         "update_date":null,
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":13098029,
         "last_downloaded":"20131104",
         "last_version":"1.0",
         "download_count":2
      },
      "lot":{
         "id":2,
         "name":"CSX Farm SF - Veronique",
         "update_date":null,
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":12850366,
         "last_downloaded":"20130812",
         "last_version":"1.0",
         "download_count":1
      },
      "lot":{
         "id":101,
         "name":"BSC TexturePack Cycledogg V 01b",
         "update_date":"20070130",
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":12850367,
         "last_downloaded":"20130812",
         "last_version":"1.0",
         "download_count":1
      },
      "lot":{
         "id":90,
         "name":"BSC Textures Vol 01",
         "update_date":"20070405",
         "version":"1.0"
      }
   },
   {
      "record":{
         "id":13116436,
         "last_downloaded":"20131111",
         "last_version":"1.0",
         "download_count":3
      },
      "lot":{
         "id":960,
         "name":"CAM Residentials BSC",
         "update_date":"20070722",
         "version":"1.0"
      }
   }
]
```

## Profile / User information (Administration)

Returns a JSON object containing all information for the requested user, if the authenticated user is an admin

	GET api/<version>/user/:userid:

### Authentication
Yes, Basic HTTP (requires Admin)

### Parameters
None

### Response
- 401 Unauthorized: authentication failed
- 403 Forbidden: not an administrator
- 200 OK

```javascript
{
   "id":1,
   "fullname":"Test McTester",
   "username":"test_account",
   "registered":"20070602",
   "last_login":"20131113090110",
   "is_active":true,
   "user_level":1,
   "email":"example@domain.com",
   "login_count":949,
   "is_donator":true,
   "is_rater":true,
   "is_uploader":true,
   "is_author":false,
   "is_admin":false
}
```

## List of Users (Administration)

Returns a JSON array containing userdata for all registered LEX users.

	GET api/<version>/user/all

### Authentication
Yes, Basic HTTP (requires Admin)

### Parameters
- concise: Boolean - true: return only userid/username, false: return everything
- start: Integer - start at account number <start>
- amount: Integer - return <amount> results

### Response
- 401 Unauthorized: authentication failed
- 403 Forbidden: not an administrator
- 400 Bad Request: start/amount parameters were not defined
- 200 OK: list of users

```javascript
[
   {
      "id":11,
      "username":"account11"
   },
   {
      "id":12,
      "username":"account12"
   },
   {
      "id":13,
      "username":"account13"
   }
]
```

