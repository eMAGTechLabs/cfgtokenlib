# ConfigToken Library

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
