# Contributing

Thank you for considering contributing to the [Aenthill](https://aenthill.github.io/) ecosystem. 

## Contributing code

### From code source

Fork and clone the project on your machine. Once done, go to the cloned directory and run:

```bash
$ docker run -it --rm -e PHP_EXTENSION_INTL=1 -v "$PWD":/usr/src/app thecodingmachine/php:7.2-v1-cli bash
```

You may now install composer dependencies with:

```bash
$ composer install
```

### Working with git

1. Create your feature branch (`git checkout -b my-new-feature`)
2. Test your code (`composer run cs-fix && composer run phpstan`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create a new pull request

## Reporting bugs and feature request

Your issue or feature request may already be reported!
Please search on the [issue tracker](../../../issues) before creating one.

If you do not find any relevant issue or feature request, feel free to
add a new one!

## Additional resources

* [Code of conduct](CODE_OF_CONDUCT.md)
* [Issue template](ISSUE_TEMPLATE.md)
* [Pull request template](PULL_REQUEST_TEMPLATE.md)