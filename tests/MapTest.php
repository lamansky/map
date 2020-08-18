<?php
namespace Lamansky\Map\Test;
use Lamansky\Map\Map;
use PHPUnit\Framework\TestCase;

final class MapTest extends TestCase {

    public function testGetSet () : void {
        $map = new Map();
        $map->set('key', 'value');
        $this->assertSame($map->get('key'), 'value');
        $this->assertSame($map->get('other'), null);
        $map->set(null, 'nothing');
        $this->assertSame($map->get('key'), 'value');
        $this->assertSame($map->get(null), 'nothing');
        $this->assertSame($map->get(false), null);
    }

    public function testIteration () : void {
        $map = new Map();
        $map->set('a', 1);
        $map->set('b', 2);
        $i = 0;
        $expected = [['a', 1], ['b', 2]];
        foreach ($map as $key => $value) {
            $this->assertSame($key, $expected[$i][0]);
            $this->assertSame($value, $expected[$i][1]);
            $i++;
        }
        $this->assertSame($i, 2);
    }

    public function testHas () : void {
        $map = new Map();
        $this->assertSame($map->has('a'), false);
        $map->set('a', 1);
        $this->assertSame($map->has('a'), true);
        $this->assertSame($map->has('b'), false);
        $map->delete('a');
        $this->assertSame($map->has('a'), false);
    }

    public function testMergeSet () : void {
        $map = new Map();
        $map->set('key', [1]);
        $map->mergeSet('key', [2], 'array_merge');
        $this->assertSame($map->get('key'), [1, 2]);
    }

    public function testEdit () : void {
        $map = new Map();
        $map->set('number', 5);
        $map->edit('number', function ($n) {
            return $n * 3;
        });
        $this->assertSame($map->get('number'), 15);
        $map->edit('unset', function ($x) {
            $this->assertSame($x, null);
            return 'ran';
        });
        $this->assertSame($map->get('unset'), 'ran');
    }

    public function testDelete () : void {
        $map = new Map();
        $this->assertSame($map->has('a'), false);
        $map->set('a', 1);
        $this->assertSame($map->has('a'), true);
        $this->assertSame($map->delete('a'), true);
        $this->assertSame($map->has('a'), false);
        $this->assertSame($map->delete('a'), false);
    }

    public function testCount () : void {
        $map = new Map();
        $this->assertSame($map->count(), 0);
        $map->set('a', 1);
        $this->assertSame($map->count(), 1);
        $map->set(null, 0);
        $this->assertSame($map->count(), 2);
        $map->delete(null);
        $this->assertSame($map->count(), 1);
    }

    public function testEntries () : void {
        $map = new Map();
        $this->assertSame($map->entries(), []);
        $map->set('key', 'value');
        $entries = $map->entries();
        $this->assertSame($entries, [['key', 'value']]);
        $this->assertNotSame($entries, ['key', 'value']);
        $entries['key2'] = 'value2';
        $this->assertSame($map->entries(), [['key', 'value']]);
    }

    public function testKeys () : void {
        $map = new Map();
        $key = [];
        $this->assertSame($map->keys(), []);
        $map->set($key, 1);
        $this->assertSame($map->keys(), [$key]);
        $map->delete($key);
        $this->assertSame($map->keys(), []);
        $map->set('key', 'value');
        $this->assertSame($map->keys(), ['key']);
    }

    public function testValues () : void {
        $map = new Map();
        $this->assertSame($map->values(), []);
        $map->set('a', 1);
        $values = $map->values();
        $this->assertSame($values, [1]);
        $map->set('b', 2);
        $this->assertSame($values, [1]);
        $this->assertSame($map->values(), [1, 2]);
        $map->delete('a');
        $this->assertSame($map->values(), [2]);
        $map->delete('b');
        $value = [1, 2];
        $map->set('c', $value);
        $this->assertSame($map->values(), [[1, 2]]);
        $value[] = 3;
        $this->assertSame($value, [1, 2, 3]);
        $this->assertSame($map->values(), [[1, 2]]);
    }

    public function testMap () : void {
        $map = new Map();
        $map->set(2, 5);
        $map->set(3, 3);
        $this->assertSame(
            $map->map(function ($v, $k, $i) {
                return ($v * $k) + $i;
            }),
            [10, 10]
        );
        $this->assertSame($map->get(2), 5);
    }

    public function testWalk () : void {
        $map = new Map();
        $map->set(2, 5);
        $map->set(3, 3);
        $map->walk(function (&$v, &$k, $i) {
            $v = ($v * $k) + $i;
            $k *= 2;
        });
        $this->assertSame($map->get(2), null);
        $this->assertSame($map->get(3), null);
        $this->assertSame($map->get(4), 10);
        $this->assertSame($map->get(6), 10);
    }

    public function testSortValuesAsc () : void {
        $map = new Map();
        $map->set('a', 2);
        $map->set('b', 1);
        $this->assertSame($map->values(), [2, 1]);
        $this->assertNotSame($map->values(), [1, 2]);
        $map->sortValuesAsc();
        $this->assertSame($map->values(), [1, 2]);
        $this->assertNotSame($map->values(), [2, 1]);
        $this->assertSame($map->keys(), ['b', 'a']);
    }

    public function testSortValuesDesc () : void {
        $map = new Map();
        $map->set('a', 1);
        $map->set('b', 2);
        $this->assertSame($map->values(), [1, 2]);
        $this->assertNotSame($map->values(), [2, 1]);
        $map->sortValuesDesc();
        $this->assertSame($map->values(), [2, 1]);
        $this->assertNotSame($map->values(), [1, 2]);
        $this->assertSame($map->keys(), ['b', 'a']);
    }

    public function testSortKeysAsc () : void {
        $map = new Map();
        $map->set('b', 2);
        $map->set('a', 1);
        $this->assertSame($map->keys(), ['b', 'a']);
        $this->assertNotSame($map->keys(), ['a', 'b']);
        $map->sortKeysAsc();
        $this->assertSame($map->keys(), ['a', 'b']);
        $this->assertNotSame($map->keys(), ['b', 'a']);
        $this->assertSame($map->values(), [1, 2]);
    }

    public function testSortKeysDesc () : void {
        $map = new Map();
        $map->set('a', 1);
        $map->set('b', 2);
        $this->assertSame($map->keys(), ['a', 'b']);
        $this->assertNotSame($map->keys(), ['b', 'a']);
        $map->sortKeysDesc();
        $this->assertSame($map->keys(), ['b', 'a']);
        $this->assertNotSame($map->keys(), ['a', 'b']);
        $this->assertSame($map->values(), [2, 1]);
    }

    public function testSortCustom () : void {
        $map = new Map();
        $map->set('a', [1, 2]);
        $map->set('b', [2, 1]);
        $map->sort(function ($v1, $v2) {
            return $v1[1] <=> $v2[1];
        });
        $this->assertSame($map->keys(), ['b', 'a']);
        $map->sort(function ($v1, $v2, $k1, $k2) {
            return $k1 <=> $k2;
        });
        $this->assertSame($map->keys(), ['a', 'b']);
    }

    public function testKeepOnlyTheFirst () : void {
        $map = new Map();
        $map->set('a', 1);
        $map->set('b', 2);
        $map->set('c', 3);
        $this->assertSame($map->keys(), ['a', 'b', 'c']);
        $map->keepOnlyTheFirst(2);
        $this->assertSame($map->keys(), ['a', 'b']);
        $map->keepOnlyTheFirst(3);
        $this->assertSame($map->keys(), ['a', 'b']);
    }

    public function testKeepOnlyTheLast () : void {
        $map = new Map();
        $map->set('a', 1);
        $map->set('b', 2);
        $map->set('c', 3);
        $this->assertSame($map->keys(), ['a', 'b', 'c']);
        $map->keepOnlyTheLast(2);
        $this->assertSame($map->keys(), ['b', 'c']);
        $map->keepOnlyTheLast(3);
        $this->assertSame($map->keys(), ['b', 'c']);
    }

    public function testFromList () : void {
        $arr = [[1, 2], [2, 4]];
        $map = Map::fromList($arr, function ($value) {
            return $value[0];
        });
        $this->assertSame($map->keys(), [1, 2]);
        $this->assertSame($map->get(1), [1, 2]);
        $this->assertSame($map->get(2), [2, 4]);
    }
}
