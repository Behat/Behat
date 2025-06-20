![Behat](https://github.com/Behat/logo/raw/master/logo.png)

Behat is a BDD framework for PHP to help you test business expectations.

[![Gitter chat](https://badges.gitter.im/Behat/Behat.svg)](https://gitter.im/Behat/Behat)
[![License](https://poser.pugx.org/behat/behat/license.svg)](https://packagist.org/packages/behat/behat)
[![Build Status](https://github.com/Behat/Behat/workflows/Build/badge.svg)](https://github.com/Behat/Behat/actions?query=workflow%3ABuild)

Installing Behat
----------------

The easiest way to install Behat is by using [Composer](https://getcomposer.org):

```bash
composer require --dev behat/behat
```

After that you'll be able to run Behat via:

```bash
vendor/bin/behat
```

Installing Development Version
------------------------------

Clone the repository and install dependencies via [Composer](https://getcomposer.org):

```bash
composer install
```

After that you will be able to run development version of Behat via:

```bash
bin/behat
```

Contributing
------------

Before contributing to Behat, please take a look at the [CONTRIBUTING.md](CONTRIBUTING.md) document.

Versioning
----------

Starting from `v3.0.0`, Behat is following [Semantic Versioning v2.0.0](https://semver.org/spec/v2.0.0.html).
This basically means that if all you do is implement interfaces (like [this one](https://github.com/Behat/Behat/blob/v3.1.0/src/Behat/Behat/Context/ContextClass/ClassResolver.php#L15-L22))
and use service constants (like [this one](https://github.com/Behat/Behat/blob/v3.1.0/src/Behat/Behat/Context/ServiceContainer/ContextExtension.php#L46)),
you would not have any backwards compatibility issues with Behat up until `v4.0.0` (or later major)
is released. Exception could be an extremely rare case where BC break is introduced as a measure
to fix a serious issue.

You can read detailed guidance on what BC means in [Symfony BC guide](https://symfony.com/doc/current/contributing/code/bc.html).

Useful Links
------------

- The main website is at [https://behat.org](https://behat.org)
- The documentation is at [https://docs.behat.org/en/latest/](https://docs.behat.org/en/latest/)
- [Note on Patches/Pull Requests](CONTRIBUTING.md)

Contributors
------------

- Konstantin Kudryashov [everzet](https://github.com/everzet) [lead developer]
- Other [awesome developers](https://github.com/Behat/Behat/graphs/contributors)

Technology Sponsors
-------------------
Technology sponsors allow us to use their products and services for free as part of a contribution to the open source ecosystem and our work.

<a href="https://jb.gg/OpenSource"><img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg" width="200" alt="JetBrains"></a>

<a href="https://github.com"><img src="GitHub_Lockup_Dark.svg" width="150"></a>

