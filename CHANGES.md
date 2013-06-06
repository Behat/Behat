2.4.6 / 2013-06-06
==================

  * New --stop-on-failure option
  * Support JSON in environment variables
  * Update Gherkin
  * Support Symfony 2.3
  * Out-of-the-box support for PHPUnit assertions pretty output

2.4.5 / 2013-01-27
==================

  * Added wrapping of lines in progress formatter
  * Added `--append-to` option to be able to add snippets to custom class
  * Both `ScenarioEvent` and `OutlineExampleEvent` now extend same `BaseScenarioEvent` class
  * Highly improved ability to create simple custom extensions
  * Always hide stack traces for `PendingException`
  * Ensured compatibility with all major symfony versions
  * Fixed configs import directive and loading precedence
  * Fixed path to vendor dir (solves problem of custom vendor dirs)

2.4.4 / 2012-09-12
==================

  * Fixed `RuntimeException` namespacing error
  * Added `FormatterManager::disableFormatter(s)` method
  * Updated Gherkin parser and fixed couple of helper bugs

2.4.3 / 2012-07-28
==================

  * Fixed broken `output_path` setting ([issue #169](https://github.com/Behat/Behat/issues/169))
  * Added shellbang to phar executable ([issue #167](https://github.com/Behat/Behat/issues/167))
  * Added feature title to progress exceptions ([issue #166](https://github.com/Behat/Behat/issues/166))
  * Tuned failed formatter to print only failed examples in outline ([issue #154](https://github.com/Behat/Behat/issues/154))
  * Small bugfixes

2.4.2 / 2012-06-26
==================

  * Fixed broken autoloading with Composer installation

2.4.1 / 2012-06-26
==================

  * Force custom context class usage if user changed it from `FeatureContext`
  * Clarified `Context class not found` exception
  * Use CWD for CLI options, basepath (config path) for everything else
  * Pass `behat.extension.classes` container param to extensions during their load
  * Tuned `event_subscriber` priorities
  * Use `require_once` instead of `require` in closured loaders
  * Fixed transformers bug with falsy transformations (that return **falsy** values)
  * Fixed custom formatters definition bug
  * Fixed formatter manager exception bug
  * Fixed czech translation
  * Fixed CS to be PSR2 compliant

2.4.0 / 2012-05-15
==================

  * New extension system based on Symfony2 DIC component
  * Refactored paths reading system (now relative paths are fully supported)
  * Support latest Composer changes
  * Removed static constraint for transformations
  * Updated to latest Gherkin with immutable AST
  * Fixed couple of definition snippet generator bugs
  * Option for HTML formatter to provide step definition links
  * Added fallback locale (in case if provided lang is unsupported yet)
  * Print step snippets in HTML formatter only if they're enabled
  * Escape placeholder brackets in HTML formatter
  * Use different names for examples in JUnit formatter
  * Major core cleanup

2.3.5 / 2012-03-30
==================

  * Fixed formatter language configuration and locale guesser

2.3.4 / 2012-03-28
==================

  * Added `StepEvent::getLogicalParent()`. Fixed issue #115

2.3.3 / 2012-03-09
==================

  * Implemented Gherkin caching support ([--cache](https://github.com/Behat/Behat/commit/753c4f6e392a873a640543306191d92e6dc91099))
  * Line ranges filtering support (`behat features/some.feature:12-19`. Thanks @headrevision)
  * `behat.yml.dist` configs support out of the box
  * Minor bug fixes
  * Updated Gherkin

2.3.2 / 2012-01-29
==================

  * Fixed bug in `ErrorException`, that caused wrong exceptions on warnings and notices

2.3.1 / 2012-01-26
==================

  * Updated error handler to avoid suppressed exceptions
  * Autoload bootstrap scripts in their name order
  * Updated Gherkin dependency to v2.0.1

2.3.0 / 2012-01-19
==================

  * Switch to the Behat\Gherkin 2.0 usage
  * Migration to the single-file translation
  * Support for callables inside steps chains
  * Support for `*.yml` and `*.php` as definition translations
  * Added opposite options to option switchers (`--[no-]colors`, `--[no-]multiline`, etc.)
  * Redesigned `--story-syntax`
  * Refactored Runner
  * Performance improvements
  * Bugfixes

2.2.7 / 2012-01-13
==================

  * Added ability to search translated definitions with `--definitions`
  * Fixed custom formatters use bug

2.2.6 / 2012-01-09
==================

  * Fixed pretty and html formatters printing of undefined steps in outlines

2.2.5 / 2012-01-07
==================

  * `BEHAT_PARAMS` env variable support (083092e)
  * HTML formatter print styles optimization (@davedevelopment)

2.2.4 / 2012-01-04
==================

  * Prevent method name duplication with definition snippets

2.2.3 / 2012-01-04
==================

  * Fixed couple of `--append-snippets` bugs

2.2.2 / 2011-12-21
==================

  * Fixed Composer deps

2.2.1 / 2011-12-21
==================

  * Fixed Composer package bin

2.2.0 / 2011-12-14
==================

  * Multiple formats and outputs support
  * New `snippets` formatter
  * New `failed` formatter
  * Updated output of `-d` option
  * Search abilities added to `-d` option
  * New `--dry-run` option
  * New `--append-snippets` option
  * Rerun functionality refactored to use `failed` formatter internally
  * Overall code refactoring and cleaning
  * Polish translation added (Joseph Bielawski)
  * Spanish translation updated (Andrés Botero)
  * Locale autodetect

2.1.3 / 2011-11-04
==================

  * Substep translations support
  * Correctly print undefined substeps in pretty printer
  * @Transform callback now gets all provided matches
  * Always set proper encoding (UTF8)

2.1.2 / 2011-10-12
==================

  * Fixed filtered feature hooks
  * Fixed JUnit formatter time output in some locales

2.1.1 / 2011-10-09
==================

  * Fixed multiline titles printing bug
  * Fixed outline parameter inside step argument printing bug

2.1.0 / 2011-09-12
==================

  * Totally revamped HTML formatter template
  * Added transliteration support to definition snippets (for most langs)
  * Written missed features and fixed some bugs
  * Stabilization fixes for 3 major OS: MacOS/Ubuntu/Windows

2.0.5 / 2011-08-07
==================

  * Cleaned ContextDispatcher extension points
  * Cleaned context-parameters passing behavior

2.0.4 / 2011-08-02
==================

  * Subcontexts aliasing and retrieving
  * Multiple steps chaining
  * `--snippets-paths` option to show steps alongside the snippets
  * getContextParameters() method in SuiteEvent and FeatureEvent
  * Updated to Symfony2 stable components
  * Spanish translation
  * Dutch translation

2.0.3 / 2011-07-20
==================

  * Fixed JUnit formatter CDATA output

2.0.2 / 2011-07-17
==================

  * Added extra checks to context instance mapper
  * Fixed i18n support in definitions printer
  * Refactored Gherkin tags inheritance

2.0.1 / 2011-07-12
==================

  * Exception prefix added to statuses. Now you should throw `PendingException` instead of just
    `Pending`

2.0.0 / 2011-07-12
==================

  * Brand new Context-oriented architecture
  * Refactored --definitions (--steps) to print more useful info
  * Rafactored --story-syntax (--usage) to print more useful info
  * Refactored Command to use separate processors
  * Added --no-paths option
  * Added --no-snippets option
  * Added --expand option to expand outlines
  * phar package
  * Faster autoloader
  * Steps chaining added
  * Added BEHAT_ERROR_REPORTING constant to change error_repoting level
  * Fixed some Gherkin bugs
  * Fixed lots of bugs in Behat itself

1.1.9 / 2011-06-17
==================

  * Updated to the latest Symfony components

1.1.8 / 2011-06-09
==================

  * Fixed empty match printing in Pretty and HTML formatters
  * Updated to latest Symfony components

1.1.7 / 2011-06-03
==================

  * Fixed steps colorization bug in outline
  * Additional checks in config import routine

1.1.6 / 2011-05-27
==================

  * Updated Symfony vendors
  * Refactored console formatters

1.1.5 / 2011-05-17
==================

  * Fixed CWD path finding
  * Fixed HTML formatter (thanks @glenjamin)

1.1.4 / 2011-05-03
==================

  * Fixed `--out` option usage critical bug
  * Added ability to specify `output_path` from config file

1.1.3 / 2011-04-28
==================

  * JUnit formatter fix
  * Formatters basePath fix. Now formatters uses CWD as path trimmer
  * Relative paths locator bug fix
  * Show table argument header in HTML formatter

1.1.2 / 2011-04-27
==================

  * Fixed custom features path locator bug(issue #020)

1.1.1 / 2011-04-21
==================

  * Fixed paths finding routines
  * Totally refactored BehatCommand
  * Added rerun functionality (`--rerun`)
  * Ability to remove previously specified paths in `behat.yml`
  * Bugfixes and little tweaks

1.1.0 / 2011-04-04
==================

  * New configuration system with profiles and imports support
  * New event system
  * Environment parameters support
  * Named regex arguments support
  * Japanese translation for formatters
  * JUnit formatter bugfixes
  * HTML and Pretty formatters multiple arguments print bugfix
  * Step snippets (proposals) bugfixes
  * Updated vendor libraries

1.0.0 / 2011-03-08
==================

  * Changed XSD
  * Updated vendors

1.0.0RC6 / 2011-03-03
=====================

  * Cleaned command options
  * Added --init option
  * Multiple paths support in behat.yml
  * Application options refactoring

1.0.0RC5 / 2011-02-25
=====================

  * Windows support
  * Bundled features hooks optimizations

1.0.0RC4 / 2011-02-23
=====================

  * Pretty formatter tag printing fix
  * Custom formatter specification fix in `behat.yml`
  * Symfony components updated
  * Extension configuration manager (Symfony\Component\Config component)
  * Cleaning of `behat.yml` configurator (thanks to Symfony\Component\Config)
  * Additional formatter parameters support in `behat.yml`

1.0.0RC3 / 2011-02-18
=====================

  * Event dispatcher binding optimizations
  * Command API optimizations for easier overloading
  * Formatter path trimming bugfix
  * BehatExtension config merging support

1.0.0RC2 / 2011-02-15
=====================

  * Step printing option bugfix

1.0.0RC1 / 2011-02-15
=====================

  * Gherkin DSL parser is standalone project
  * Own Behat namespace for both Behat & Gherkin
  * Fully rewritten formatters (much cleaner & beautifull API)
  * Big refactoring of whole Behat code (clean code DRYing)
  * Config file is now handled by standart-driven DIC extension (cleaner `behat.yml`)
  * API documentation retouched
  * New `--strict` option
  * New `--no-multiline` option
  * Feature examples in your language with `--usage`
  * Available definitions listing with `--steps`
  * Definition i18n
  * Command refactoring (much cleaner API & actions)
  * Event system refactoring
  * 42 new languages with new Gherkin DSL parser

0.3.6 / 2010-12-07
==================

  * [Behat,Gherkin] Fixed French support includes (fr)

0.3.6 / 2010-12-06
==================

  * [Behat] Updated Symfony2 Components to latest PR4
  * [Gherkin] Added French support (fr)
  * [Gherkin] Added German support (de)
  * [Behat] Small bugfixes

0.3.5 / 2010-11-19
==================

  * [Behat] Refactored EnvironmentBuilder to allow Environment service definition overload

0.3.4 / 2010-11-18
==================

  * [Behat] Introduced environment builder
  * [Gherkin,Behat] id locale support

0.3.3 / 2010-11-07
==================

  * [Gherkin] Added ability to create Table & PyString nodes with hands (in your step to step calls for example)
  * [Gherkin] Added getRowsHash() method to TableNode, so now you can "rotate" given tables
  * [Gherkin] You now can add comments before language specification in your feature files

0.3.2 / 2010-11-06
==================

  * [Gherkin] Added ability to specify extended langs (en-US)
  * [Behat,Gherkin] Added pt-BR translation

0.3.1 / 2010-11-02
==================

  * [Behat] JUnit formatter
  * [Behat] Pretty & HTML formatter background hooks fix
  * [Behat] Other small fixes

0.3.0 / 2010-11-02
==================

  * [Behat] Refactored tags filter
  * [Behat] Added name filter
  * [Behat] Refactored hooks
  * [Behat] Added tagged/named hooks
  * [Behat] Customizable HTML formatter with w3c valid default markup
  * [Behat] Ability to specify out path for formatters
  * [Behat] Bunch of new options
  * [Behat] DIC optimisations

0.2.5 / 2010-10-22
==================

  * [Behat] Format manager introduced
  * [Behat] Formatters refactoring
  * [Behat] Optmized container parameters to support EverzetBehatBundle
  * [Behat] --no-color => --no-colors

0.2.4 / 2010-10-19
==================

  * [Behat] Autoguess of colors support
  * [Behat] Formatter setup bugfix (properl casing)

0.2.3 / 2010-10-19
==================

  * [Behat] Filters optimisations
  * [Behat] Changed Core Loaders with topic-specific (`StepDefinition\Loader\PHPLoader`,
    `Features\Loader\GherkinLoader`)
  * [Behat] Simplified TestCommand in prepare of Symfony2 BehatBundle
  * [Behat] Configuration file/path setting update (you can now create `behat.yml` inside `./config/behat.yml` & Behat
    will load it
  * [Behat] Updated Redundant & Ambiguous exceptions behavior

0.2.2 / 2010-10-10
==================

  * [Behat] Configuration file/path setting update

0.2.1 / 2010-10-10
==================

  * [PEAR] Fix path to phpbin on installation

0.2.0 / 2010-10-08
==================

  * [Behat] Brand new stateless testers, based on Visitor pattern
  * [Behat] Refactored event listeners & event names
  * [Behat] Refactored formatters to confirm with new stateless testers (statuses now sent as event parameters)
  * [Behat] Refactored ConsoleFormatter (and removed base formatter)
  * [Behat] Removed custom I18n classes & refactored Translator routines in flavor of Symfony\Component\Translation
  * [Behat] Added missed translation strings into XLIFF files
  * [Behat] Optimised multiline arguments (Node instances are sent to definitions instead of their plain representations)
  * [Behat] Support for Scenario Outline tokens replace in multiline arguments (tables & pystrings)
  * [Behat] Step arguments transformations (including table transformations)
  * [Behat] Colorize inline step arguments
  * [Behat] Optimized exit statuses of CLI
  * [Behat] Added ability to turn-off colors
  * [Behat] Added ability to translate formatters output with `--i18n` option
  * [Behat] Bunch of new core feature tests
  * [Gherkin] Parser now uses Symfony Dependency Injection to
  * [Gherkin] Refactored parser to be like AST (Nodes that supports Visitor pattern)
  * [Gherkin] Comments support
  * [Gherkin] Fixed PHPUnit warnings
  * [Behat,Gherkin] PEAR release script to support http://pear.everzet.com release model
  * [Behat,Gherkin] DIC naming refactoring
  * [Behat,Gherkin] Autoloader refactoring
  * [Behat,Gherkin] Removed Zend & Goutte depencies

0.1.5 / 2010-09-25
==================

  * Added ability to call other steps inside step definition
  * Added profiles
  * Refactored container creation routine
  * Single quotes support in step definitions
  * Added tests for hooks, profiles, inline steps

0.1.4 / 2010-09-16
==================

  * Refactored code
  * Removed logic from object constructors
  * Added Loader & Filter interfaces

0.1.3 / 2010-09-14
==================

  * Ability to specify arrays of paths/files for loaders
  * Event hooks and support for `support/hooks.php`
  * Formatters listens events with smallest priority
  * Don't try to load steps if `steps` folder doesn't exists
  * Bugfixes/refactoring

0.1.2 / 2010-09-10
==================

  * Added ability to read from `behat.yml` and `behat.xml`
  * Moved tags filter to separate object
  * Refactored injection controller
  * Optimized event names in event dispatcher
  * Other small fixes/refactorings

0.1.1 / 2010-09-09
==================

  * Added `--tags` option
  * Changed environment (world) routines
  * Added lots of core tests (writed in Behat itself)

0.1.0 / 2010-09-08
==================

  * Initial release
