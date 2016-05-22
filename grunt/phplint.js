'use strict';

module.exports.tasks = {
  phplint: {
    options: {
      swapPath: '/tmp'
    },
    application: [
      'src/**/*.php',
      'tests/**/*.php'
    ]
  }
};
