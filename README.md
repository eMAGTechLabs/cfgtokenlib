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


## The Tree Compiler

The tree compiler may be used to merge together multiple structures through an inheritance-like pattern.
In order for that to be possible, all structures must comply with a pre-determined schema.

### Tree Compiler Usage Example

```php
$treeCompiler = new TreeCompiler();
$result = $treeCompiler->compile('example.json');
$treeCompiler->save($result, 'result.json');
```

phrase.json
```js
{
  "phrase": {
    "line1": "The [[attribute|lower]] [[color]] [[mammal|upper]] jumps over the [[target]].",
    "remove_me": "This line must be removed",
    "line2": "The [[mammal]] is [[attribute]]."
  }
}
```

example.json
```js
{
  "include": {
    "a": {
      "type": "file",
      "src": "./phrase.json"
      "resolve": [
        {
          "type": "registered",
          "values": {
            "attribute": "QUICK",
            "color": "brown",
            "target": "lazy dog"
          }
        }
      ]
    }
  },
  "remove": {
    "phrase": {
      "remove_me": null
    }
  },
  "add": {
    "phrase": {
      "line3": "The end."
    }
  }
}
```

result.json
```js
{
  "phrase": {
    "line1": "The quick brown FOX jumps over the lazy dog.",
	"line2": "The Fox is QUICK.",
    "line3": "The end."
  }
}
```


### Input structure schema (associative array):

- _**include**_: optional. First to be processed if present. Associative array describing the included external references and include groups;
  - **xref**: required. Associative array describing the included structures from external references;
    - &lt;xref name&gt;: required. String. The local declared name of the external reference;
      - **type**: required. String. The type of the external reference. Possible values are:
        - _file_: local file external
          - &lt;file path&gt;: the path to a local file; either absolute (/) or relative to the current file (./).
        - _url_: url external reference;
          - &lt;url&gt;: the URL to a HTTP accessible remote file. If the response Content-Type does not match a known XrefResolver then the file extension in the URL path will be used to determine the appropriate de-serializer.
        - _git_: git external reference
          - &lt;git file&gt;: git@&lt;host>:/&lt;group>/&lt;repo>.git:&lt;branch&gt;:&lt;file path&gt;
          - &lt;git file url&gt;: [http|https]://&lt;host&gt;/&lt;group&gt;/&lt;repo&gt;.git:&lt;branch&gt;:<file path&gt;
        - _&lt;custom&gt;_: custom external reference handling class registered with **XrefResolverFactory**;
      - **src**: required. The string describing the location of the xref formatted according to _type_ (see above);
      - **resolve**: optional. Ordered list of resolver settings. This key describes the token resolvers and the values used to replace the tokens in the structure fetched from the external reference. If more than one, the resolvers will be used in the given order.
        - _&lt;unnamed resolver settings&gt;_: required. Associative array describing the configuration of the token resolvers.
          - **type**: required. String. The type identifying the token resolver's class. Possible values are:
            - _registered_|_&lt;custom resolver extending RegisteredTokenResolver&gt;_
            - _scope_|_&lt;custom resolver extending ScopeTokenResolver&gt;_
          - **options**: String. The options to set on the TokenResolverClass. Options common to all resolvers are:
            - **_ignore-unknown-tokens_**: boolean. Default `true`. If `false`, a compile error will be raised if the resolver encounters an unknown token. For chaining resolvers, this value must be `true` for all except the last.
            - **_ignore-unknown-filters_**: boolean. Default `true`. If `false`, a compile error will be raised if the resolver encounters an unknown token value filter.
            - **_token-regex_**: string. Default `"/\[\[+(.*?)\]\]/"`. The regular expression used to identify the tokens. If specified, the resolver will ignore _token-prefix_ and _token-suffix_.
            - **_token-prefix_**: optional. String. Default `"[["`. The token prefix for the default regular expression. For simple tokens that do not need to override _token-regex_ but just alter the start delimiter.
            - **_token-suffix_**: optional. String. Default `"]]"`. The token suffix for the default regular expression. For simple tokens that do not need to override _token-regex_ but just alter the ending delimiter.
            - **_token-filter-delimiter_***: string. Default `"|"`. The character/string used to delimit multiple token value filters in the token name (eg. `[[token_name|lower|dot]]`)
            
            The following options are available for token resolvers extending the **ScopeTokenResolver** base class:
            - **scope-token-name**: required. String. The token name to identify the resolver's scope. (eg. for scope-token-name = json-a `[[json-a:object.value]]`)
            - **_scope-token-name-delimiter_***: optional. String. Default `":"`. The character/string used to delimit the scope token name from the scope value path.
            - **_scope-token-level-delimiter_***: optional. String. Default `"."`. The character/string used to delimit the levels in the scope value path. (eg. if set to `"->"` the following will match `[[json:object->value]]`

            *_Note that all delimiters used inside the token must be different ._
            
          Only one of the following keys is required (if both are present, the compiler will raise a syntax error):
            
          - **_values_**: associative array containing the name-value pairs that form the scope of the token resolver.
          - **_values-xref_**: associative array describing the type and source of the external reference that holds the name-value pairs that form the scope of the token resolver.
            - **type**: required. String. The type of the external reference. (see above)
            - **src**: required. The string describing the location of the xref formatted according to _type_ (see above);
          
          _The scope values may only be nested for scope token resolvers._
          
  - **main**: ordered list of external reference names to include if no specific include group requested. This group may only be ommited if there is only one Xref in the **_include_** section.
  - _&lt;include group name&gt;_: named include groups represented by ordered lists of external reference names;
- _**remove**_: optional. Second to be processed if present. Associative array describing the keys to be removed from the result obtained by merging the included structures;
- _**add**_: optional. Last to be processed if present. Associative array describing the keys to be added / overridden in the result obtained by merging the included structures;

If the **_include_**, **_remove_** and **_add_** keys are missing from the first level of the structure then all keys will be considered to belong under the **_add_** key.

```js
{
  "include": {
    "xref": {
      "<xref name>": {
        "type": "file|url|git|<custom>",
        "src": "<file path>|<url>|[<git file>|<git file url>]",
        "resolve": [
          {
            "type": "registered|custom",
            "options": {
              "ignore-unknown-tokens": true,
              "ignore-unknown-filters": true,
              "token-regex": "/\[\[+(.*?)\]\]/",
              "token-prefix": "[[",
              "token-suffix": "]]",
              "token-filter-delimiter": "|"
            },
            "values": {
              "<token name 1>": "<token value 1>",
              "<token name n>": "<token value n>"
            },
            "values-xref": {
              "type": "file|url|git|<custom>",
              "src": "<file path>|<url>|[<git file>|<git file url>]"
            }
          }, {
            "type": "scope|custom",
            "options": {
              "ignore-unknown-tokens": true,
              "ignore-unknown-filters": true,
              "token-regex": "/\[\[+(.*?)\]\]/",
              "token-prefix": "[[",
              "token-suffix": "]]",
              "token-filter-delimiter": "|",
              "scope-token-name": "<token name>",
              "scope-token-name-delimiter": ":",
              "scope-token-level-delimiter": "."
            },
            "values": {
              "<token name 1>": "<token value 1>",
              "<token name 2>": {
                "<token level 2>": {
                  "<token leven n>": "<token value @level n>"
                },
              },
              "<token name n>": "<token value n>"
            },
            "values-xref": {
              "type": "...",
              "src": "...",
            }
          }
        ]
      }
    },
    "main": [
      "<xref name 1>",
      "<xref name n>"
    ],
    "<include group name>": [
      "<xref name 1>",
      "<xref name n>"
    ]
  },
  "remove": {
  },
  "add": {
  }
}
```