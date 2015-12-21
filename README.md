# Env - Environment variable container

[![Build Status](https://travis-ci.org/crysalead/env.png?branch=master)](https://travis-ci.org/crysalead/env)
[![Code Coverage](https://scrutinizer-ci.com/g/crysalead/env/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/crysalead/env/)

Environment variable container.

## Installation

```bash
composer require crysalead/env
```

## API

### Example

```php
use Lead\Env\Env;

$env = new Env($_SERVER + $_ENV);

$env['PHP_SELF'];

$env['UNEXISTING_VARIABLE']; // returns `false` like `getenv()` on undefined

$env['CUSTOM_VARIABLE'] = 'myvalue';

// Multiple set
$env->set([
    'CUSTOM_VARIABLE2' => 'myvalue2',
    'CUSTOM_VARIABLE3' => 'myvalue3'
]);

isset($env['CUSTOM_VARIABLE']);

unset($env['CUSTOM_VARIABLE']);

$env->clear(); // removes all variables.
```
