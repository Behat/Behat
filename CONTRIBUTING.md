Contributing
------------

Behat is an open source, community-driven project. If you'd like to contribute,
feel free to do this, but remember to follow this few simple rules:

1. Clone the `master` of this repository
2. Make your feature addition, bug fix or refactoring
3. Add `*.features` (tests) for those changes (please look into `features/` folder for
  some examples). This is important so we don't break it in a future version
  unintentionally
4. Make sure your changes adhere to [Semantic Versioning](http://semver.org/spec/v2.0.0.html)
  (e.g. no changes in public interfaces between minor or patch releases)
5. Commit your code, but do not mess with the `BehatApplication` version
6. Explain the kind of change you made under the [`[Unreleased]`](https://github.com/Behat/Behat/blob/master/CHANGELOG.md#unreleased) section of the `CHANGELOG.md`. You'd make our life even easier if you stick to [Keep a Changelog](http://keepachangelog.com/en/0.3.0/) format

Backwards compatibility
-----------------------

Starting from `v3.0.0`, Behat is following [Semantic Versioning v2.0.0](http://semver.org/spec/v2.0.0.html).
This means that we take backwards compatibility of public API very seriously. So unless you want your PR to start a
new major version of Behat (`v4.0.0` for example), you need to make sure that either you do not change existing
interfaces and their usage across the system or that you at least introduce backwards compatibility layer together with
your change. Not following these rules will cause a rejection of your PR. Exception could be an extremely rare case
where BC break is introduced as a measure to fix a serious issue.

You can read detailed guidance on what BC means in [Symfony2 BC guide](http://symfony.com/doc/current/contributing/code/bc.html).

Contributing to Formatter Translations
--------------------------------------

Almost any output message (except exceptions and custom output) printed by Behat
formatters could be translated into your language with `--lang` option. In order
to fix/add translation, edit the appropriate section of the `i18n.php` file.

Running tests
-------------

Make sure that you don't break anything with your changes by running the test
suite with your locale set to english:

```bash
$> LANG=C bin/behat --format=progress
```

