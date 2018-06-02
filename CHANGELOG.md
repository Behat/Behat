# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [3.4.3] - 2017-11-27
### Fixed
  * BC break due to parameters resolution in Dependency Injection Container

## [3.4.2] - 2017-11-20
### Added
  * [#1095](https://github.com/Behat/Behat/pull/1095): Support for Symfony 4.x
  * [#1096](https://github.com/Behat/Behat/pull/1096): Allow to use latest PHPUnit

## [3.4.1] - 2017-09-18
### Fixed
  * PHP 5.3 style cleanup.

## [3.4.0] - 2017-09-10
### Added
  * [#1071](https://github.com/Behat/Behat/pull/1071): Services auto-wiring
  * [#1054](https://github.com/Behat/Behat/pull/1054): [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md)
    support for helper containers.
  * Support for modern PHPUnit.

### Fixed
  * [#1056](https://github.com/Behat/Behat/pull/1056): Make Gherkin aware of the
  base path so it can filter correctly

### Changed
  * [#1069](https://github.com/Behat/Behat/pull/1069): Rework argument validators

### Deprecated
  * [#1054](https://github.com/Behat/Behat/pull/1054): Deprecated usage
    of `Interop\Container`. Versions prior to `1.2` are not supported, but `1.2`
    is a non-breaking change. If you depend heavily on `Interop`, upgrade to
    `1.2`, which is still supported by helper containers. Aim to migrate to
    `Psr` before Behat 4.0 shows up on horizon
  * PHP versions prior to 5.6 and HHVM were dropped from CI build matrix. It
    doesn't mean that we'll start using features of 5.6 yet, it just means we
    don't get out of our way to support 5.3 and 5.4 anymore. In 4.0 support will
    be completely dropped.

## [3.3.1] - 2017-05-15
### Added
  * [#976](https://github.com/Behat/Behat/pull/1001): Add tests to check that
    snippets treat words containing apostrophes as a single word

### Fixed
  * [#993](https://github.com/Behat/Behat/pull/993) Fix mixed arguments
    organizer not marking typehinted arguments as "defined"
  * [#992](https://github.com/Behat/Behat/pull/993) Do not misinterpret first
    argument as a numbered argument if it is in fact typehinted
  * [#1028](https://github.com/Behat/Behat/pull/1028) Parent / Child class
    argument ambiguity issue with `MixedArgumentResolver`

## [3.3.0] - 2016-12-25
### Added
  * [#973](https://github.com/Behat/Behat/pull/974): Added helper containers
  * [#973](https://github.com/Behat/Behat/pull/974): Added
    `SuiteScopedResolverFactory` extension point

### Removed
  * Removed php 5.3 from the Travis build matrix. You can consider it official
    end of support. 5.4 and 5.5 will follow shortly.

## [3.2.3] - 2016-12-25
### Fixed
  * [#971](https://github.com/Behat/Behat/pull/971): Added support for suite
    names with hyphens

## [3.2.2] - 2016-11-05
### Fixed
  * [#959](https://github.com/Behat/Behat/issues/959): Fix transformations not
    sorted properly on different php version

## [3.2.1] - 2016-09-25
### Changed
  * [#955](https://github.com/Behat/Behat/pull/955): `--snippets-for` is not
    required now as interactive mode is the new default
  * [#954](https://github.com/Behat/Behat/pull/954): Stop execution on missing
    steps when running with `--stop-on-failure` and `--strict` options

## [3.2.0] - 2016-09-20
### Added
  * [#910](https://github.com/Behat/Behat/pull/910): Return type based
    transformations
  * [#903](https://github.com/Behat/Behat/pull/903): Multiline step definitions
    support
  * [#930](https://github.com/Behat/Behat/pull/930): Whole table transformation
  * [#935](https://github.com/Behat/Behat/pull/935): Narrative filters in suites
  * [#936](https://github.com/Behat/Behat/pull/936): Debug command
  * [#931](https://github.com/Behat/Behat/pull/931): Exception handlers
    extension point
  * [#870](https://github.com/Behat/Behat/pull/870): Added build-related files
    and folders to .gitattributes
  * [#946](https://github.com/Behat/Behat/pull/946): Official full Windows
    support with CI ([AppVeyor](http://appveyor.com)) on every build

### Changed
  * [#922](https://github.com/Behat/Behat/pull/922): Snippets generation revamp
  * [#920](https://github.com/Behat/Behat/pull/920): More context for
    pending/failed steps with progress formatter
  * [#905](https://github.com/Behat/Behat/pull/905): Transformations refactoring
  * [#864](https://github.com/Behat/Behat/pull/864): Use only one autoloader if
    possible
  * [#920](https://github.com/Behat/Behat/pull/920): Improve "No specifications
    found" error message
  * Refactor changelog to follow [Keep a Changelog](http://keepachangelog.com/)
  * Refreshed [CONTRIBUTING.md](CONTRIBUTING.md)
  * Refreshed Scrutinizer config

### Fixed
  * [#911](https://github.com/Behat/Behat/pull/911): Fix context isolation for
    Scenario Outlines
  * [#860](https://github.com/Behat/Behat/pull/860): Include basepath in
    `generateKey`
  * [#857](https://github.com/Behat/Behat/pull/857): Only cache failed
    scenario's for rerun
  * [#933](https://github.com/Behat/Behat/pull/933): Save failed runs with suite
    information
  * [#833](https://github.com/Behat/Behat/pull/833): Properly handle interupts
    on PHP7
  * [#904](https://github.com/Behat/Behat/pull/904): Provide clearer exception
    message when long token names used
  * [#941](https://github.com/Behat/Behat/pull/941): Transformation should be
    allowed if printable chars are used

### Deprecated
  * [#922](https://github.com/Behat/Behat/pull/922): `*SnippetAcceptingContext`
    interfaces
  * [#905](https://github.com/Behat/Behat/pull/905): `RuntimeTransformation`
  * [#905](https://github.com/Behat/Behat/pull/905): `Transformation::getPattern`
  * [#920](https://github.com/Behat/Behat/pull/920): `StepStat`

### Removed
  * Remove behat.bat (by Konstantin Kudryashov)

## [3.1.0] - 2016-03-28
### Changed
  * Add support for Symfony 3 (thanks @benji07)
  * Add ability to specify execution order of suite (thanks @ciaranmcnulty)
  * Add translated keywords in definition printer (thanks @WouterJ)
  * Add 'rowtable' transformations (thanks @PurpleBooth)
  * Add 'narrative' filters (thanks @WouterJ)
  * Add JUnit formatter (thanks @WouterJ and @james75)
  * Add Japanese translation (thanks @SNakano)
  * Add romanian translation for formatters (thanks @Chriton)
  * Add table row transformations (thanks @ciaranmcnulty)
  * Add support for negative numbers without surrounding quotes (thanks
    @ryancookdev)
  * Handle case when non-existent config file is used (thanks @watermanio)
  * Handle non-default `error_reporting()`
  * Handle PHP7 errors implementing `Throwable`
  * Fix autoloading from the global installation (thanks @sroze)
  * Fix scenario scope naming (thanks @Taluu)
  * Fix output buffering errors (thanks @tscheepers)
  * Fix xdebug maximum nesting level errors (thanks @WorkingDevel)
  * Fix weird edge case in GroupedSpecificationIterator
  * Allow --verbose flag at CLI (thanks @pfrenssen)
  * Allow hyphens in suite names (thanks @WouterJ)
  * Allow suite settings with null values to exist (thanks @docteurklein)
  * Improve "can not generate snippets" message
  * Improve performance of Turnip parsing (thanks @Sam-Burns)
  * Improve the snippet generation by auto-importing needed classes (thanks
    @stof)

## [3.0.15] - 2015-02-22
### Changed
  * Fix broken null-transformations (Issue #669)
  * Improve exception messages (thanks @dantleech)

## [3.0.14] - 2014-09-23
### Changed
  * Improve generated context class

## [3.0.13] - 2014-08-28
### Changed
  * Add support for typehinted parameters
  * Allow any whitespace characters at the end of context class
  * Fix scenario with decimal number following string in Turnip pattern
  * Fix scenario with empty string in step with Turnip pattern
  * Fix scenario where step has slashes in Turnip pattern

## [3.0.12] - 2014-07-17
### Changed
  * Fix remaining issues with the definition arguments parsing
  * Introduce `Testwork\Argument` component

## [3.0.11] - 2014-07-09
### Changed
  * Fix argument resolution for functions with default values (thanks @alesblaznik)
  * Fix step colouring of internationalised definitions
  * Refactor `ContextFactory` and `RepositorySearchEngine` arguments resolution into the new
    Testwork component - `ArgumentResolver`

## [3.0.10] - 2014-06-29
### Changed
  * Fix argument resolution when named arguments used and method has defaults (thanks @WouterJ)
  * Fix support for decimal numbers in turnip placeholders

## [3.0.9] - 2014-06-20
### Changed
  * Fix definition translations reading bug with multi-suite configurations (thanks @WouterJ for reporting)
  * Fix pretty printer bug with failing background and 2 scenarios (thanks @andytson for reporting)
  * Fix memory footprint calculation (thanks @dready for reporting)

## [3.0.8] - 2014-06-06
### Changed
  * Profile level Gherkin filters are now overridable by CLI filter options
  * Rerun cache path is now configurable
  * Fix turnip-based step definitions starting from token
  * Fix token-based transformations interfering with regex-based ones
  * Rerun cache dump have been optimised

## [3.0.7] - 2014-05-27
### Changed
  * Properly generate keywords in snippets for non-english and `And`, `But` steps (thanks @kibao)
  * Fix regex check bug with transformations that return objects (thanks @vaidasm)
  * Return ability to use custom formatters by specifiying their class names

## [3.0.6] - 2014-05-06
### Changed
  * Fix a small extension registration shortcut issue introduced in previous release (thanks @FrenkyNet)

## [3.0.5] - 2014-05-06
### Changed
  * Fix a suite initialization bug when suite contexts have arguments
  * Fix wrong handling of an empty `behat.yml`
  * Explicitly fail when provided context argument is not supported by constructor
  * Fix extension registration shortcut for 3rd-part plugins

## [3.0.4] - 2014-04-29
### Changed
  * Make sure that `Before*Tested` is always executed before `Before*` hooks
  * Introduce additional `After*Setup` and `Before*Teardown` events
  * Improved the error reporting for invalid regexes in step definitions (thanks @stof)

## [3.0.3] - 2014-04-27
### Changed
  * Support definition transformations without capture groups
  * Override gherkin filters in custom profiles instead of merging them
  * Refactored the handling of colors to set them earlier
    ([#513](https://github.com/Behat/Behat/pull/513) thanks to @stof)

## [3.0.2] - 2014-04-26
### Changed
  * Fix warning on empty scenarios

## [3.0.1] - 2014-04-26
### Changed
  * Make sure that `AfterStep` hook is running even if step is failed
    ([504](https://github.com/Behat/Behat/issues/504))
  * Optimised the way service wrappers are registered (thanks @stof)

## [3.0.0] - 2014-04-20
### Changed
  * Brand new highly extendable and clear architecture
  * Support for multiple suites per profile
  * Support for multiple contexts per suite
  * Support for multiple feature paths per suite
  * Support for filtered suites
  * Support for unique context constructor parameters
  * Hooks are first class citizens and thus have their own error and output buffering
  * Turnip syntax in definitions
  * Reworked formatters with improved error and output buffering
  * Rerun does not require precache run
  * New gherkin role filter
  * Improved error handling with 3 levels of error reporting (-v, -vv, -vvv)
  * Dropped subcontexts
  * Dropped chained steps
  * Dropped closured definitions

## 3.0.0rc3 - 2014-03-16
### Changed
  * Multiline step description support ([082da36b7db2525700287616babe982e485330d1](https://github.com/Behat/Behat/commit/082da36b7db2525700287616babe982e485330d1))
  * Added ability to choose all 3 verbosity levels and moved stack traces to the 2nd one ([d550f72d6aa49f0f87a6ce0e50721356a5d04c45](https://github.com/Behat/Behat/commit/d550f72d6aa49f0f87a6ce0e50721356a5d04c45))
  * Renamed Subject to Specification ([#447](https://github.com/Behat/Behat/pull/447))
  * Refactored ContextSnippetGenerator ([#445](https://github.com/Behat/Behat/pull/445))
  * Refactored context arguments handling ([#446](https://github.com/Behat/Behat/pull/446))
  * Refactored testers to use composition over inheritance and added setUp/tearDown phase to them ([#457](https://github.com/Behat/Behat/pull/457))
  * Refactored output formatters to be chain of event listeners
  * Refactored hooks to use [scopes](https://github.com/Behat/Behat/tree/3.0/src/Behat/Behat/Hook/Scope) instead of events
  * Fixed the GroupedSubjectIterator when dealing with an empty iterator ([2c1312780d610f01116ac42fb958c0c09a64c041](https://github.com/Behat/Behat/commit/2c1312780d610f01116ac42fb958c0c09a64c041))
  * Forced the paths.base to use a real path all the time ([b## [4477d7cf3f9550874c609d4edc5a4f55390672c](https://github.com/Behat/Behat/commit/b4477d7cf3f9550874c609d4edc5a4f55390672c))

3.0.0rc2] - 2014-01-10

### Changed
  * Fixed progress formatter hooks support
  * Reintroduced suite hooks (with an additional functionality of name filtering)
  * Behat tells about steps that it couldn't generate snippets for
  * Memory consumption optimizations
  * Fixed contexts inheritance
  * New formatter translations

  * Added constructor arguments and class resolving extension points to context creation routine
  * Simplified and cleaned `Context` package of the Behat
  * Minor public API changes across the board (simplification)
  * Optimized subject finding routine and cleaned extension points (`SubjectLocator`)
  * Both `ExampleTested` and `ScenarioTested` now use same method name - `getScenario()`
  * Added exception accessors to `StepTestResult`
  * Renamed `ExerciseTester` to `Exercise`
  * Added `HookableEvent` to Testwork, which extends `LifecycleEvent`
  * Made `priority` attribute of a tag optional
  * Changed all occurrences of `classname` to `class` across public API
  * Renamed `GherkinSuite` to `GenericSuite` and moved it into the Testwork
  * Added `initialize` call to extension lifecycle and Extension interface
  * Renamed some extensions config keys to be more intuitive

## 3.0.0rc1 - 2014-01-01
### Changed
  * New layered and highly extendable architecture
  * Standard output buffering of definitions and hooks
  * Hooks as first class citizens
  * New pretty and progress formatters
  * Huge speed and memory footprint improvements
  * Moved 40% of non-Behat related codebase into a shared foundation called Testwork

## 3.0.0beta8 - 2013-10-01
### Changed
  * Add `*SnippetsFriendlyInterface`(s) that are now required to generate snippets
  * Add support for turnip-style definitions
  * Use turnip-style definitions by default from `--init`
  * Rename `SuitesLoader` to `SuitesRegistry` to clarify purpose
  * Extract snippet generators into extendable component
  * Extract context generators into extendable component

## 3.0.0beta7 - 2013-09-29
### Changed
  * Multivalue options are now array options (format, output, name and tags)
  * Added back junit formatter (should support all junit formats from 4 to 7)
  * Added back html formatter
  * Small optimizations and refactorings
  * Proper handling of hook failures

## 3.0.0beta6 - 2013-09-25
### Changed
  * Skip step execution and `AfterStep` hook if its `BeforeStep` hook failed
  * Fix failure-initiated skips of hooks in Scenario and Example testers
  * Refactor Suite routines
  * Cleanup Context Pools
  * Enhance `--definitions` option with suites output and regex search
  * Add `toString()` methods to `DefinitionInterface` and `TransformationInterface`
  * Add `SnippetlessContextInterface` to `Snippet` namespace - to prevent snippet generation for
    custom contexts

## 3.0.0beta5 - 2013-09-15
### Changed
  * Switch to Gherkin 3.0 parser
  * Complete rewrite of pretty formatter (much better outline handling)
  * Automatically add `use` for `PendingException` to contexts during `--append-snippets`
  * Lots of optimizations

## 3.0.0beta4 - 2013-08-17
### Changed
  * Cleanup suite configuration sub-system
  * New ability to turn off specific suites through `behat.yml`
  * Support for danish language

## 3.0.0beta3 - 2013-08-13
### Changed
  * Refactor extension sub-system. Update `ExtensionInterface`
  * Avoid trying to create folders for non-fs suites

## 3.0.0beta2 - 2013-08-13
### Changed
  * Remove support for Symfony 2.0 components

## 3.0.0beta1 - 2013-08-13
### Changed
  * New suite-centric architecture
  * New context pools sub-system with multi-context support
  * New dynamic event-driven testing core
  * Refactored console processors sub-system
  * Refactored formatters management sub-system
  * 8 new process extension points and 36 generic execution extension points
  * Gherkin caching is enabled by default
  * Rerun is enabled by default (use `--rerun` to rerun failed scenarios)
  * New Gherkin Role filter
  * Subcontexts removed in favor of context pools
  * Chained steps extracted into [separate extension](https://github.com/Behat/ChainedStepsExtension)
  * Closured step definitions removed

## 2.5.0 - 2013-08-11
### Changed
  * First Behat LTS release
  * Update Junit formatter to reflect latest junit format (thanks @alistairstead)
  * Fix some container options

## 2.4.6 - 2013-06-06
### Changed
  * New --stop-on-failure option
  * Support JSON in environment variables
  * Update Gherkin
  * Support Symfony 2.3
  * Out-of-the-box support for PHPUnit assertions pretty output

## 2.4.5 - 2013-01-27
### Changed
  * Added wrapping of lines in progress formatter
  * Added `--append-to` option to be able to add snippets to custom class
  * Both `ScenarioEvent` and `OutlineExampleEvent` now extend same `BaseScenarioEvent` class
  * Highly improved ability to create simple custom extensions
  * Always hide stack traces for `PendingException`
  * Ensured compatibility with all major symfony versions
  * Fixed configs import directive and loading precedence
  * Fixed path to vendor dir (solves problem of custom vendor dirs)

## 2.4.4 - 2012-09-12
### Changed
  * Fixed `RuntimeException` namespacing error
  * Added `FormatterManager::disableFormatter(s)` method
  * Updated Gherkin parser and fixed couple of helper bugs

## 2.4.3 - 2012-07-28
### Changed
  * Fixed broken `output_path` setting ([issue #169](https://github.com/Behat/Behat/issues/169))
  * Added shellbang to phar executable ([issue #167](https://github.com/Behat/Behat/issues/167))
  * Added feature title to progress exceptions ([issue #166](https://github.com/Behat/Behat/issues/166))
  * Tuned failed formatter to print only failed examples in outline ([issue #154](https://github.com/Behat/Behat/issues/154))
  * Small bugfixes

## 2.4.2 - 2012-06-26
### Changed
  * Fixed broken autoloading with Composer installation

## 2.4.1 - 2012-06-26
### Changed
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

## 2.4.0 - 2012-05-15
### Changed
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

## 2.3.5 - 2012-03-30
### Changed
  * Fixed formatter language configuration and locale guesser

## 2.3.4 - 2012-03-28
### Changed
  * Added `StepEvent::getLogicalParent()`. Fixed issue ### 115

2.3.3 - 2012-03-09

### Changed
  * Implemented Gherkin caching support ([--cache](https://github.com/Behat/Behat/commit/753c4f6e392a873a640543306191d92e6dc91099))
  * Line ranges filtering support (`behat features/some.feature:12-19`. Thanks @headrevision)
  * `behat.yml.dist` configs support out of the box
  * Minor bug fixes
  * Updated Gherkin

## 2.3.2 - 2012-01-29
### Changed
  * Fixed bug in `ErrorException`, that caused wrong exceptions on warnings and notices

## 2.3.1 - 2012-01-26
### Changed
  * Updated error handler to avoid suppressed exceptions
  * Autoload bootstrap scripts in their name order
  * Updated Gherkin dependency to v## 2.0.1

2.3.0 - 2012-01-19

### Changed
  * Switch to the Behat\Gherkin 2.0 usage
  * Migration to the single-file translation
  * Support for callables inside steps chains
  * Support for `*.yml` and `*.php` as definition translations
  * Added opposite options to option switchers (`--[no-]colors`, `--[no-]multiline`, etc.)
  * Redesigned `--story-syntax`
  * Refactored Runner
  * Performance improvements
  * Bugfixes

## 2.2.7 - 2012-01-13
### Changed
  * Added ability to search translated definitions with `--definitions`
  * Fixed custom formatters use bug

## 2.2.6 - 2012-01-09
### Changed
  * Fixed pretty and html formatters printing of undefined steps in outlines

## 2.2.5 - 2012-01-07
### Changed
  * `BEHAT_PARAMS` env variable support (083092e)
  * HTML formatter print styles optimization (@davedevelopment)

## 2.2.4 - 2012-01-04
### Changed
  * Prevent method name duplication with definition snippets

## 2.2.3 - 2012-01-04
### Changed
  * Fixed couple of `--append-snippets` bugs

## 2.2.2 - 2011-12-21
### Changed
  * Fixed Composer deps

## 2.2.1 - 2011-12-21
### Changed
  * Fixed Composer package bin

## 2.2.0 - 2011-12-14
### Changed
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
  * Spanish translation updated (Andrés Botero)
  * Locale autodetect

## 2.1.3 - 2011-11-04
### Changed
  * Substep translations support
  * Correctly print undefined substeps in pretty printer
  * @Transform callback now gets all provided matches
  * Always set proper encoding (UTF## 8)

2.1.2 - 2011-10-12

### Changed
  * Fixed filtered feature hooks
  * Fixed JUnit formatter time output in some locales

## 2.1.1 - 2011-10-09
### Changed
  * Fixed multiline titles printing bug
  * Fixed outline parameter inside step argument printing bug

## 2.1.0 - 2011-09-12
### Changed
  * Totally revamped HTML formatter template
  * Added transliteration support to definition snippets (for most langs)
  * Written missed features and fixed some bugs
  * Stabilization fixes for 3 major OS: MacOS/Ubuntu/Windows

## 2.0.5 - 2011-08-07
### Changed
  * Cleaned ContextDispatcher extension points
  * Cleaned context-parameters passing behavior

## 2.0.4 - 2011-08-02
### Changed
  * Subcontexts aliasing and retrieving
  * Multiple steps chaining
  * `--snippets-paths` option to show steps alongside the snippets
  * getContextParameters() method in SuiteEvent and FeatureEvent
  * Updated to Symfony2 stable components
  * Spanish translation
  * Dutch translation

## 2.0.3 - 2011-07-20
### Changed
  * Fixed JUnit formatter CDATA output

## 2.0.2 - 2011-07-17
### Changed
  * Added extra checks to context instance mapper
  * Fixed i18n support in definitions printer
  * Refactored Gherkin tags inheritance

## 2.0.1 - 2011-07-12
### Changed
  * Exception prefix added to statuses. Now you should throw `PendingException` instead of just
    `Pending`

## 2.0.0 - 2011-07-12
### Changed
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

## 1.1.9 - 2011-06-17
### Changed
  * Updated to the latest Symfony components

## 1.1.8 - 2011-06-09
### Changed
  * Fixed empty match printing in Pretty and HTML formatters
  * Updated to latest Symfony components

## 1.1.7 - 2011-06-03
### Changed
  * Fixed steps colorization bug in outline
  * Additional checks in config import routine

## 1.1.6 - 2011-05-27
### Changed
  * Updated Symfony vendors
  * Refactored console formatters

## 1.1.5 - 2011-05-17
### Changed
  * Fixed CWD path finding
  * Fixed HTML formatter (thanks @glenjamin)

## 1.1.4 - 2011-05-03
### Changed
  * Fixed `--out` option usage critical bug
  * Added ability to specify `output_path` from config file

## 1.1.3 - 2011-04-28
### Changed
  * JUnit formatter fix
  * Formatters basePath fix. Now formatters uses CWD as path trimmer
  * Relative paths locator bug fix
  * Show table argument header in HTML formatter

## 1.1.2 - 2011-04-27
### Changed
  * Fixed custom features path locator bug(issue ### 020)

1.1.1 - 2011-04-21

### Changed
  * Fixed paths finding routines
  * Totally refactored BehatCommand
  * Added rerun functionality (`--rerun`)
  * Ability to remove previously specified paths in `behat.yml`
  * Bugfixes and little tweaks

## 1.1.0 - 2011-04-04
### Changed
  * New configuration system with profiles and imports support
  * New event system
  * Environment parameters support
  * Named regex arguments support
  * Japanese translation for formatters
  * JUnit formatter bugfixes
  * HTML and Pretty formatters multiple arguments print bugfix
  * Step snippets (proposals) bugfixes
  * Updated vendor libraries

## 1.0.0 - 2011-03-08
### Changed
  * Changed XSD
  * Updated vendors

## 1.0.0RC6 - 2011-03-03
### Changed
  * Cleaned command options
  * Added --init option
  * Multiple paths support in behat.yml
  * Application options refactoring

## 1.0.0RC5 - 2011-02-25
### Changed
  * Windows support
  * Bundled features hooks optimizations

## 1.0.0RC4 - 2011-02-23
### Changed
  * Pretty formatter tag printing fix
  * Custom formatter specification fix in `behat.yml`
  * Symfony components updated
  * Extension configuration manager (Symfony\Component\Config component)
  * Cleaning of `behat.yml` configurator (thanks to Symfony\Component\Config)
  * Additional formatter parameters support in `behat.yml`

## 1.0.0RC3 - 2011-02-18
### Changed
  * Event dispatcher binding optimizations
  * Command API optimizations for easier overloading
  * Formatter path trimming bugfix
  * BehatExtension config merging support

## 1.0.0RC2 - 2011-02-15
### Changed
  * Step printing option bugfix

## 1.0.0RC1 - 2011-02-15
### Changed
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

## 0.3.6 - 2010-12-07
### Changed
  * [Behat,Gherkin] Fixed French support includes (fr)

## 0.3.6 - 2010-12-06
### Changed
  * [Behat] Updated Symfony2 Components to latest PR4
  * [Gherkin] Added French support (fr)
  * [Gherkin] Added German support (de)
  * [Behat] Small bugfixes

## 0.3.5 - 2010-11-19
### Changed
  * [Behat] Refactored EnvironmentBuilder to allow Environment service definition overload

## 0.3.4 - 2010-11-18
### Changed
  * [Behat] Introduced environment builder
  * [Gherkin,Behat] id locale support

## 0.3.3 - 2010-11-07
### Changed
  * [Gherkin] Added ability to create Table & PyString nodes with hands (in your step to step calls for example)
  * [Gherkin] Added getRowsHash() method to TableNode, so now you can "rotate" given tables
  * [Gherkin] You now can add comments before language specification in your feature files

## 0.3.2 - 2010-11-06
### Changed
  * [Gherkin] Added ability to specify extended langs (en-US)
  * [Behat,Gherkin] Added pt-BR translation

## 0.3.1 - 2010-11-02
### Changed
  * [Behat] JUnit formatter
  * [Behat] Pretty & HTML formatter background hooks fix
  * [Behat] Other small fixes

## 0.3.0 - 2010-11-02
### Changed
  * [Behat] Refactored tags filter
  * [Behat] Added name filter
  * [Behat] Refactored hooks
  * [Behat] Added tagged/named hooks
  * [Behat] Customizable HTML formatter with w3c valid default markup
  * [Behat] Ability to specify out path for formatters
  * [Behat] Bunch of new options
  * [Behat] DIC optimisations

## 0.2.5 - 2010-10-22
### Changed
  * [Behat] Format manager introduced
  * [Behat] Formatters refactoring
  * [Behat] Optmized container parameters to support EverzetBehatBundle
  * [Behat] --no-color => --no-colors

## 0.2.4 - 2010-10-19
### Changed
  * [Behat] Autoguess of colors support
  * [Behat] Formatter setup bugfix (properl casing)

## 0.2.3 - 2010-10-19
### Changed
  * [Behat] Filters optimisations
  * [Behat] Changed Core Loaders with topic-specific (`StepDefinition\Loader\PHPLoader`,
    `Features\Loader\GherkinLoader`)
  * [Behat] Simplified TestCommand in prepare of Symfony2 BehatBundle
  * [Behat] Configuration file/path setting update (you can now create `behat.yml` inside `./config/behat.yml` & Behat
    will load it
  * [Behat] Updated Redundant & Ambiguous exceptions behavior

## 0.2.2 - 2010-10-10
### Changed
  * [Behat] Configuration file/path setting update

## 0.2.1 - 2010-10-10
### Changed
  * [PEAR] Fix path to phpbin on installation

## 0.2.0 - 2010-10-08
### Changed
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

## 0.1.5 - 2010-09-25
### Changed
  * Added ability to call other steps inside step definition
  * Added profiles
  * Refactored container creation routine
  * Single quotes support in step definitions
  * Added tests for hooks, profiles, inline steps

## 0.1.4 - 2010-09-16
### Changed
  * Refactored code
  * Removed logic from object constructors
  * Added Loader & Filter interfaces

## 0.1.3 - 2010-09-14
### Changed
  * Ability to specify arrays of paths/files for loaders
  * Event hooks and support for `support/hooks.php`
  * Formatters listens events with smallest priority
  * Don't try to load steps if `steps` folder doesn't exists
  * Bugfixes/refactoring

## 0.1.2 - 2010-09-10
### Changed
  * Added ability to read from `behat.yml` and `behat.xml`
  * Moved tags filter to separate object
  * Refactored injection controller
  * Optimized event names in event dispatcher
  * Other small fixes/refactorings

## 0.1.1 - 2010-09-09
### Changed
  * Added `--tags` option
  * Changed environment (world) routines
  * Added lots of core tests (writed in Behat itself)

## 0.1.0 - 2010-09-08
### Changed
  * Initial release

[Unreleased]: https://github.com/Behat/Behat/compare/v3.4.3...HEAD
[3.4.3]: https://github.com/Behat/Behat/compare/v3.4.2...v3.4.3
[3.4.2]: https://github.com/Behat/Behat/compare/v3.4.1...v3.4.2
[3.4.1]: https://github.com/Behat/Behat/compare/v3.4.0...v3.4.1
[3.4.0]: https://github.com/Behat/Behat/compare/v3.3.1...v3.4.0
[3.3.1]: https://github.com/Behat/Behat/compare/v3.3.0...v3.3.1
[3.3.0]: https://github.com/Behat/Behat/compare/v3.2.3...v3.3.0
[3.2.3]: https://github.com/Behat/Behat/compare/v3.2.2...v3.2.3
[3.2.2]: https://github.com/Behat/Behat/compare/v3.2.1...v3.2.2
[3.2.1]: https://github.com/Behat/Behat/compare/v3.2.0...v3.2.1
[3.2.0]: https://github.com/Behat/Behat/compare/v3.1.0...v3.2.0
[3.1.0]: https://github.com/Behat/Behat/compare/v3.0.15...v3.1.0
[3.0.15]: https://github.com/Behat/Behat/compare/v3.0.14...v3.0.15
[3.0.14]: https://github.com/Behat/Behat/compare/v3.0.13...v3.0.14
[3.0.13]: https://github.com/Behat/Behat/compare/v3.0.12...v3.0.13
[3.0.12]: https://github.com/Behat/Behat/compare/v3.0.11...v3.0.12
[3.0.11]: https://github.com/Behat/Behat/compare/v3.0.10...v3.0.11
[3.0.10]: https://github.com/Behat/Behat/compare/v3.0.9...v3.0.10
[3.0.9]: https://github.com/Behat/Behat/compare/v3.0.8...v3.0.9
[3.0.8]: https://github.com/Behat/Behat/compare/v3.0.7...v3.0.8
[3.0.7]: https://github.com/Behat/Behat/compare/v3.0.6...v3.0.7
[3.0.6]: https://github.com/Behat/Behat/compare/v3.0.5...v3.0.6
[3.0.5]: https://github.com/Behat/Behat/compare/v3.0.4...v3.0.5
[3.0.4]: https://github.com/Behat/Behat/compare/v3.0.3...v3.0.4
[3.0.3]: https://github.com/Behat/Behat/compare/v3.0.2...v3.0.3
[3.0.2]: https://github.com/Behat/Behat/compare/v3.0.1...v3.0.2
[3.0.1]: https://github.com/Behat/Behat/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/Behat/Behat/compare/v2.5.5...v3.0.0
