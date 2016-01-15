SC4Devotion Lot Exchange API
===================

This repository contains usage documentation for developers using the SC4Devotion LEX API

## Libraries
A few support/wrapper libraries for this API have been built
* (Java, Android) **LEX4J** - https://github.com/caspervg/SC4D-LEX4J (almost at v1.0)
* (C#, .NET) **SharpLEX** - https://github.com/caspervg/SC4D-SharpLEX (in early development)

## Changelog

#### v1
* Initial release

#### v2
* Search-by-name added

#### v3
* Images and category images now use a full url, instead of a relative url
* Added options to return a full or concise dependency list on the /search/ endpoint

#### v4
* Add easier options to access lot comments & vote totals
* Improve consistency in the JSON results (especially /lot/:lotid:/ vs the search results)
* Improved UTF-8 support on new versions of PHP
* Search returns an empty array (```[]```) instead of a HTTP 404 Not Found error when no results are found
* Added bulk dependency download functionality under /lot/:lotid:/bulk-dependency
