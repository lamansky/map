<?php
namespace Lamansky\Map;

class Map implements \Iterator {

    protected $pairs = [];

    public function rewind () : void {
        reset($this->pairs);
    }

    /**
     * @throws \UnderflowException if there are no more elements left.
     *         Normally one would call the valid() method first to preclude
     *         this possibility.
     * @return mixed
     */
    public function key () {
        $current = current($this->pairs);
        if (!$current) { throw new \UnderflowException(); }
        return $current[0];
    }

    /**
     * @throws \UnderflowException if there are no more elements left.
     *         Normally one would call the valid() method first to preclude
     *         this possibility.
     * @return mixed
     */
    public function current () {
        $current = current($this->pairs);
        if (!$current) { throw new \UnderflowException(); }
        return $current[1];
    }

    public function next () : void {
        next($this->pairs);
    }

    public function valid () : bool {
        return key($this->pairs) !== null;
    }

    /**
     * @return mixed
     */
    public function get ($key) {
        foreach ($this->pairs as $pair) {
            if ($pair[0] === $key) {
                return $pair[1];
            }
        }
        return null;
    }

    public function has ($key) : bool {
        foreach ($this->pairs as $pair) {
            if ($pair[0] === $key) {
                return true;
            }
        }
        return false;
    }

    public function set ($key, $value) : void {
        $pairs_count = count($this->pairs);
        for ($i = 0; $i < $pairs_count; $i++) {
            if ($this->pairs[$i][0] === $key) {
                $this->pairs[$i][1] = $value;
                return;
            }
        }

        $this->pairs[] = [$key, $value];
    }

    public function mergeSet ($key, $value, callable $merge) : void {
        $pairs_count = count($this->pairs);
        for ($i = 0; $i < $pairs_count; $i++) {
            if ($this->pairs[$i][0] === $key) {
                $this->pairs[$i][1] = call_user_func($merge, $this->pairs[$i][1], $value);
                return;
            }
        }

        $this->pairs[] = [$key, $value];
    }

    public function edit ($key, callable $edit) : void {
        $pairs_count = count($this->pairs);
        for ($i = 0; $i < $pairs_count; $i++) {
            if ($this->pairs[$i][0] === $key) {
                $this->pairs[$i][1] = call_user_func($edit, $this->pairs[$i][1]);
                return;
            }
        }

        $this->pairs[] = [$key, call_user_func($edit, null)];
    }

    public function increment ($key, $value) : void {
        foreach ($this->pairs as &$pair) {
            if ($pair[0] === $key) {
                $pair[1] += $value;
                return;
            }
        }
        $this->set($key, $value);
    }

    public function decrement ($key, $value) : void {
        foreach ($this->pairs as &$pair) {
            if ($pair[0] === $key) {
                $pair[1] -= $value;
                return;
            }
        }
        $this->set($key, -$value);
    }

    public function delete ($key) : bool {
        $found = false;
        $_pairs = [];
        foreach ($this->pairs as $pair) {
            if ($pair[0] === $key) {
                $found = true;
            } else {
                $_pairs[] = [$pair[0], $pair[1]];
            }
        }
        $this->pairs = $_pairs;
        return $found;
    }

    public function count () : int {
        return count($this->pairs);
    }

    public function entries () : array {
        return $this->pairs;
    }

    public function keys () : array {
        return array_column($this->pairs, 0);
    }

    public function values () : array {
        return array_column($this->pairs, 1);
    }

    public function map (callable $callback) : array {
        $i = 0;
        return array_map(function ($pair) use ($callback, &$i) {
            return call_user_func($callback, $pair[1], $pair[0], $i++);
        }, $this->pairs);
    }

    public function walk (callable $callback) : void {
        $i = 0;
        array_walk($this->pairs, function (&$pair) use ($callback, &$i) {
            call_user_func_array($callback, [&$pair[1], &$pair[0], $i++]);
        });
    }

    public function sort (callable $sort) : void {
        usort($this->pairs, function ($a, $b) use ($sort) {
            return call_user_func($sort, $a[1], $b[1], $a[0], $b[0]);
        });
    }

    public function sortValuesAsc () : void {
        $this->sort(function ($v1, $v2) {
            return $v1 <=> $v2;
        });
    }

    public function sortValuesDesc () : void {
        $this->sort(function ($v1, $v2) {
            return $v2 <=> $v1;
        });
    }

    public function sortKeysAsc () : void {
        $this->sort(function ($v1, $v2, $k1, $k2) {
            return $k1 <=> $k2;
        });
    }

    public function sortKeysDesc () : void {
        $this->sort(function ($v1, $v2, $k1, $k2) {
            return $k2 <=> $k1;
        });
    }

    public function keepOnlyTheFirst (int $number) : void {
        $this->pairs = array_slice($this->pairs, 0, max(0, $number));
    }

    public function keepOnlyTheLast (int $number) : void {
        $number = max(0, $number);
        $this->pairs = array_slice($this->pairs, -$number, $number);
    }

    public static function fromList (iterable $items, callable $get_key) : Map {
        $map = new static();
        foreach ($items as $item) {
            $map->set(call_user_func($get_key, $item), $item);
        }
        return $map;
    }

    public static function merge (self ...$maps) : Map {
        $new = new static();
        foreach ($maps as $map) {
            foreach ($map as $key => $value) {
                $new->set($key, $value);
            }
        }
        return $new;
    }

    public static function deepMerge (callable $merge, self ...$maps) : Map {
        $new = new static();
        foreach ($maps as $map) {
            foreach ($map as $key => $value) {
                if ($new->has($key)) {
                    $value = call_user_func($merge, $new->get($key, $value), $value);
                }
                $new->set($key, $value);
            }
        }
        return $new;
    }
}
