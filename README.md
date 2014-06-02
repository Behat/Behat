![Behat](https://dl.dropboxusercontent.com/u/282797/behat/behat.png)

Behat is a BDD framework for PHP to help you test business expectations.

[![License](https://poser.pugx.org/behat/behat/license.png)](https://packagist.org/packages/behat/behat)
[![Build Status](https://travis-ci.org/Behat/Behat.png?branch=master)](https://travis-ci.org/Behat/Behat)
[![HHVM Status](http://hhvm.h4cc.de/badge/behat/behat.png?branch=master)](http://hhvm.h4cc.de/package/behat/behat)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Behat/Behat/badges/quality-score.png?s=ad84e95fc2405712f88a96d89b4f31dfe5c80fae)](https://scrutinizer-ci.com/g/Behat/Behat/)
[![Latest Stable Version](https://poser.pugx.org/behat/behat/v/stable.png)](https://packagist.org/packages/behat/behat)
[![Total Downloads](https://poser.pugx.org/behat/behat/downloads.png)](https://packagist.org/packages/behat/behat)

Installing Behat
----------------

The easiest way to install Behat is by using [Composer](http://getcomposer.org):

```bash
$> curl -s https://getcomposer.org/installer | php
$> php composer.phar require behat/behat='~3.0.6'
```

After that you'll be able to run Behat via:

```bash
$> vendor/bin/behat
```

Installing Development Version
------------------------------

Clone the repository and install dependencies via [Composer](http://getcomposer.org):

```bash
$> curl -s https://getcomposer.org/installer | php
$> php composer.phar install
```

After that you will be able to run development version of Behat via:

```bash
$> bin/behat
```

Contributing
------------

Before contributing to Behat, please take a look at the [CONTRIBUTING.md](CONTRIBUTING.md) document.

Versioning
----------

Starting from `v3.0.0`, Behat is following [Semantic Versioning v2.0.0](http://semver.org/spec/v2.0.0.html).
This basically means that if all you do is implement interfaces (like [this one](https://github.com/Behat/Behat/blob/master/src/Behat/Behat/Context/ContextClass/ClassResolver.php#L15-L22))
and use service constants (like [this one](https://github.com/Behat/Behat/blob/master/src/Behat/Behat/Context/ServiceContainer/ContextExtension.php#L45)),
you would not have any backwards compatibility issues with Behat up until `v4.0.0` (or later major)
is released. Exception could be an extremely rare case where BC break is introduced as a measure
to fix a serious issue.

You can read detailed guidance on what BC means in [Symfony2 BC guide](http://symfony.com/doc/current/contributing/code/bc.html).

Useful Links
------------

- The main website is at [http://behat.org](http://behat.org)
- The documentation is at [http://behat.readthedocs.org](http://behat.readthedocs.org)
- Official Google Group is at [http://groups.google.com/group/behat](http://groups.google.com/group/behat)
- IRC channel on [#freenode](http://freenode.net/) is `#behat`
- [Note on Patches/Pull Requests](CONTRIBUTING.md)

Contributors
------------

- Konstantin Kudryashov [everzet](http://github.com/everzet) [lead developer]
- Other [awesome developers](https://github.com/Behat/Behat/graphs/contributors)
