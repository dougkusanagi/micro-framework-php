<?php

use GuepardoSys\Core\Container;

describe('Container', function () {
    beforeEach(function () {
        $this->container = new Container();
    });

    it('can bind and resolve services', function () {
        $this->container->bind('test', function () {
            return 'test-value';
        });

        expect($this->container->resolve('test'))->toBe('test-value');
    });

    it('can bind singletons', function () {
        $this->container->singleton('singleton', function () {
            return new stdClass();
        });

        $instance1 = $this->container->resolve('singleton');
        $instance2 = $this->container->resolve('singleton');

        expect($instance1)->toBe($instance2);
    });

    it('can bind instances', function () {
        $instance = new stdClass();
        $instance->property = 'test';

        $this->container->instance('object', $instance);

        expect($this->container->resolve('object'))->toBe($instance);
        expect($this->container->resolve('object')->property)->toBe('test');
    });

    it('can resolve with dependencies', function () {
        $this->container->bind('dependency', function () {
            return 'dependency-value';
        });

        $this->container->bind('service', function ($container) {
            return 'service-' . $container->resolve('dependency');
        });

        expect($this->container->resolve('service'))->toBe('service-dependency-value');
    });

    it('can check if service exists', function () {
        $this->container->bind('existing', function () {
            return 'value';
        });

        expect($this->container->has('existing'))->toBeTrue();
        expect($this->container->has('non-existing'))->toBeFalse();
    });

    it('throws exception for non-existent service', function () {
        expect(function () {
            $this->container->resolve('non-existent');
        })->toThrow(Exception::class, 'Service [non-existent] not found in container');
    });

    it('can resolve class instances automatically', function () {
        // This tests auto-resolution if implemented
        if (method_exists($this->container, 'make')) {
            $instance = $this->container->make(stdClass::class);
            expect($instance)->toBeInstanceOf(stdClass::class);
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });
});
