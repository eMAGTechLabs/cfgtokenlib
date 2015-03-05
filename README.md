# ConfigToken Library

The ConfigToken library provides the following classes to aid in token parsing, value formatting and injection:
- **TokenParser**: configurable string parser class used to extract a collection of tokens.
- **TokenCollection**: generic token collection class used in conjunction with a given token resolver instance to resolve tokens to values and apply the given filters.
- **TokenInjector**: provides a method to inject the resolved token values collection back into the original string.

a set of interfaces and factories to create custom resolvers and filters:
- **TokenResolverInterface**: interface to create custom token value resolvers;
- **TokenFilterInterface**: interface to create custom token value filters;
- **TokenFilterFactory**: factory class used to hold and register token value filters.

and base classes to extend and customize two of the most common use-cases:
- **RegisteredTokenResolver**: resolves token values based on a given uni-dimensional associative array (token name => value).
- **ScopeTokenResolver**: resolves token values based on a given mutli-dimensional associative array.

## The Token Parser

The token parser may be used to parse strings and extract a collection of tokens based on a configurable regular expression.

```php
$tokenParser = new TokenParser();
$tokens = $tokenParser->parseString($input);
```

or explicitly setting token delimiters and filter delimiters:

```php
$tokenParser = new TokenParser('|', null, '[[', ']]');

// also possible via setters
$tokenParser
    ->setFilterDelimiter('|')
    ->setTokenPrefix('[[')
    ->setTokenSuffix(']]')
;

$tokens = $tokenParser->parseString($input);
```

or explicitly setting token regex and filter delimiters:

```php
$tokenParser = new TokenParser('|', '/\[\[s+(.*?)\]\]/');

// also possible via setters
$tokenParser
    ->setFilterDelimiter('|')
    ->setTokenRegex('/\[\[s+(.*?)\]\]/')
;

$tokens = $tokenParser->parseString($input);
```

example input:
```
The [[attribute|lower]] [[color]] [[mammal|upper]] jumps over the [[target]].\n
The [[mammal]] is [[attribute]].
```

identified tokens:
```
    [[attribute|lower]]
        token name: "attribute"
        filters: ["lower"]
        offsets: [4]
    [[color]]
        token name: "color"
        offsets: [24]
    [[mammal|upper]]
        token name: "mammal"
        filters: ["upper"]
        offsets: [34]
    [[target]]
        token name: "target"
        offsets: [66]
    [[mammal]]
        token name: "mammal"
        offsets: [82]
    [[attribute]]
        token name: "attribute"
        offsets: [96]
```

The extracted tokens may later be resolved directly by setting their values.

```php
$mammal = $tokens->findByName('mammal');
foreach ($mammal as $token) {
    $token
        ->setUnfilteredTokenValue('Fox')
        ->applyFilters()
    ;
}
$output = TokenInjector::injectString($input, $tokens);
```

```
The [[attribute|lower]] [[color]] FOX jumps over the [[target]].\n
The Fox is [[attribute]].
```

or by using a predefined or a custom TokenResolver.

```php
$resolver = new RegisteredTokenResolver(
    'attribute' => 'QUICK',
    'color' => 'brown',
    'target' => 'lazy dog',
);
$tokens->resolve($resolver);
$output = TokenInjector::injectString($output, $tokens);
```

```
The quick brown FOX jumps over the lazy dog.\n
The Fox is QUICK.
```

Injection may be done in multiple steps on the result string without the need of re-parsing the tokens.

Regardless of the method through which the token values are resolved, either custom or predefined value filters may be applied prior to injecting.


## Tree Compiler

Usage example:

```php
$treeCompiler = new TreeCompiler();
$treeCompiler->compile('base.json', 'baseCompiled.json');
```

## Token Parser, Resolver & Injector

test.json

```
{
    'firstKey': {
        'firstSubKey': [
            'a',
            'b'
        ],
        'secondSubKey': 'secondSubKeyValue'
    },
    'secondKey[[suffix|dot|lower]]': [
        '[[json:firstKey.firstSubKey.0|upper]]',
        '[[json:firstKey.firstSubKey]]',
        '[[json:firstKey.secondSubKey]]',
        'value4'
    ],
    'thirdKey': {
        'firstSubKey': 'prefix-[[json:firstKey.secondSubKey]]-suffix'
    }
}
```

```php
$tokenizedJsonData = file_get_contents('test.json');
// init token parser
$tokenParser = new TokenParser();
// identify tokens in string
$tokens = $tokenParser->parseString($tokenizedJsonData);

function printTokens(TokenCollection $tokens) {
    // print found tokens
    foreach ($tokens as $token) {
        echo sprintf(
            "tokenString: \"%s\", tokenName: \"%s\", filters: [%s], offsets: [%s]\n",
            $token->getTokenString(),
            $token->getTokenName(),
            implode(', ', $token->getFilters()),
            implode(', ', $token->getOffsets())
        );
    }
}
printTokens($tokens);
/*
  tokenString: "[[suffix|dot|lower]]", tokenName: "suffix", filters: [dot, lower], offsets: [155]
  tokenString: "[[json:firstKey.firstSubKey.0|upper]]", tokenName: "json:firstKey.firstSubKey.0", filters: [upper], offsets: [189]
  tokenString: "[[json:firstKey.firstSubKey]]", tokenName: "json:firstKey.firstSubKey", filters: [], offsets: [238]
  tokenString: "[[json:firstKey.secondSubKey]]", tokenName: "json:firstKey.secondSubKey", filters: [], offsets: [279, 385]
*/

// create simple token resolver
$simpleResolver = new RegisteredTokenResolver(
    array(
        'suffix' => '_Value',
    )
);
$tokens->resolve($simpleTokenResolver);

// create the scope token resolver with JSON serializer
$jsonScopeResolver = new JsonScopeTokenResolver('json', json_decode($tokenizedJsonData, true));
$tokens->resolve($jsonScopeResolver);

// inject resolved tokens
$jsonData = TokenInjector::injectString($tokenizedJsonData);

file_put_contents('test-injected.json', $jsonData);
```

test-injected.json

```
{
    'firstKey': {
        'firstSubKey': [
            'a',
            'b'
        ],
        'secondSubKey': 'secondSubKeyValue'
    },
    'secondKey_value': [
        'A',
        '[\'a\', \'b\']',
        'secondSubKeyValue',
        'value4'
    ],
    'thirdKey': {
        'firstSubKey': 'prefix-secondSubKeyValue-suffix'
    }
}
```
