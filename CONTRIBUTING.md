# Contributing

First ol all **thank you** for contributing!

Make your contributions through Pull Requests

Find here a few rules to follow in order to keep the code clean and easy to reviews and merge:

- Follow **[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** coding standard
- **Unit test everything** and run the test suite
- Try not to bring **code coverage** down
- Keep documentation **updated**
- Just **one pull request per feature** at a time
- Check that **[Travis CI](https://travis-ci.org/juliangut/janitor)** build passed

[Grunt](http://gruntjs.com/) tasks are provided to help you keep code quality and run the test suite

- `grunt qa` will run PHP linting, [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) for coding style guidelines, [PHPMD](https://github.com/phpmd/phpmd) for code smells and [PHPCPD](https://github.com/sebastianbergmann/phpcpd) for copy/paste detection
- `grunt test` will run [PHPUnit](https://github.com/sebastianbergmann/phpunit) for unit tests
- `grunt security` will run [Composer](https://getcomposer.org) (>=1.1.0) for outdated dependencies and [Security checker](https://github.com/sensiolabs/security-checker) for dependencies with known issues
- `grunt` will run `qa` and `test` tasks at once
