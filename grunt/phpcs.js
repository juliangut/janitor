'use strict';

module.exports.tasks = {
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
  }
};
