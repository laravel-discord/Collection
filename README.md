# Collection
This is a Pure PHP implementation based on Laravel's Collections. The usage isn't much different from the Laravel Collections. The docs on laravel.com can be used with this. There is no `collect` helper function - use `Collection::create` instead.

# Usage
Include the file and make a new instance by passing an array to `Collection::create`.

    $collection = CharlotteDunois\Collect\Collection::create(array(1, 2, 3, 4));

# Laravel Docs
https://laravel.com/docs/5.1/collections
