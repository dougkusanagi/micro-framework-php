<?php

use GuepardoSys\Core\Security\CsrfToken;

describe('CSRF Token', function () {
    beforeEach(function () {
        // Start session for testing
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear any existing CSRF token
        unset($_SESSION['_token']);
    });

    afterEach(function () {
        // Clean up session
        unset($_SESSION['_token']);
    });

    it('can generate CSRF token', function () {
        $token = CsrfToken::generate();

        expect($token)->toBeString();
        expect(strlen($token))->toBeGreaterThan(0);
        expect($_SESSION['_token'])->toBe($token);
    });

    it('generates different tokens each time', function () {
        $token1 = CsrfToken::generate();
        $token2 = CsrfToken::generate();

        expect($token1)->not->toBe($token2);
    });

    it('can validate correct token', function () {
        $token = CsrfToken::generate();

        expect(CsrfToken::validate($token))->toBeTrue();
    });

    it('rejects invalid token', function () {
        CsrfToken::generate();

        expect(CsrfToken::validate('invalid_token'))->toBeFalse();
        expect(CsrfToken::validate(''))->toBeFalse();
        expect(CsrfToken::validate(null))->toBeFalse();
    });

    it('rejects token when no session token exists', function () {
        expect(CsrfToken::validate('any_token'))->toBeFalse();
    });

    it('can get current token', function () {
        $generated = CsrfToken::generate();
        $current = CsrfToken::get();

        expect($current)->toBe($generated);
    });

    it('returns null when no token exists', function () {
        expect(CsrfToken::get())->toBeNull();
    });

    it('can regenerate token', function () {
        $original = CsrfToken::generate();
        $regenerated = CsrfToken::regenerate();

        expect($regenerated)->not->toBe($original);
        expect(CsrfToken::get())->toBe($regenerated);
    });

    it('can check if token exists', function () {
        expect(CsrfToken::exists())->toBeFalse();

        CsrfToken::generate();

        expect(CsrfToken::exists())->toBeTrue();
    });

    it('can create hidden form field', function () {
        $token = CsrfToken::generate();
        $field = CsrfToken::field();

        expect($field)->toBeString();
        expect($field)->toContain('type="hidden"');
        expect($field)->toContain('name="_token"');
        expect($field)->toContain('value="' . $token . '"');
    });

    it('can create meta tag', function () {
        $token = CsrfToken::generate();
        $meta = CsrfToken::meta();

        expect($meta)->toBeString();
        expect($meta)->toContain('<meta');
        expect($meta)->toContain('name="csrf-token"');
        expect($meta)->toContain('content="' . $token . '"');
    });

    it('validates token from request data', function () {
        $token = CsrfToken::generate();

        $_POST['_token'] = $token;
        expect(CsrfToken::validateRequest())->toBeTrue();

        $_POST['_token'] = 'invalid';
        expect(CsrfToken::validateRequest())->toBeFalse();

        unset($_POST['_token']);
    });

    it('validates token from headers', function () {
        $token = CsrfToken::generate();

        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
        expect(CsrfToken::validateRequest())->toBeTrue();

        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'invalid';
        expect(CsrfToken::validateRequest())->toBeFalse();

        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    });

    it('can clear token', function () {
        CsrfToken::generate();
        expect(CsrfToken::exists())->toBeTrue();

        CsrfToken::clear();
        expect(CsrfToken::exists())->toBeFalse();
    });

    it('generates tokens with sufficient entropy', function () {
        $tokens = [];

        // Generate multiple tokens and check uniqueness
        for ($i = 0; $i < 100; $i++) {
            $token = CsrfToken::generate();
            expect(in_array($token, $tokens))->toBeFalse();
            $tokens[] = $token;
        }
    });

    it('handles missing session gracefully', function () {
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        expect(function () {
            CsrfToken::generate();
        })->not->toThrow(Exception::class);
    });
});
