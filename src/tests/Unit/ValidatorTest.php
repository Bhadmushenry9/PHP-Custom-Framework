<?php

use App\Services\Validator;

class ValidatorTest
{
    public function assert($condition, $message = '')
    {
        if (!$condition) {
            echo "❌ Failed: $message\n";
        } else {
            echo "✅ Passed: $message\n";
        }
    }

    public function testRequiredField()
    {
        $v = new Validator(['name' => ''], ['name' => 'required']);
        $this->assert($v->fails(), 'Required field fails if empty');
    }

    public function testEmailValidation()
    {
        $v = new Validator(['email' => 'invalid-email'], ['email' => 'email']);
        $this->assert($v->fails(), 'Invalid email is rejected');

        $v = new Validator(['email' => 'valid@example.com'], ['email' => 'email']);
        $this->assert(!$v->fails(), 'Valid email passes');
    }

    public function testMinMaxValidation()
    {
        $v = new Validator(['username' => 'abc'], ['username' => 'min:5']);
        $this->assert($v->fails(), 'min:5 fails for 3-char string');

        $v = new Validator(['username' => 'abcdef'], ['username' => 'max:5']);
        $this->assert($v->fails(), 'max:5 fails for 6-char string');
    }

    public function testConfirmedRule()
    {
        $v = new Validator(['password' => 'abc123', 'password_confirmation' => 'abc123'], ['password' => 'confirmed']);
        $this->assert(!$v->fails(), 'Password confirmed passes');

        $v = new Validator(['password' => 'abc123', 'password_confirmation' => 'xyz'], ['password' => 'confirmed']);
        $this->assert($v->fails(), 'Password confirmed fails if mismatch');
    }

    public function testSameRule()
    {
        $v = new Validator(['pin' => '1234', 'confirm_pin' => '1234'], ['confirm_pin' => 'same:pin']);
        $this->assert(!$v->fails(), 'same: passes when values match');

        $v = new Validator(['pin' => '1234', 'confirm_pin' => '4321'], ['confirm_pin' => 'same:pin']);
        $this->assert($v->fails(), 'same: fails when values differ');
    }

    public function testRegexRule()
    {
        $v = new Validator(['slug' => 'abc-123'], ['slug' => 'regex:/^[a-z\-0-9]+$/']);
        $this->assert(!$v->fails(), 'Regex passes valid slug');

        $v = new Validator(['slug' => 'abc$123'], ['slug' => 'regex:/^[a-z\-0-9]+$/']);
        $this->assert($v->fails(), 'Regex fails invalid slug');
    }

    public function testExistsRule()
    {
        $v = new Validator(['user_id' => 1], ['user_id' => 'exists:users,id']);
        $this->assert(!$v->fails(), 'exists passes for valid id');

        $v = new Validator(['user_id' => 999], ['user_id' => 'exists:users,id']);
        $this->assert($v->fails(), 'exists fails for non-existent id');
    }

    public function testCustomMessages()
    {
        $v = new Validator(['email' => ''], ['email' => 'required'], ['email.required' => 'Email is required']);
        $errors = $v->errors();
        $this->assert(isset($errors['email']) && $errors['email'][0] === 'Email is required', 'Custom error message applied');
    }

    public function runAllTests()
    {
        echo "Running Validator Tests...\n\n";
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (str_starts_with($method, 'test')) {
                $this->$method();
            }
        }
        echo "\nDone.\n";
    }
}

// Run tests
$tests = new ValidatorTest();
$tests->runAllTests();
