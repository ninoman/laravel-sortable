# Make your Laravel models Sortable in a moment


[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/ninoman/laravel-sortable.svg?style=flat-square)](https://packagist.org/packages/ninoman/laravel-sortable)

There are lots of situations when you need a sorting functionality for your models. Of course everyone wants simple package to cover all common use cases.

Know what? ***You've found it!***

This package will automatically apply sort index for your newly created models, and also handle all resorting stuff.

## Installation

You can install this package using composer. Just run a command bellow.

```
composer require ninoman/laravel-sortable
```

## Usage

It's very easy to start with this package. Just use `Ninoman\LaravelSortable\Sortable` trait in your model, and add `sort_index` column in your models migration.

But of course every cool package should be configurable. This one is too (ohh and also it's configurable :) )

## Configurations

So if you want to change the default sorting column, you should override a `$sortIndexColumn` property in your model   


##### Example

```php
use Ninoman\LaravelSortable\Sortable;

class MyModel extends Eloquent
{
    use Sortable;
    
    public $sortIndexColumn = 'order';
    
    ...
}
```


How I've mentioned above your newly created model will be sorted automatically, but in case you don't want it, you always can set property `$setSortIndexOnCreating` to `false` 


##### Example

```php
use Ninoman\LaravelSortable\Sortable;

class MyModel extends Eloquent
{
    use Sortable;
    
    public $setSortIndexOnCreating = false;
    
    ...
}
```


Let's imagine a situation, when you have **Users** and every **user** has many **Posts**. In this kind of situation if you would like to add sorting for **Posts**, it will be weird to sort all **Posts** together, of course you will want to sort them for each user (grouped by `user_id`).
 
It can be easily done by adding `parent_id` column to your model. And your newly created **Posts** now will be sorted uniquely for their user.


##### Example

```php
use Ninoman\LaravelSortable\Sortable;

class Post extends Eloquent
{
    use Sortable;
    
    public $parentColumn = 'user_id';
    
    ...
}
```    


Trait also will add some functionality to your models. For example, if you want get your models sorted, just apply `sorted` scope on your models.


```php

/*
    Models 
    
    ['id' => 1, 'sort_index' => 2];
    ['id' => 2, 'sort_index' => 3];
    ['id' => 3, 'sort_index' => 1];
*/

MyModel::pluck('id'); // [1, 2, 3]
MyModel::sorted()->pluck('id'); // [3, 1, 2]

```

Also you can use `sortedDesc` scope, which how you have guessed will order models in descending order.

##### There are some shorthands for you too. 

If you have two models and want to swap them:
```php 
MyModel::swapSort($modelOne, $modelTwo);
```

If you want to manipulate your one model's sorting you can use those methods:
```php 
$myModel->moveSortIndexDown();
$myModel->moveSortIndexUp();
$myModel->toSortingTop();
$myModel->toSortingBottom();
```