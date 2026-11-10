<?php

test('registration page is unavailable', function () {
    $this->get('/register')
        ->assertNotFound();
});

test('new accounts cannot be created through the registration endpoint', function () {
    $this->post('/register', [
        'full_name' => 'John Doe',
        'phone' => '+15550000000',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
