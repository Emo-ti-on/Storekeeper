# Storekeeper

### Be careful README.md was created by (not) master of english
## Configuring

You must set up folder were we will store all configs.

```php
Simplify\Storekeeper\Config::setConfigFolder('/path/to/folder')
```

Also, you may select separator for map.

```php
Simplify\Storekeeper\Config::setSeparator('.') // By default separator is "."
```

## Usage

Create instance of ``` Simplify\Storekeeper\Config ```

```php
$config = new Simplify\Storekeeper\Config('name_of_config');
```

If you want you can store some data inside config, when you create instance
```php
$data = [
    'data' => '2',
    'data2' => 1
];

$config = new Simplify\Storekeeper\Config('name_of_config', $data);
```

Storekeeper check is file (with name as config name) exists. 
If exists it fills current config, otherwise it stays empty.

Now you can start your work with the available methods.

---
### _get(string $map = ''): mixed_

Returns data searched by $map

Example
```php
$config = new Simplify\Storekeeper\Config('name_of_config'),
    [
        'someData' => [
            'subData1' => true,
            'subData2' => 'data2'            
        ],
        'number' => 123
    ]
);

$data = $config->get('someData.subData1'); // Returns true
$data2 = $config->get('somData.subData2'); // Returns 'data2'
$data3 = $config->get('number'); // Returns 123
```

---
### _set(string $map, mixed $value): void_

Setup data by $map

Example
```php
$config = new Simplify\Storekeeper\Config('name_of_config');

$config->set('str', '1234');
$config->set('number', 1234);
$config->set('somData.subData2', false);
```

### Pay attention!

All data keeps in global value

```php
$config = new Simplify\Storekeeper\Config('name_of_config');
$sameConfig = new Simplify\Storekeeper\Config('name_of_config');

$config->set('foo', 'global value');

echo $sameConfig->get('foo'); // Output: 'global value'
```

---
### _contains(string $map): bool_

Check is config key exists

Example
```php
$config = new Simplify\Storekeeper\Config('name_of_config'), 
    [
        'existVal' => [
            'existsSubVal' => 'data'
        ]
    ]
);

$config->contains('existVal') // Returns true
$config->contains('existVal.existsSubVal') // Returns true
$config->contains('thisValueIsNotExists') // Returns false
```

---
### _unset(string $map): void_

Removes data by $map 

Example
```php
$config = new Simplify\Storekeeper\Config('name_of_config'), 
    [
        'someData' => [
            'subData1' => true,
            'subData2' => 'data2'            
        ],
        'number' => 123
    ]
);
$config->unset('someData')
$config->get('someData') // throws Exception 'Key someData does not exist'
```

---
### _reset()_

Returns config to it initial state

Example

```php
$config = new Simplify\Storekeeper\Config('name_of_config'), 
    [
        'value1' => 'i\'am a string'
        'value2' => 97
    ]
);
$config->set('value1', 555)
$config->unset('value2')

print_r($config->get()) // Array (value1 => 555)

$config->reset()
print_r($config->get()) 
/* 
   Array (
       value1 => i'am a string
       value2 => 97
   )
 */
```

### Be careful!!!

When you create instance of Config it saves current state in local value.

```php
$snake_config = new \Simplify\Storekeeper\Config('config'); // Saved state is []

$snake_config->set('foo', 1233); // Current state ['foo' => 1233]
echo $snake_config->get('foo') . PHP_EOL; // output 1233

$camelConfig = new \Simplify\Storekeeper\Config('config'); // Saved state ['foo' => 1233]

$camelConfig->set('foo', 12); // Current state ['foo' => 12]
echo $camelConfig->get('foo') . PHP_EOL; // output 12

$camelConfig->reset(); // Current state ['foo' => 1233]
echo $snake_config->get('foo') . PHP_EOL; // output 1233

$snake_config->reset();

$camelConfig->get('foo'); // Exception: value: 'foo' Does not exists in ...
```

---
### _truncate(): void_

Remove all data from current config

Example

```php
$config = new Simplify\Storekeeper\Config('name_of_config'), 
    [
        'value1' => 'i\'am a string'
        'value2' => 97
    ]
);

print_r($config->get()) 
/* 
   Array (
       value1 => i'am a string
       value2 => 97
   )
 */
 
$config->truncate();

print_r($config->get()); // Array ()
```

---
### _delete(): void_

Remove file from config folder

---
### _save(): void_

Stores data to file inside config folder (filename is the same as config name)

---
### _isEmpty(): bool_

Check is current config empty

---
### _isNotExists(): bool_

Check is config file exists
