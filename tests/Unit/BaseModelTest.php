<?php

use GuepardoSys\Core\BaseModel;
use GuepardoSys\Core\Database;

// Mock model for testing
class TestModel extends BaseModel
{
    protected string $table = 'test_models';
    protected array $fillable = ['name', 'email', 'age'];
}

describe('BaseModel', function () {
    beforeEach(function () {
        // Set up SQLite in-memory database
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $reflection = new ReflectionClass(Database::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, null);

        // Create test table
        $pdo = Database::getConnection();
        $pdo->exec('
            CREATE TABLE test_models (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT,
                age INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
    });

    it('can create a new model instance', function () {
        $model = new TestModel();

        expect($model)->toBeInstanceOf(BaseModel::class);
        expect($model)->toBeInstanceOf(TestModel::class);
    });

    it('can get table name from class name', function () {
        $model = new TestModel();

        expect($model->getTable())->toBe('test_models');
    });

    it('can save a new model', function () {
        $model = new TestModel();
        $model->name = 'John Doe';
        $model->email = 'john@example.com';
        $model->age = 30;

        $result = $model->save();

        expect($result)->toBeTrue();
        expect($model->id)->toBeGreaterThan(0);
    });

    it('can find model by id', function () {
        // Create test data
        $model = new TestModel();
        $model->name = 'Jane Doe';
        $model->email = 'jane@example.com';
        $model->save();

        $found = TestModel::find($model->id);

        expect($found)->toBeInstanceOf(TestModel::class);
        expect($found->name)->toBe('Jane Doe');
        expect($found->email)->toBe('jane@example.com');
    });

    it('returns null when model not found', function () {
        $found = TestModel::find(999);

        expect($found)->toBeNull();
    });

    it('can get all models', function () {
        // Create test data
        $model1 = new TestModel();
        $model1->name = 'User 1';
        $model1->save();

        $model2 = new TestModel();
        $model2->name = 'User 2';
        $model2->save();

        $all = TestModel::all();

        expect($all)->toBeArray();
        expect(count($all))->toBe(2);
        expect($all[0])->toBeInstanceOf(TestModel::class);
        expect($all[1])->toBeInstanceOf(TestModel::class);
    });

    it('can update existing model', function () {
        $model = new TestModel();
        $model->name = 'Original Name';
        $model->save();

        $model->name = 'Updated Name';
        $result = $model->save();

        expect($result)->toBeTrue();

        $found = TestModel::find($model->id);
        expect($found->name)->toBe('Updated Name');
    });

    it('can delete model', function () {
        $model = new TestModel();
        $model->name = 'To Delete';
        $model->save();

        $id = $model->id;
        $result = $model->delete();

        expect($result)->toBeTrue();

        $found = TestModel::find($id);
        expect($found)->toBeNull();
    });

    it('can create model with mass assignment', function () {
        $data = [
            'name' => 'Mass Assigned',
            'email' => 'mass@example.com',
            'age' => 25
        ];

        $model = TestModel::create($data);

        expect($model)->toBeInstanceOf(TestModel::class);
        expect($model->name)->toBe('Mass Assigned');
        expect($model->email)->toBe('mass@example.com');
        expect($model->age)->toBe(25);
        expect($model->id)->toBeGreaterThan(0);
    });

    it('respects fillable attributes', function () {
        $model = new TestModel();
        $model->fill([
            'name' => 'Fillable Test',
            'email' => 'fillable@example.com',
            'id' => 999 // Should be ignored as not fillable
        ]);

        expect($model->name)->toBe('Fillable Test');
        expect($model->email)->toBe('fillable@example.com');
        expect($model->id)->toBeNull(); // Should remain null
    });

    it('can find by specific column', function () {
        $model = new TestModel();
        $model->name = 'Unique Name';
        $model->email = 'unique@example.com';
        $model->save();

        $found = TestModel::where('email', 'unique@example.com')->first();

        expect($found)->toBeInstanceOf(TestModel::class);
        expect($found->name)->toBe('Unique Name');
    });

    it('can count records', function () {
        TestModel::create(['name' => 'Count 1']);
        TestModel::create(['name' => 'Count 2']);
        TestModel::create(['name' => 'Count 3']);

        $count = TestModel::count();

        expect($count)->toBe(3);
    });

    it('can get first record', function () {
        TestModel::create(['name' => 'First']);
        TestModel::create(['name' => 'Second']);

        $first = TestModel::first();

        expect($first)->toBeInstanceOf(TestModel::class);
        expect($first->name)->toBe('First');
    });

    it('can order results', function () {
        TestModel::create(['name' => 'Beta', 'age' => 30]);
        TestModel::create(['name' => 'Alpha', 'age' => 25]);

        $ordered = TestModel::orderBy('name')->get();

        expect($ordered[0]->name)->toBe('Alpha');
        expect($ordered[1]->name)->toBe('Beta');
    });

    it('can limit results', function () {
        TestModel::create(['name' => 'Item 1']);
        TestModel::create(['name' => 'Item 2']);
        TestModel::create(['name' => 'Item 3']);

        $limited = TestModel::limit(2)->get();

        expect(count($limited))->toBe(2);
    });

    it('can handle timestamps', function () {
        $model = new TestModel();
        $model->name = 'Timestamp Test';
        $model->save();

        expect($model->created_at)->not->toBeNull();
        expect($model->updated_at)->not->toBeNull();
    });

    it('can convert to array', function () {
        $model = new TestModel();
        $model->name = 'Array Test';
        $model->email = 'array@example.com';
        $model->age = 35;
        $model->save();

        $array = $model->toArray();

        expect($array)->toBeArray();
        expect($array['name'])->toBe('Array Test');
        expect($array['email'])->toBe('array@example.com');
        expect($array['age'])->toBe(35);
    });

    it('can convert to JSON', function () {
        $model = new TestModel();
        $model->name = 'JSON Test';
        $model->save();

        $json = $model->toJson();
        $decoded = json_decode($json, true);

        expect($decoded)->toBeArray();
        expect($decoded['name'])->toBe('JSON Test');
    });
});
