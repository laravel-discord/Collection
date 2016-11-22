<?php
/**
 * Collection
 * Pure PHP implementation based on Laravel's Collections.
 * Copyright 2016 Charlotte Dunois, All Rights Reserved
 *
 * Docs: https://laravel.com/docs/5.2/collections
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Collection/blob/master/LICENSE
**/

namespace CharlotteDunois\Collect;

class Collection {
	private $data = array();
	
	private function __construct($data) {
		$this->data = $data;
	}
	
	static function create($data) {
		$inst = new Collection($data);
		return $inst;
	}
	
	function all() {
		return $this->data;
	}
	
	function avg($closure = NULL) {
		$count = $this->count();
		if($count > 0) {
            return ($this->sum($closure) / $count);
        }
		
		return $this;
	}
	
	function chunk($numitems, $preserve_keys = false) {
		return Collection::create(array_chunk($this->data, $numitems, $preserve_keys));
	}
	
	function collapse() {
		$new = array();
        foreach($this->data as $values) {
            if(is_a($values, 'Collection')) {
                $values = $values->all();
            } elseif(!is_array($values)) {
                continue;
            }
			
            $new = array_merge($new, $values);
        }
		
        return Collection::create($new);
	}
	
	function column($key, $index = NULL) {
		return Collection::create(array_column($this->data, $key, $index));
	}
	
	function combine($values) {
		return Collection::create(array_combine($this->data, $values));
	}
	
	function contains($item, $value = "") {
		if(!empty($item)) {
			return $this;
		}
		
		foreach($this->data as $key => $val) {
			if($item instanceof Closure) {
				$bool = $item($val, $key);
				return $bool;
			} else {
				if(!empty($value)) {
					if($key == $item AND $val == $value) {
						return true;
					}
				} else {
					if($val == $item) {
						return true;
					}
				}
			}
		}
	}
	
	function count() {
		return count($this->data);
	}
	
	function diff($arr) {
		if(is_a($arr, 'Collection')) {
			$arr = $arr->all();
		}
		
		return Collection::create(array_diff($this->data, $arr));
	}
	
	function each(callable $closure) {
		foreach($this->data as $key => $val) {
			$feed = $closure($val, $key);
			if($feed === false) {
				break;
			}
		}
		
		return $this;
	}
	
	function every($nth, $offset = 0) {
		if(!is_int($nth)) {
			return $this;
		}
		
		$new = array();
		for($i = $offset; $i < count($this->data); $i += $nth) {
			$new[] = $this->data[$i];
		}
		
		return Collection::create($new);
	}
	
	function except($keys) {
		if(!is_array($keys)) {
			$keys = array($keys);
		}
		
		$new = array();
		foreach($this->data as $key => $val) {
			if(!in_array($key, $keys)) {
				$new[$key] = $val;
			}
		}
		
		return Collection::create($new);
	}
	
	function filter(callable $closure) {
		$new = array();
		foreach($this->data as $key => $val) {
			$feed = $closure($val, $key);
			if($feed === true) {
				$new[$key] = $val;
			}
		}
		
		return Collection::create($new);
	}
	
	function first(callable $closure) {
		foreach($this->data as $key => $val) {
			$feed = $closure($val, $key);
			if($feed === true) {
				return $val;
			}
		}
		
		return false;
	}
	
	function flatten($depth = 0) {
		$data = $this->flatten_do($this->data, $depth);
		return Collection::create($data);
	}
	
	function flip() {
		$data = @array_flip($this->data);
		return Collection::create($data);
	}
	
	function forget($key) {
		$data = $this->data;
		$data[$key] = NULL;
		unset($data[$key]);
		return Collection::create($data);
	}
	
	function forPage($page, $numitems) {
		$start = ($page * $numitems) - $numitems - 1;
		
		$data = array_values($this->data);
		$new = array();
		for($i = $start; $i <= $start + $numitems; $i++) {
			$new[] = $data[$i];
		}
		
		return Collection::create($new);
	}
	
	function get($key, $default = NULL) {
		if(isset($this->data[$key])) {
			return $this->data[$key];
		}
		
		if($default instanceof Closure) {
			return $default();
		} else {
			return $default;
		}
	}
	
	function groupBy($column) {
		if(empty($column)) {
			return $this;
		}
		
		$new = array();
		foreach($this->data as $key => $val) {
			if($column instanceof Closure) {
				$key = $column($val, $key);
			} else {
				$key = $val[$column];
			}
			
			$new[$key][] = $val;
		}
		
		return Collection::create($new);
	}
	
	function has($key) {
		return (bool) isset($this->data[$key]);
	}
	
	function implode($col, $glue = ', ') {
		$data = "";
		foreach($this->data as $key => $val) {
			if(is_array($val)) {
				$data .= $glue.$val[$col];
			} else {
				$data .= $col.$val;
			}
		}
		
		return $data;
	}
	
	function intersect($arr) {
		if(is_a($arr, 'Collection')) {
			$arr = $arr->all();
		}
		
		return Collection::create(array_intersect($this->data, $arr));
	}
	
	function isEmpty() {
		return (bool) empty($this->data);
	}
	
	function keyBy($col) {
		$data = array();
		foreach($this->data as $key => $val) {
			if(!is_array($val)) {
				continue;
			}
			
			if($col instanceof Closure) {
				$k = $col($val, $key);
			} else {
				$k = $val[$col];
			}
			
			$data[$k] = $val;
		}
		
		return Collection::create($data);
	}
	
	function keys() {
		return Collection::create(array_keys($this->data));
	}
	
	function last(callable $closure) {
		$data = false;
		foreach($this->data as $key => $val) {
			$feed = $closure($val, $key);
			if($feed === true) {
				$data = $val;
			}
		}
		
		return $data;
	}
	
	function map(callable $closure) {
		$keys = array_keys($this->data);
		$items = array_map(closure, $this->data, $keys);
		return Collection::create(array_combine($keys, $items));
	}
	
	function max($key = '') {
		if(!empty($key)) {
			$data = array_column($this->data, $key);
		} else {
			$data = $this->data;
		}
		
		return Collection::create(max($data));
	}
	
	function merge(array $arr) {
		return Collection::create(array_merge($this->data, $arr));
	}
	
	function min($key = '') {
		if(!empty($key)) {
			$data = array_column($this->data, $key);
		} else {
			$data = $this->data;
		}
		
		return Collection::create(min($data));
	}
	
	function only($keys) {
		if(!is_array($keys)) {
			$keys = array($keys);
		}
		
		$new = array();
		foreach($this->data as $key => $val) {
			if(in_array($key, $keys)) {
				$new[$key] = $val;
			}
		}
		
		return Collection::create($new);
	}
	
	function pop() {
		$data = $this->data;
		return array_pop($data);
	}
	
	function prepend($value, $key = NULL) {
		if(!empty($key) AND !is_int($key)) {
			$data = array_unshift($this->data, $value);
		} else {
			$data = array_merge(array($key => $value), $this->data);
		}
		
		return Collection::create($data);
	}
	
	function pull($key) {
		$value = $this->data[$key];
		$this->data[$key] = NULL;
		unset($this->data[$key]);
		
		return $value;
	}
	
	function push($value, $key = NULL) {
		if(!empty($key) AND !is_int($key)) {
			$data = array_push($this->data, $value);
		} else {
			$data = array_merge($this->data, array($key => $value));
		}
		
		return Collection::create($data);
	}
	
	function put($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}
	
	function random($num = 1) {
		return Collection::create(array_rand($this->data, $num));
	}
	
	function reduce(callable $closure, $carry = NULL) {
		foreach($this->data as $val) {
			$carry = $closure($carry, $val);
		}
		
		return $carry;
	}
	
	function reject(callable $closure) {
		$new = array();
		foreach($this->data as $key => $val) {
			$feed = $closure($val, $key);
			if($feed !== true) {
				$new[$key] = $val;
			}
		}
		
		return Collection::create($new);
	}
	
	function reverse($preserve_keys = false) {
		return Collection::create(array_reverse($this->data, $preserve_keys));
	}
	
	function search($needle, $strict = false) {
		if($needle instanceof Closure) {
			foreach($this->data as $key => $val) {
				$feed = $closure($val, $key);
				if($feed === true) {
					return $key;
				}
			}
		} else {
			return array_search($needle, $this->data, $strict);
		}
		
		return false;
	}
	
	function shift() {
		return array_shift($this->data);
	}
	
	function shuffle() {
		$data = $this->data;
		shuffle($data);
		return Collection::create($data);
	}
	
	function slice($offset, $limit = NULL, $preserve_keys = false) {
		$data = $this->data;
		return Collection::create(array_slice($data, $offset, $limit, $preserve_keys));
	}
	
	function sort($closure = NULL, $options = SORT_REGULAR) {
		$data = $this->data;
		if($closure instanceof Closure) {
			uasort($data, $closure);
		} else {
			asort($data);
		}
		
		return Collection::create($data);
	}
	
	function sortBy($sortkey, $options = SORT_REGULAR, $descending = false) {
		$sortkey = $this->value_retriever($sortkey);
		
		$new = array();
		foreach($this->data as $key => $val) {
			$new[$key] = $sortkey($value, $key);
		}
		
		if($descending === true) {
			arsort($new, $options);
		} else {
			asort($new, $options);
		}
		
		foreach(array_keys($new) as $key) {
			$new[$key] = $this->data[$key];
		}
		
		return Collection::create($new);
	}
	
	function sortByDesc($sortkey, $options = SORT_REGULAR) {
		return $this->sortBy($sortkey, $options, true);
	}
	
	function splice($offset, $length = NULL, $replacement = array()) {
		return Collection::create(array_splice($this->data, $offset, $length, $replacement));
	}
	
	function sum($closure = NULL) {
		if($closure === NULL) {
			return array_sum($this->data);
		}
		
		$closure = $this->value_retriever($closure);
		
		return $this->reduce(function ($result, $item) use ($closure) {
            return $result += $closure($item);
        }, 0);
	}
	
	function take($limit) {
		if($limit < 0) {
			return $this->slice($limit, abs($limit));
		}
		
		return $this->slice(0, $limit);
	}
	
	function toArray() {
        return array_map(function ($value) {
			if($value instanceof Arrayable) {
				return $value->toArray();
			} else {
				return $value;
			}
        }, $this->data);
    }
	
	function toJSON($options = 0) {
        return json_encode($this->json_serialize(), $options);
    }
	
	function transform(callable $closure) {
		$this->data = $this->map($closure)->all();
        return $this;
	}
	
	function unique($key) {
		if($key === NULL) {
			return Collection::create(array_unique($this->data, SORT_REGULAR));
		}
		
		$key = $this->valueRetriever($key);
		
		$exists = array();
		return $this->reject(function ($item) use ($key, &$exists) {
			$id = $key($item);
			if(in_array($id, $exists)) {
				return true;
			}
			$exists[] = $id;
        });
	}
	
	function values() {
		return Collection::create(array_values($this->data));
	}
	
	function where($key, $value, $strict = false) {
		$data = array();
		foreach($this->data as $val) {
			if($strict === true) {
				$bool = ($val[$key] === $value);
			} else {
				$bool = ($val[$key] == $value);
			}
			
			if($bool === true) {
				$data[] = $val;
			}
		}
		
		return Collection::create($data);
	}
	
	function zip($arr) {
		if(!is_array($zip)) {
			return $this;
		}
		
		$data = $this->data;
		foreach($arr as $key => $val) {
			if(isset($data[$key])) {
				$data[$key] = array($data[$key], $val);
			} else {
				$data[$key] = array($val);
			}
		}
		
		return Collection::create($data);
	}
	
	private function data_get($target, $key, $default = NULL) {
        if(is_null($key)) {
            return $target;
        }
		
		if(!is_array($key)) {
			$key = explode('.', $key);
		}
        
        while(($segment = array_shift($key)) !== null) {
            if($segment === '*') {
                if($target instanceof Collection) {
                    $target = $target->all();
                } elseif(!is_array($target)) {
                    return value($default);
                }
				
                $result = array_column($target, $key);
				if(in_array('*', $key)) {
					return Collection::create($result)->collapse();
				} else {
					return $result;
				}
            }
			
            if(isset($target[$segment])) {
                $target = $target[$segment];
            } elseif(is_object($target) AND isset($target->$segment)) {
                $target = $target->$segment;
            } else {
				if($value instanceof Closure) {
					return $value();
				} else {
					return $value;
				}
            }
        }
        return $target;
    }
	
	private function flatten_do($array, $depth, $in_depth = 0) {
		$data = array();
		foreach($array as $val) {
			if(is_array($val) AND ($depth == 0 OR $depth > $in_depth)) {
				$data = array_merge($data, $this->flatten_do($val, $depth, ($in_depth + 1)));
			} else {
				$data[] = $val;
			}
		}
		
		return $data;
	}
	
	private function json_serialize() {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->json_serialize();
            } else {
                return $value;
            }
        }, $this->data);
    }
	
	protected function value_retriever($value) {
        if($value instanceof Closure) {
            return $value;
        }
		
        return function ($item) use ($value) {
            return $this->data_get($item, $value);
        };
    }
}
