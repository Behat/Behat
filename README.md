Behat
=====

The main website with documentation is at [http://everzet.com/Behat](http://everzet.com/Behat)

Note on Patches/Pull Requests
-----------------------------
 
* Fork the project.
* Make your feature addition or bug fix.
* Add tests for it (in Behat). This is important so I don't break it in a
  future version unintentionally.
* Commit, do not mess with `BehatApplication` version, or `History.md`.
  (if you want to have your own version, that is fine but
   bump version in a commit by itself I can ignore when I pull)
* Send me a pull request.

Running tests
-------------

	behat

If you get errors about missing dependencies - just run

	git submodule update --init

Copyright
---------

Copyright (c) 2010 Konstantin Kudryashov (ever.zet). See LICENSE for details.
