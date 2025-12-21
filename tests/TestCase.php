<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
<<<<<<< HEAD

abstract class TestCase extends BaseTestCase
{
    //
=======
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
}
