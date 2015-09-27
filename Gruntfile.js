module.exports = function(grunt) {
    require('time-grunt')(grunt);
    require('load-grunt-tasks')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('./package.json'),
        composer: grunt.file.readJSON('./composer.json'),

        phplint: {
            options: {
                swapPath: '/tmp'
            },
            application: [
                './src/**/*.php',
                './test/**/*.php'
            ]
        },
        phpcs: {
            options: {
                bin: './vendor/bin/phpcs',
                standard: './phpcs.xml'
            },
            application: {
                dir: [
                    './src',
                    './test'
                ]
            }
        },
        phpmd: {
            options: {
                bin: './vendor/bin/phpmd',
                rulesets: './phpmd.xml',
                reportFormat: 'text'
            },
            application: {
                dir: './src'
            }
        },
        phpcpd: {
            options: {
                bin: './vendor/bin/phpcpd',
                quiet: false,
                ignoreExitCode: true
            },
            application: {
                dir: './src'
            }
        },
        phpunit: {
            options: {
                bin: './vendor/bin/phpunit',
                coverage: true
            },
            application: {
                configuration: './phpunit.xml'
            }
        }
    });

    grunt.task.registerTask('build', 'Building', function() {
        grunt.log.writeln('Task ready to be implemented');
    });

    grunt.registerTask('check', ['phplint', 'phpcs', 'phpmd', 'phpcpd']);
    grunt.registerTask('test', ['phpunit']);

    grunt.registerTask('default', ['check', 'test']);
};
