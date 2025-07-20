<?php

require_once __DIR__ . '/../../src/Core/FactoryInterface.php';

use GuepardoSys\Core\BaseFactory;

describe('BaseFactory', function () {
    class DummyFactory extends BaseFactory {
        public function definition(): array {
            return [
                'foo' => 'bar',
                'num' => 42
            ];
        }
        public function modelClass() { return null; }
    }

    it('makes a single item', function () {
        $factory = new DummyFactory();
        $item = $factory->make();
        expect($item['foo'])->toBe('bar');
        expect($item['num'])->toBe(42);
    });

    it('makes with attribute overrides', function () {
        $factory = new DummyFactory();
        $item = $factory->make(['foo' => 'baz']);
        expect($item['foo'])->toBe('baz');
        expect($item['num'])->toBe(42);
    });

    it('bulk makes items', function () {
        $factory = new DummyFactory();
        $items = $factory->bulkMake(3);
        expect(count($items))->toBe(3);
        foreach ($items as $item) {
            expect($item['foo'])->toBe('bar');
        }
    });
}); 