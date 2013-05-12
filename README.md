Behat
=====

- [stable (master)](https://github.com/Behat/Behat) ([![Master Build Status](https://secure.travis-ci.org/Behat/Behat.png?branch=master)](http://travis-ci.org/Behat/Behat)) - latest stable version.
- [development (develop)](https://github.com/Behat/Behat/tree/develop) ([![Develop Build Status](https://secure.travis-ci.org/Behat/Behat.png?branch=develop)](http://travis-ci.org/Behat/Behat)) - development happens here and you should send your PRs here too.

Useful Links
------------

- The main website with documentation is at [http://behat.org](http://behat.org)
- Official Google Group is at [http://groups.google.com/group/behat](http://groups.google.com/group/behat)
- IRC channel on [#freenode](http://freenode.net/) is `#behat`
- [Note on Patches/Pull Requests](CONTRIBUTING.md)

Installing Dependencies
-----------------------

```bash
$> curl -s https://getcomposer.org/installer | php
$> php composer.phar install
```

Running
-------

```bash
$> bin/behat -h
```

Running inside Vagrant
----------------------

1. Install [Vagrant](http://www.vagrantup.com).
2. Install [Berkshelf plugin](http://berkshelf.com/#vagrant_with_berkshelf) for Vagrant
3. Run `vagrant up`

Contributors
------------

- Konstantin Kudryashov [everzet](http://github.com/everzet) [lead developer]
- Other [awesome developers](https://github.com/Behat/Behat/graphs/contributors)
