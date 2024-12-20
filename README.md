# VarDumper for SMF
![SMF 2.1](https://img.shields.io/badge/SMF-2.1-ed6033.svg?style=flat)
![License](https://img.shields.io/github/license/dragomano/vardumper-for-smf)
![Hooks only: Yes](https://img.shields.io/badge/Hooks%20only-YES-blue)
![PHP](https://img.shields.io/badge/PHP-^8.1-blue.svg?style=flat)

* **Tested on:** PHP 8.1.31 / MariaDB 11.2.2
* **Languages:** English, Russian

## Description
This small mod adds the Symfony [VarDumper component](https://github.com/symfony/var-dumper) to work with `dump()` and `dd()` functions (instead of `var_dump()`).

## Examples

```php
$data = [1, 2, 3];
var_dump($data);
```
![](https://user-images.githubusercontent.com/229402/147452006-bd78695f-004b-4582-a4c6-3f5c09a11c0c.png)

```php
$data = [1, 2, 3];
dump($data);
```
![](https://user-images.githubusercontent.com/229402/147452012-c7da482a-3bce-431b-9f94-cf2ccc6757d4.png)