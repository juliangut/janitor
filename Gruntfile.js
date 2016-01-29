module.exports = function(grunt) {
  require('time-grunt')(grunt);
  require('load-grunt-tasks')(grunt);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    phplint: {
      options: {
        swapPath: '/tmp'
      },
      application: [
        'src/**/*.php',
        'tests/**/*.php'
      ]
    },
    phpcs: {
      options: {
        bin: 'vendor/bin/phpcs',
        standard: 'PSR2'
      },
      application: {
        dir: [
          'src',
          'tests'
        ]
      }
    },
    phpmd: {
      options: {
        bin: 'vendor/bin/phpmd',
        rulesets: 'phpmd.xml',
        reportFormat: 'text'
      },
      application: {
        dir: 'src'
      }
    },
    phpcpd: {
      options: {
        bin: 'vendor/bin/phpcpd',
        quiet: false,
        ignoreExitCode: true
      },
      application: {
        dir: 'src'
      }
    },
    climb: {
      options: {
        bin: 'vendor/bin/climb',
        exclude: [
          'phpunit/phpunit'
        ]
      },
      application: {
        directory: './'
      }
    },
    security_checker: {
      options: {
        bin: 'vendor/bin/security-checker',
        format: 'text'
      },
      application: {
        file: 'composer.json'
      },
    },
    phpunit: {
      options: {
        bin: 'vendor/bin/phpunit',
        coverage: true
      },
      application: {
        coverageHtml: 'build/coverage'
      }
    }
  });

  grunt.registerTask('check', ['phplint', 'phpcs', 'phpmd', 'phpcpd']);
  grunt.registerTask('security', ['climb', 'security_checker']);
  grunt.registerTask('test', ['phpunit']);
  grunt.registerTask('default', ['check', 'security', 'test']);
};
