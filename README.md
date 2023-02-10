[![Build & Test](https://github.com/acoby/acoby-php-framework/actions/workflows/php.yml/badge.svg)](https://github.com/acoby/acoby-php-framework/actions/workflows/php.yml)

# acoby/framework

This is our primary php framework. It contains some helpful classes for our PHP projects. Here is a list of classes

- `acoby\controller\AbstractController` - an abstract base class for managing Slim requests
- `acoby\controller\AbstractAPIController` - an abstract base class for managing Slim REST requests
- `acoby\controller\AbstractEditController` - an abstract base class for managing Slim/Twig Edit requests
- `acoby\controller\AbstractListController` - an abstract base class for managing Slim/Twig List requests
- `acoby\middleware\AcobyAuthHandler` - a middleware handler for managing Twig tools in Slim request handling
- `acoby\middleware\ErrorHandler` - a middleware handler for managing exceptions during request handling in Slim
- `acoby\services\ConfigService` - a small tool to wrap a global array with parameters
- `acoby\system\DatabaseManager` - an easy Object Mapper for PDO operations to have CRUD operations in a database
- `acoby\system\HTTPClient` - a wrapper around Gizzle HTTP client to have a PHPUnit wrapping options
- `acoby\system\SessionManager` - very easy wrapper around a session.
- `acoby\system\Utils` - contains some always used functions for string and object manipulation. Also for Logging
- `acoby\system\auth\AnsibleVault` - a tool for de- and encrypting data to an AnsibleVault
- also we add some dependency (like Slim, Twig), that are always used in our PHP projects

## Usage

If you want to use this package, please add this

    "require" : {
      "acoby/framework" : "*"
    },
    "repositories" : [{
        "type" : "vcs",
        "url" : "https://github.com/acoby/acoby-php-framework.git"
      }
    ]

to your compose.json. We don't publish the package to the public composer archive. So you need to add the repository itself.

## change log

### v2.0.0

- we made some major changes
  - Slim4 REST Backend functions
  - additional support for Controller and Factories
  - additional OAuthMiddleware
- a lot of bugfixes

### v1.5.0

- feat: created some more FormField Types
- fix: update dependencies

### v1.4.8

- fix: found a minor bug during decrypting data

### v1.4.7

- feat: cookie support and domain validation utility
- feat: add some more query options
- feat: add History
- fix: remove strict mode for exceptions
- fix: some minor issues


### v1.0.0

- inital version with Slim, Twig and DatabaseMapper
