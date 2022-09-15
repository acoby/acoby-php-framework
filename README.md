[![Build & Test](https://github.com/acoby/acoby-php-framework/actions/workflows/php.yml/badge.svg)](https://github.com/acoby/acoby-php-framework/actions/workflows/php.yml)

# acoby/framework

This is our primary php framework. It contains some helpful classes for our PHP projects. Please add them via

    "require" : {
      "acoby/framework" : "*"
    },
    "repositories" : [{
        "type" : "vcs",
        "url" : "https://github.com/acoby/acoby-php-framework.git"
      }
    ]

to your compose.json.

## change log

### v1.0.0

- inital version with Slim, Twig and DatabaseMapper