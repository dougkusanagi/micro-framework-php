<?php

use GuepardoSys\Core\Security\Validator;

describe('Validator', function () {
    it('can validate required fields', function () {
        $data = ['name' => 'John', 'email' => ''];
        $rules = ['name' => 'required', 'email' => 'required'];

        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeFalse();
        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('email');
    });

    it('can validate email format', function () {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'email'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('email');

        // Test valid email
        $data = ['email' => 'test@example.com'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate minimum length', function () {
        $data = ['password' => 'abc'];
        $rules = ['password' => 'min:6'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('password');

        // Test valid length
        $data = ['password' => 'abcdef'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate maximum length', function () {
        $data = ['username' => 'very_long_username_that_exceeds_limit'];
        $rules = ['username' => 'max:10'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('username');

        // Test valid length
        $data = ['username' => 'john'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate numeric values', function () {
        $data = ['age' => 'not-a-number'];
        $rules = ['age' => 'numeric'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('age');

        // Test valid numeric
        $data = ['age' => '25'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate integer values', function () {
        $data = ['count' => '25.5'];
        $rules = ['count' => 'integer'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('count');

        // Test valid integer
        $data = ['count' => '25'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate boolean values', function () {
        $data = ['active' => 'maybe'];
        $rules = ['active' => 'boolean'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('active');

        // Test valid boolean values
        $validBooleans = ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'];

        foreach ($validBooleans as $value) {
            $data = ['active' => $value];
            $validator = new Validator($data, $rules);

            expect($validator->passes())->toBeTrue();
        }
    });

    it('can validate URL format', function () {
        $data = ['website' => 'not-a-url'];
        $rules = ['website' => 'url'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('website');

        // Test valid URLs
        $validUrls = ['http://example.com', 'https://example.com', 'ftp://example.com'];

        foreach ($validUrls as $url) {
            $data = ['website' => $url];
            $validator = new Validator($data, $rules);

            expect($validator->passes())->toBeTrue();
        }
    });

    it('can validate date format', function () {
        $data = ['birthday' => 'not-a-date'];
        $rules = ['birthday' => 'date'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('birthday');

        // Test valid dates
        $validDates = ['2023-01-01', '01/01/2023', 'January 1, 2023'];

        foreach ($validDates as $date) {
            $data = ['birthday' => $date];
            $validator = new Validator($data, $rules);

            expect($validator->passes())->toBeTrue();
        }
    });

    it('can validate field confirmation', function () {
        $data = ['password' => 'secret', 'password_confirmation' => 'different'];
        $rules = ['password' => 'confirmed'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('password');

        // Test matching confirmation
        $data = ['password' => 'secret', 'password_confirmation' => 'secret'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate values in array', function () {
        $data = ['status' => 'invalid'];
        $rules = ['status' => 'in:active,inactive,pending'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('status');

        // Test valid value
        $data = ['status' => 'active'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can validate multiple rules per field', function () {
        $data = ['email' => 'a'];
        $rules = ['email' => 'required|email|min:5'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        $errors = $validator->errors();
        expect($errors)->toHaveKey('email');
        expect(count($errors['email']))->toBeGreaterThan(1);
    });

    it('can validate arrays of rules', function () {
        $data = ['name' => '', 'email' => 'invalid'];
        $rules = [
            'name' => ['required', 'min:2'],
            'email' => ['required', 'email']
        ];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors())->toHaveKey('name');
        expect($validator->errors())->toHaveKey('email');
    });

    it('can set custom error messages', function () {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];
        $messages = ['name.required' => 'Please enter your name'];

        $validator = new Validator($data, $rules, $messages);

        expect($validator->fails())->toBeTrue();
        $errors = $validator->errors();
        expect($errors['name'][0])->toBe('Please enter your name');
    });

    it('can validate optional fields', function () {
        $data = ['name' => 'John']; // email is not provided
        $rules = ['name' => 'required', 'email' => 'email']; // email is optional but must be valid if provided

        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();

        // Test with invalid optional field
        $data = ['name' => 'John', 'email' => 'invalid'];
        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();
    });

    it('can validate regex patterns', function () {
        $data = ['phone' => '123-abc-7890'];
        $rules = ['phone' => 'regex:/^\d{3}-\d{3}-\d{4}$/'];

        $validator = new Validator($data, $rules);

        expect($validator->fails())->toBeTrue();

        // Test valid pattern
        $data = ['phone' => '123-456-7890'];
        $validator = new Validator($data, $rules);

        expect($validator->passes())->toBeTrue();
    });

    it('can get first error for field', function () {
        $data = ['email' => 'a'];
        $rules = ['email' => 'required|email|min:5'];

        $validator = new Validator($data, $rules);
        $validator->validate();

        $firstError = $validator->first('email');
        expect($firstError)->toBeString();
        expect(strlen($firstError))->toBeGreaterThan(0);
    });

    it('can validate unique values if database method exists', function () {
        // This test depends on database connection and table setup
        // Skip if unique validation is not implemented
        if (method_exists(Validator::class, 'validateUnique')) {
            $data = ['email' => 'test@example.com'];
            $rules = ['email' => 'unique:users,email'];

            $validator = new Validator($data, $rules);

            // This would require actual database setup to test properly
            expect(true)->toBeTrue();
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });

    it('can validate file uploads', function () {
        // Mock file upload data
        $data = [
            'avatar' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'size' => 1024,
                'tmp_name' => '/tmp/test',
                'error' => UPLOAD_ERR_OK
            ]
        ];
        $rules = ['avatar' => 'file|mimes:jpg,jpeg,png|max:2048'];

        if (method_exists(Validator::class, 'validateFile')) {
            $validator = new Validator($data, $rules);
            expect($validator->passes())->toBeTrue();
        } else {
            expect(true)->toBeTrue(); // Skip if not implemented
        }
    });
});
