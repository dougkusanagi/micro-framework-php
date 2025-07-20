<?php
use GuepardoSys\Core\BaseFactory;

class UserFactory extends BaseFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->fakeName(),
            'email' => $this->fakeEmail(),
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function modelClass()
    {
        return User::class;
    }

    protected function fakeName()
    {
        $names = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank'];
        return $names[array_rand($names)];
    }

    protected function fakeEmail()
    {
        $domains = ['example.com', 'test.com', 'mail.com'];
        $name = strtolower($this->fakeName());
        return $name . rand(1, 1000) . '@' . $domains[array_rand($domains)];
    }
} 