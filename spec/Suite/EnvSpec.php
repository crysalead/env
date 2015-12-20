<?php
namespace Lead\Env\Spec\Suite;

use Lead\Env\Env;

describe("Env", function() {

    beforeEach(function() {
        $this->env = new Env([], false);
    });

    describe("->__construct()", function() {

        it("sets default environment variables", function() {

            $env = new Env();
            expect($env->get())->toBe(['PHP_SAPI' => 'cli']);

        });

        it("don't normalize variables", function() {

            $env = new Env([], false);
            expect($env->get())->toBe([]);

        });

    });

    describe("->set()/->get()", function() {

        it("sets a variable", function() {

            expect($this->env->set('CUSTOM_VARIABLE', 'myvalue'))->toBe($this->env);
            expect($this->env->get('CUSTOM_VARIABLE'))->toBe('myvalue');
            expect($this->env->get())->toBe([
                'CUSTOM_VARIABLE' => 'myvalue'
            ]);

        });

        it("sets an array of variables", function() {

            expect($this->env->set([
                'CUSTOM_VARIABLE1' =>'myvalue1',
                'CUSTOM_VARIABLE2' =>'myvalue2'
            ]))->toBe($this->env);

            expect($this->env->get())->toBe([
                'CUSTOM_VARIABLE1' =>'myvalue1',
                'CUSTOM_VARIABLE2' =>'myvalue2'
            ]);

        });

    });

    describe("->has()", function() {

        it("checks if a variable exists", function() {

            expect($this->env->has('CUSTOM_VARIABLE'))->toBe(false);
            expect($this->env->set('CUSTOM_VARIABLE', 'myvalue'))->toBe($this->env);
            expect($this->env->has('CUSTOM_VARIABLE'))->toBe(true);

        });

    });

    describe("->clear()", function() {

        it("clears all environment variables", function() {

            expect($this->env->get())->toBe([]);
            expect($this->env->set('CUSTOM_VARIABLE', 'myvalue'))->toBe($this->env);
            expect($this->env->get())->toBe([
                'CUSTOM_VARIABLE' => 'myvalue'
            ]);

            $this->env->clear();
            expect($this->env->get())->toBe([]);

        });

    });

    describe("->remove()", function() {

        it("removes an environment variable", function() {

            expect($this->env->set('CUSTOM_VARIABLE', 'myvalue'))->toBe($this->env);
            expect($this->env->has('CUSTOM_VARIABLE'))->toBe(true);

            expect($this->env->remove('CUSTOM_VARIABLE'))->toBe($this->env);
            expect($this->env->has('CUSTOM_VARIABLE'))->toBe(false);

        });

    });

    describe("::normalize()", function() {

        it("normalizes REMOTE_ADDR", function() {

            $env = new Env(['REMOTE_ADDR' => '123.456.789.000']);
            expect($env->get('REMOTE_ADDR'))->toBe('123.456.789.000');


            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '111.222.333.444'
            ]);
            expect($env->get('REMOTE_ADDR'))->toBe('111.222.333.444');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '333.222.444.111, 444.333.222.111, 255.255.255.255'
            ]);
            expect($env->get('REMOTE_ADDR'))->toBe('333.222.444.111');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_PC_REMOTE_ADDR' => '222.333.444.555'
            ]);
            expect($env->get('REMOTE_ADDR'))->toBe('222.333.444.555');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_REAL_IP' => '111.222.333.444'
            ]);
            expect($env->get('REMOTE_ADDR'))->toBe('111.222.333.444');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '111.222.333.444',
                'HTTP_PC_REMOTE_ADDR' => '222.333.444.555'
            ]);
            expect($env->get('REMOTE_ADDR'))->toBe('111.222.333.444');

        });

        it("normalizes HTTP_AUTHORIZATION", function() {

            $env = new Env([
                'REDIRECT_HTTP_AUTHORIZATION' => 'Basic dGVzdC11c2VyOnRlc3QtcGFzc3dvcmQ=',
            ]);
            expect($env->get('HTTP_AUTHORIZATION'))->toBe('Basic dGVzdC11c2VyOnRlc3QtcGFzc3dvcmQ=');

        });

        it("normalizes HTTPS", function() {

            $env = new Env([
                'SCRIPT_URI' => 'https://example.com',
                'HTTPS' => null
            ]);
            expect($env->get('HTTPS'))->toBe(true);

            $env = new Env([
                'HTTPS' => 'on'
            ]);
            expect($env->get('HTTPS'))->toBe(true);

            $env = new Env([
                'HTTPS' => 'off'
            ]);
            expect($env->get('HTTPS'))->toBe(false);

        });

        it("normalizes REQUEST_METHOD", function() {

            $env = new Env([
                'REQUEST_METHOD'              => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'
            ]);
            expect($env->get('REQUEST_METHOD'))->toBe('PATCH');
        });

        it("normalizes SCRIPT_FILENAME", function() {

            $env = new Env([
                'REQUEST_METHOD'              => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'
            ]);
            expect($env->get('REQUEST_METHOD'))->toBe('PATCH');
        });

        it("normalizes IIS environment variables", function() {

            $env = new Env([
                'PHP_SAPI'            => 'isapi',
                'SCRIPT_NAME'         => '\index.php',
                'SCRIPT_FILENAME'     => false,
                'DOCUMENT_ROOT'       => false,
                'PATH_TRANSLATED'     => '\app\public\index.php',
                'HTTP_PC_REMOTE_ADDR' => '123.456.789.000',
                'LOCAL_ADDR'          => '789.456.123.000'
            ]);

            expect($env->get())->toBe([
                'PHP_SAPI'            => 'isapi',
                'SCRIPT_NAME'         => '\index.php',
                'SCRIPT_FILENAME'     => '\app\public\index.php',
                'DOCUMENT_ROOT'       => '\app\public',
                'PATH_TRANSLATED'     => '\app\public\index.php',
                'HTTP_PC_REMOTE_ADDR' => '123.456.789.000',
                'LOCAL_ADDR'          => '789.456.123.000',
                'REMOTE_ADDR'         => '123.456.789.000',
                'SERVER_ADDR'         => '789.456.123.000'
            ]);

        });

    });

});
