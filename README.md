# Make your Laravel models Sortable in a moment


[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

There are lots of situations when you need a sorting functionality for your models. Of course everyone wants simple package to cover all common use cases.

Know what? ***You've found it!***

This package will automatically apply sort index for your newly created models, and also handle all resorting stuff.


## Installation

You can install this package using composer. Just run a command bellow.

```
composer require ninoman/laravel-sortable
```


## Usage

It's very easy to start with this package. Just use `Ninoman\LaravelSortable\Sortable` trait in your model, and add `sort_index` column in your models migration and into `$fillable` property.

```php
use Ninoman\LaravelSortable\Sortable;

class MyModel extends Eloquent
{
    use Sortable;
    
    protected $fillable = [
        ...
        'sort_index',
        ...
    ];
    
    ...
}



class CreateMyModelsTable extends Migration
{
    public function up()
    {
        Schema::create('my_models', function (Blueprint $table) {
            ...
            $table->unsignedInteger('sort_index');
            ...
        });
    }

    public function down()
    {
        Schema::dropIfExists('my_models');
    }
}

```

But of course every cool package should be configurable. This one is too :)


## Configurations

So if you want to change the sorting column (by default it is `sort_index`), you should set a `$sortIndexColumn` property in your model. 

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
 
It can be easily done by setting `$sortingParentColumn` property of your model name of the column by which you want to group your sorting. And your newly created **Posts** now will be sorted uniquely for their user.

```php
use Ninoman\LaravelSortable\Sortable;

class Post extends Eloquent
{
    use Sortable;
    
    public $sortingParentColumn = 'user_id';
    
    ...
}
```    


You also can configure start index of your models sorting, by default it will start from 1. To change it you should set `$startSortingFrom` property the number from which you want to start sorting.  

```php
use Ninoman\LaravelSortable\Sortable;

class MyModel extends Eloquent
{
    use Sortable;
    
    public $startSortingFrom = 0;
    
    ...
}

MyModel::create([...]); // sort_index 0
MyModel::create([...]); // sort_index 1
MyModel::create([...]); // sort_index 2
```


## Helpful stuff

##### Simple but useful scopes
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

##### Methods
Be sure this methods will make your life easier. 

If you have two models and want to swap them use `swapSort` method:
```php 
MyModel::swapSort($modelOne, $modelTwo);
```

In order to manipulate your one model's sorting you can use those methods:
```php 
$myModel->moveSortIndexDown();
$myModel->moveSortIndexUp();
$myModel->toSortingTop();
$myModel->toSortingBottom();
```

And of course you can just update your model's property, which is responsible for sort index and all other entities will be reordered automatically.
```php
use Ninoman\LaravelSortable\Sortable;

class MyModel extends Eloquent
{
    use Sortable;
    
    public $sortIndexColumn = 'order';
    
    ...
}

$one = $myModel::create([...]); //order 1 
$two = $myModel::create([...]); //order 2 
$three = $myModel::create([...]); //order 3 
$four = $myModel::create([...]); //order 4

$four->update(['order' => 2]);
//$one -> order 1;
//$four -> order 2;
//$two -> order 3;
//$three -> order 4;
```

##### Conclusions

It's a lightweight, easy to use package which you can easily integrate into your application. Feel free to report about issues and possible improvements.

Thank you!