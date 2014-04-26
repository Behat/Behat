Contributing
------------

Behat is an open source, community-driven project. If you'd like to contribute,
feel free to do this, but remember to follow this few simple rules:

- Make your feature addition or bug fix,
- __Always__ as base for your changes use `3.0` branch (all new 3.0 development
  happens here, `master` branch is for releases & hotfixes only),
- Add `*.features` for those changes (please look into `features/` folder for
  some examples). This is important so we don't break it in a future version
  unintentionally,
- Commit your code, but do not mess with `BehatApplication` version, or
  `CHANGES.md` one,
- __Remember__: when you create Pull Request, always select `3.0` branch as
  target, otherwise it will be closed.

Backwards compatibility
-----------------------

Starting from `v3.0.1`, Behat is following [Semantic Versioning v2.0.0](http://semver.org/spec/v2.0.0.html).
This means that we take backwards compatibility of public API very seriously. Public API of Behat
consists of all Behat and Testwork interfaces (like [this one](https://github.com/Behat/Behat/blob/3.0/src/Behat/Behat/Context/ContextClass/ClassResolver.php#L15-L22))
and service constants (like [this one](https://github.com/Behat/Behat/blob/3.0/src/Behat/Behat/Context/ServiceContainer/ContextExtension.php#L45)).
So unless you want your PR to start a new major version of Behat (`v4.0.0` for example), you need
to make sure that either you do not change existing interfaces and their usage across the system or
that you at least introduce backwards compatiblity layer together with your change. Not following
these rules will cause a rejection of your PR. Exception could be an extremely rare case where BC
break is introduced as a measure to fix a serious issue.


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
$> LANG=C bin/behat
```

