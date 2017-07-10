# php-random-gen
## Random Data Library

The library is easy to get up and running quickly. It is suitable for generating
random bytes or strings from a given character set. It is possible to generate
arrays of arbitrary depth of nesting as well as files with large data structures

## Install

Via Composer

``` bash
$ composer require ierusalim/php-random-gen
```

## Usage

#### Generation random strings
This simple functions placed in file RandomStr.php and have have no dependencies
* **genRandomStr**($len)
* **genRabdomBytes**($bytes_cnt)
* **setChars**(*list of values from which the result will be generate*)

It is easy to understand from the examples below
```php
<?php

$g = new RandomStr();

// Generate random 16 characters from default list
$str = $g->genRandomStr(16);
echo $str;

// Generate 33 random bytes
$bytes = $g->genRandomBytes(33);

// It works fast. No problem for create random string of million chars
$str = $g->genRandomStr(1000000);
echo "\nGenerated bytes: " . strlen($str);

// Need generate multibyte characters? No problems, set flag $utf8mode = true
$g->setChars("神會貓性少女 迪克和陰部", true);
$str = $g->genRandomStr(888);

// Generate string of 100 chars from character list "abcdefgh"
$g->setChars("abcdefgh");
$str = $g->genRandomStr(100);
echo $str;

// Generate string of 10 random words from specified words array
$words_arr = explode(',',' one, two, three, four, five, six, seven');
$g->setChars([$words_arr]);
echo $g->genRandomStr(10); 
// We get a result like " five three three three four seven four four six four"

```

#### Generation random Arrays
The random array generation class RandomArray are extended of RandomStr.

Simple examples:
```php
<?php

$g = new RandomArray();

// Generate small random array with default parameters:
$arr = $g->genRandomArray();
print_r($arr);

// Generate random array with string keys from listed chars, 3-9 chars length
$g->setKeysModel(3, 9, 'abcdefghijklmnopqrstuvwxyz');
$g->setValuesModel(0, 100); //random numeric values range from 0 to 100
$arr = $g->genRandomArray(10, 15, 0); //generate 10-15 elements (not nested)
print_r($arr);

```

The generation of random arrays occurs in memory, so with a large number
of elements (100,000 or more) it may work slowly if using non-simple models.

But, when using simple numeric keys (1,2...n) and simple values range 0-65535,
generation time of array 1 million elements (with depth of nested 2-3 levels) 
is less than 1 second.

See example:
```php
<?php

$g = new RandomArray();
$g->setKeysModel();     //set simple keys model (1,2...n)
$g->setValuesModel();   //set simple values model (integer range 0-65535)
$lim_elements = 1000000;
$lim_depth = 3;
$_ts = microtime(true);
$arr = $g->genRandomArray(10000, 10000, 32768, $lim_depth, $lim_elements);
$total_generated = $lim_elements - $g->lim_elements;
echo (microtime(true) - $_ts) . " sec, generated: $total_generated elements.\n";
```

### What is Generation models?

Generation random keys and values can use 4 models:
* 0 **Simple model** - Is the fastest, 1,2..n for keys and 0-65535 for values.
* 1 **Numeric range model** - Use mt_rand(min,max) for random keys or values.
* 2 **RandomStr model** - Use function genRandomStr for gen. keys or values.
* 3 **User function** - Use user defined function for generation keys or values.

These models are set as follows:

* 0 **Simple model**
```php
$g->setKeysModel();   //set simple keys model (1,2...n)
$g->setValuesModel(); //set simple values model (integer range 0-65535)
```
* 1 **Numeric range model**
```php
$g->setKeysModel(min, max); //set keys model range from min to max (integer)
$g->setValuesModel(min, max); //set values model range from min to max (integer)
```
* 2 **RandomStr model**
```php
$g->setKeysModel(min_len, max_len, $char_str [, $utf8flag] )
$g->setValuesModel(min_len, max_len, $char_str [, $utf8flag] )
```
* 4 **User function model**
```php
$g->setKeysModelFn(callable);
$g->setValuesModelFn(callable);
```
### Example of use callable generation model:
...
