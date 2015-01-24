Roll'n API
==========

Apigility with Doctrine OAuth2 Server and Entities
--------------------------------------------------

This is written for PHP 5.3 but these instructions assume 5.4 or greater.

```
git clone git@github.com:TomHAnderson/apigility-doctrine-skeleton
cd apigility-doctrine-skeleton
cp config/autoload/local.php.dist config/autoload/local.php
./composer.phar install
php public/index.php orm:schema-tool:create
php public/index.php data-fixture:import
php -S localhost:8083 -t public/ public/index.php
```


Data
----

There are three endpoints and three data tables.  The Artist has no references.  The Album has a refernce to Artist.  The UserAlbum has a reference to Album and a User.

The endpoints are
```
/api/artist
/api/album
/api/user-album
```

There are two clients registerd.
```
user: client1
pass: client1password
(odd numbered UserAlbums 1, 3, 5)

user: client2
pass: client2password
(even numbered UserAlbums 2, 4, 6)
```


Run
---

Test the service.  This call will return an (ugly) exception stating 'Not Authorized':
```
http -f GET http://localhost:8083/api/artist
```

Authenticate with OAuth2
```
http --auth client1:client1password -f POST http://localhost:8083/oauth grant_type=client_credentials
```

Using the access_token from the Authentication replace it with your token and run:
```
http -f GET http://localhost:8083/api/artist "Authorization: Bearer access_token"
```

User specific handling.  Run this to see your client has three UserAlbums:
```
http -f GET http://localhost:8083/api/user-album "Authorization: Bearer access_token"
```

Create a new UserAlbum and Roll'n API will assign the user to the authenticated user
```
echo '{"album":1,"description": "User Album from POST"}' |  http -f POST http://localhost:8083/api/user-album "Authorization: Bearer access_token" "Content-type: application/json"
```

Run the UserAlbum again to see the new UserAlbum
```
http -f GET http://localhost:8083/api/user-album "Authorization: Bearer access_token"
```

Change the just-created UserAlbum
```
echo '{"album":2,"description": "Change Description"}' |  http -f PATCH http://localhost:8083/api/user-album/7 "Authorization: Bearer access_token" "Content-type: application/json"
```

Make a filtered query to UserAlbum.  This filter is:
```
field: description
type: like
value: %Aoxomoxoa%

http -f GET http://localhost:8083/api/user-album?filter%5B0%5D%5Btype%5D=like&filter%5B0%5D%5Bfield%5D=description&filter%5B0%5D%5Bvalue%5D=%25Aoxomoxoa%25" "Authorization: Bearer access_token"
```


Features
--------

A default Query Provider is used for UserAlbum so a user can only fetch or update their own UserAlbum.

A FetchAll Query Provider for UserAlbum is used so filters may be applied to a GET request.  See https://github.com/zfcampus/zf-doctrine-querybuilder for details.

A Query Create Filter is used for UserAlbum so the authenticated user is assigned to any new UserAlbum.

A Hydrator Filter is used for UserAlbum so the assigned user entity is not included in results.
