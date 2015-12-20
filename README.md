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

$env->get('PHP_SELF');

$env->set('CUSTOM_VARIABLE', 'myvalue');

$env->set([
    'CUSTOM_VARIABLE2' => 'myvalue2',
    'CUSTOM_VARIABLE3' => 'myvalue3'
]);

$env->has('CUSTOM_VARIABLE');

$env->remove('CUSTOM_VARIABLE');

$env->clear(); // removes all variables.
```
