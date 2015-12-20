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
            expect($env->data())->toBe(['PHP_SAPI' => 'cli']);

        });

        it("don't normalize variables", function() {

            $env = new Env([], false);
            expect($env->data())->toBe([]);

        });

    });

    describe("->offsetSet()/->offsetGet()", function() {

        it("sets a variable", function() {

            $this->env['CUSTOM_VARIABLE'] = 'myvalue';
            expect($this->env['CUSTOM_VARIABLE'])->toBe('myvalue');
            expect($this->env->data())->toBe([
                'CUSTOM_VARIABLE' => 'myvalue'
            ]);

        });

        it("sets an array of variables", function() {

            expect($this->env->set([
                'CUSTOM_VARIABLE1' =>'myvalue1',
                'CUSTOM_VARIABLE2' =>'myvalue2'
            ]))->toBe($this->env);

            expect($this->env->data())->toBe([
                'CUSTOM_VARIABLE1' =>'myvalue1',
                'CUSTOM_VARIABLE2' =>'myvalue2'
            ]);

        });

        it("returns `false` for unexisting value", function() {

            expect($this->env['CUSTOM_VARIABLE'])->toBe(false);

        });

    });

    describe("->offsetExists()", function() {

        it("checks if a variable exists", function() {

            expect(isset($this->env['CUSTOM_VARIABLE']))->toBe(false);
            $this->env['CUSTOM_VARIABLE'] = 'myvalue';
            expect(isset($this->env['CUSTOM_VARIABLE']))->toBe(true);

        });

    });

    describe("->offsetUnset()", function() {

        it("removes an environment variable", function() {

            $this->env['CUSTOM_VARIABLE'] = 'myvalue';
            expect(isset($this->env['CUSTOM_VARIABLE']))->toBe(true);

            unset($this->env['CUSTOM_VARIABLE']);
            expect(isset($this->env['CUSTOM_VARIABLE']))->toBe(false);

        });

    });

    describe("->clear()", function() {

        it("clears all environment variables", function() {

            $this->env->set([
                'CUSTOM_VARIABLE1' => 'myvalue1',
                'CUSTOM_VARIABLE2' => 'myvalue2'
            ]);

            expect($this->env->data())->toBe([
                'CUSTOM_VARIABLE1' => 'myvalue1',
                'CUSTOM_VARIABLE2' => 'myvalue2'
            ]);

            $this->env->clear();
            expect($this->env->data())->toBe([]);

        });

    });

    describe("::normalize()", function() {

        it("normalizes REMOTE_ADDR", function() {

            $env = new Env(['REMOTE_ADDR' => '123.456.789.000']);
            expect($env['REMOTE_ADDR'])->toBe('123.456.789.000');


            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '111.222.333.444'
            ]);
            expect($env['REMOTE_ADDR'])->toBe('111.222.333.444');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '333.222.444.111, 444.333.222.111, 255.255.255.255'
            ]);
            expect($env['REMOTE_ADDR'])->toBe('333.222.444.111');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_PC_REMOTE_ADDR' => '222.333.444.555'
            ]);
            expect($env['REMOTE_ADDR'])->toBe('222.333.444.555');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_REAL_IP' => '111.222.333.444'
            ]);
            expect($env['REMOTE_ADDR'])->toBe('111.222.333.444');

            $env = new Env([
                'REMOTE_ADDR' => '123.456.789.000',
                'HTTP_X_FORWARDED_FOR' => '111.222.333.444',
                'HTTP_PC_REMOTE_ADDR' => '222.333.444.555'
            ]);
            expect($env['REMOTE_ADDR'])->toBe('111.222.333.444');

        });

        it("normalizes HTTP_AUTHORIZATION", function() {

            $env = new Env([
                'REDIRECT_HTTP_AUTHORIZATION' => 'Basic dGVzdC11c2VyOnRlc3QtcGFzc3dvcmQ=',
            ]);
            expect($env['HTTP_AUTHORIZATION'])->toBe('Basic dGVzdC11c2VyOnRlc3QtcGFzc3dvcmQ=');

        });

        it("normalizes HTTPS", function() {

            $env = new Env([
                'SCRIPT_URI' => 'https://example.com',
                'HTTPS' => null
            ]);
            expect($env['HTTPS'])->toBe(true);

            $env = new Env([
                'HTTPS' => 'on'
            ]);
            expect($env['HTTPS'])->toBe(true);

            $env = new Env([
                'HTTPS' => 'off'
            ]);
            expect($env['HTTPS'])->toBe(false);

        });

        it("normalizes REQUEST_METHOD", function() {

            $env = new Env([
                'REQUEST_METHOD'              => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'
            ]);
            expect($env['REQUEST_METHOD'])->toBe('PATCH');
        });

        it("normalizes SCRIPT_FILENAME", function() {

            $env = new Env([
                'REQUEST_METHOD'              => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'
            ]);
            expect($env['REQUEST_METHOD'])->toBe('PATCH');
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

            expect($env->data())->toBe([
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
