# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [3.23.0] - 2025-07-15

### Added

* Support providing multiple paths for features to run by @marmichalski in [#1611](https://github.com/Behat/Behat/pull/1611)
* Add editorUrl option to provide clickable links to IDEs in CLI output by @carlos-granados in [#1638](https://github.com/Behat/Behat/pull/1638)
* Add removePrefix option to trim printed paths in CLI output @carlos-granados in [#1644](https://github.com/Behat/Behat/pull/1644)
* Add line number of last executed step in JUnit <testcase> by @magnetik in [#1608](https://github.com/Behat/Behat/pull/1608)

### Fixed

* Relaxed constraints on nikic/php-parser and composer/xdebug-handler dependencies to allow wider use of newer Behat
  releases by @acoulton in [#1650](https://github.com/Behat/Behat/pull/1650) and [#1649](https://github.com/Behat/Behat/pull/1649)

### Internal

* Add Rector and standardise namespace imports by @carlos-granados in [#1640](https://github.com/Behat/Behat/pull/1640)
* Update expected output in tests to reflect new gherkin translations by @acoulton in [#1635](https://github.com/Behat/Behat/pull/1635)
* Remove start signs in docs to improve copy/paste by @yosmanyga in [#1647](https://github.com/Behat/Behat/pull/1647)

## [3.16.1] - 2025-05-07

### Changed

* Remove dependency on file location in Gherkin package. The (internal) service container parameters gherkin.paths.lib
  and gherkin.paths.i18n are no longer defined or used. Minimum behat/gherkin version is now ^4.12.0.
  By @carlos-granados in #1604, backported from 3.20.0 to fix errors for users stuck on 3.16.0 due to
  dependency conflicts.

## [3.22.0] - 2025-05-05

### Changed

* Suggested method names for new step definitions will no longer be transliterated to ASCII.
  Users working in languages whose characters are mostly outside the allowed UTF-8 range
  will see generic `stepDefinitionX` names. We have provided an extension point for custom
  suggestion implementations, and would consider providing an official extension. If this
  affects you, please open a discussion on the Behat repository.
  By @acoulton in [#1633](https://github.com/Behat/Behat/pull/1633)
* The behat/transliterator package is no longer required and will shortly be archived.

### Internal

* Update all code style to Symfony coding style (with small variations) by @carlos-granados in [#1628](https://github.com/Behat/Behat/pull/1628)

## [3.21.1] - 2025-04-22

### Fixed

* ExceptionPresenter was causing a TypeError when constructed with a null $basePath by @acoulton in [#1631](https://github.com/Behat/Behat/pull/1631)

## [3.21.0] - 2025-04-18

### Fixed

* JUnit formatter options were being lost when converting config to PHP by @acoulton in [#1622](https://github.com/Behat/Behat/pull/1622)
* Contexts with constructor arguments were not properly converted to PHP configuration by @acoulton in [#1619](https://github.com/Behat/Behat/pull/1619)

### Added

* New CLI and configuration option to print all paths as absolute paths by @carlos-granados in [#1620](https://github.com/Behat/Behat/pull/1620)
* Expose PHP configuration interface for tester and error_reporting options by @acoulton in [#1626](https://github.com/Behat/Behat/pull/1626)
* Improve config conversion to PHP to generate class references instead of string names; reference extensions by their
  fully qualified class names; convert output_verbosity values to constants; and convert tester and error_reporting
  configuration to PHP by @acoulton in [#1619](https://github.com/Behat/Behat/pull/1619), [#1623](https://github.com/Behat/Behat/pull/1623)
  and [#1626](https://github.com/Behat/Behat/pull/1626)

### Internal

* Refactor features for configurable tester / error_reporting options by @acoulton in [#1625](https://github.com/Behat/Behat/pull/1625)

## [3.20.0] - 2025-04-02

### Changed

* Remove dependency on file location in Gherkin package. The (internal) service container parameters `gherkin.paths.lib`
  and `gherkin.paths.i18n` are no longer defined or used. Minimum behat/gherkin version is now ^4.12.0.
  By @carlos-granados in [#1604](https://github.com/Behat/Behat/pull/1604).

### Added

* `--allow-no-tests` CLI option to pass even if no specifications found by @Kingdutch in [#1420](https://github.com/Behat/Behat/pull/1420)
* `--convert-config` CLI option to convert the yaml config to php by @carlos-granados in [#1605](https://github.com/Behat/Behat/pull/1605)

### Internal

* Apply PSR-12 and PER-CS2.0 code styles by @carlos-granados in [#1599](https://github.com/Behat/Behat/pull/1599)
  and [#1606](https://github.com/Behat/Behat/pull/1606)

## [3.19.0] - 2025-02-13

### Changed

* Remove internal wrappers for PSR Container interfaces - may affect projects using container-interop/container-interop
  < 1.2.0 (released in 2017, package now deprecated and unsupported by behat since 2021).
  By @acoulton in [#1584](https://github.com/Behat/Behat/pull/1584)
* Remove legacy Symfony event dispatchers - these were internal wrappers to support symfony <5 and >=5, both now
  redundant. By @carlos-granados in [#1585](https://github.com/Behat/Behat/pull/1585)

### Added

* Add option to print unused definitions by @carlos-granados in [#1594](https://github.com/Behat/Behat/pull/1594) and
  [#1597](https://github.com/Behat/Behat/pull/1597)
* Support transforming named column(s) in any table by @carlos-granados in [#1593](https://github.com/Behat/Behat/pull/1593)

### Fixed

* Allow unicode characters in table transformations by @carlos-granados in [#1595](https://github.com/Behat/Behat/pull/1595)

### Internal

* Use real files instead of generated files in local tests by @carlos-granados in [#1544](https://github.com/Behat/Behat/pull/1544)
* Adopt PHP CS Fixer and apply PSR2 styles by @carlos-granados in [#1592](https://github.com/Behat/Behat/pull/1592)
* Migrate from Psalm to PHPStan and improve internal type safety by @carlos-granados in [#1583](https://github.com/Behat/Behat/pull/1583),
  [#1589](https://github.com/Behat/Behat/pull/1589) and [#1590](https://github.com/Behat/Behat/pull/1590) and by
  @stof in [#1591](https://github.com/Behat/Behat/pull/1591)

## [3.18.1] - 2025-01-10

### Fixed

* Fix handling of `show_output` option when used with a custom formatter that does not define it by @carlos-granados in
  [#1587](https://github.com/Behat/Behat/pull/1587)

## [3.18.0] - 2025-01-09

### Changed

* Add new methods to the `Behat\Hook\Hook` and `Behat\Step\Definition` interfaces used internally by step definition
  attributes by @carlos-granados in [#1573](https://github.com/Behat/Behat/pull/1573)

### Added

* Add `show_output` formatter option to control if/when to display stdout generated during tests
  by @carlos-granados in [#1576](https://github.com/Behat/Behat/pull/1576)

### Fixed

* Do not disable xdebug if there is an active debugging connection by @carlos-granados in [#1581](https://github.com/Behat/Behat/pull/1581)
* Inherit step definition attributes on methods extended from parent Context by @fmatsos in [#1567](https://github.com/Behat/Behat/pull/1567)

### Internal

* Add PHPStan and improve / fix docblock annotations and type-safety within methods to achieve level 3 by
  @carlos-granados in [#1571](https://github.com/Behat/Behat/pull/1571), [#1573](https://github.com/Behat/Behat/pull/1573)
  [#1578](https://github.com/Behat/Behat/pull/1578) and by @stof in [#1575](https://github.com/Behat/Behat/pull/1575)
* Use annotations rather than attributes for step definitions in Behat's own feature suites by @fmatsos in [#1564](https://github.com/Behat/Behat/pull/1564)
* Remove composer dev dependency on legacy herrera-io/box by @acoulton in [#1580](https://github.com/Behat/Behat/pull/1580)
* Do not run ci builds if only markdown files have changed by @codisart in [#1582](https://github.com/Behat/Behat/pull/1582)

## [3.17.0] - 2024-12-18

### Changed

* Use attributes rather than annotations when generating suggested Context snippets
  by @fmatsos in [#1549](https://github.com/Behat/Behat/pull/1549) and [#1569](https://github.com/Behat/Behat/pull/1569)
* Disable Xdebug unless `--xdebug` is specified on the CLI, to improve performance by @carlos-granados in [#1560](https://github.com/Behat/Behat/pull/1560)

### Added

* :partying_face: Support configuring Behat with a PHP file and helper objects / methods - see [the docs](https://docs.behat.org/en/latest/user_guide/configuration/suites.html)
  by @loic425 in [#1537](https://github.com/Behat/Behat/pull/1537), [#1538](https://github.com/Behat/Behat/pull/1538),
  [#1550](https://github.com/Behat/Behat/pull/1550), [#1547](https://github.com/Behat/Behat/pull/1547),
  [#1540](https://github.com/Behat/Behat/pull/1540), [#1546](https://github.com/Behat/Behat/pull/1546),
  [#1556](https://github.com/Behat/Behat/pull/1556), [#1557](https://github.com/Behat/Behat/pull/1557) and
  [#1558](https://github.com/Behat/Behat/pull/1558).
* Display location of hook failure in progress printer by @carlos-granados in [#1526](https://github.com/Behat/Behat/pull/1526)
* Print failed hooks summary at the end of the pretty format by @carlos-granados in [#1530](https://github.com/Behat/Behat/pull/1530)
* Print `<failure>` nodes for all hook failures in the junit output by @carlos-granados in [#1536](https://github.com/Behat/Behat/pull/1536)
* Add the `#[Transform]` attribute, equivalent to the `@Transform` annotation by @carlos-granados in [#1545](https://github.com/Behat/Behat/pull/1545)
* Allow using the `--narrative` filter on the command line by @carlos-granados in [#1559](https://github.com/Behat/Behat/pull/1559)

### Fixed

* Show more meaningful message if no `output_path` is specified for the junit formatter by @carlos-granados in [#1533](https://github.com/Behat/Behat/pull/1533)
* Fix error from the JUnit printer if scenario has no title by @mvhirsch in [#1525](https://github.com/Behat/Behat/pull/1525)
* Fix naming of suggested methods when generating regex snippets for steps defined with single quotes by @carlos-granados in [#1524](https://github.com/Behat/Behat/pull/1524)

### Internal

* Improve / fix docblock annotations and type-safety within methods to achieve Psalm level 6
  by @carlos-granados in [#1554](https://github.com/Behat/Behat/pull/1554), [#1562](https://github.com/Behat/Behat/pull/1562),
  [#1566](https://github.com/Behat/Behat/pull/1566), [#1568](https://github.com/Behat/Behat/pull/1568)
  [#1570](https://github.com/Behat/Behat/pull/1570).
* Improve failure output of Behat's own tests with unexpected status code or output by @jdeniau in [#1532](https://github.com/Behat/Behat/pull/1532)
* Remove redundant tests for hook failures in junit formatter by @carlos-granados in [#1543](https://github.com/Behat/Behat/pull/1543)
* Update .editorconfig indent size to 2 for feature files by @carlos-granados in [#1528](https://github.com/Behat/Behat/pull/1528)
* Update static analysis to use Psalm v5 by @carlos-granados in [#1548](https://github.com/Behat/Behat/pull/1548)
* Remove tagging of tests that require PHP8, as these now always run by @carlos-granados in [#1551](https://github.com/Behat/Behat/pull/1551)
* Add composer scripts for testing tools by @carlos-granados in [#1555](https://github.com/Behat/Behat/pull/1555)

## [3.16.0] - 2024-11-08

### Changed

* Drop support for PHP < 8.1, Symfony < 5.4 and Symfony 6.0 - 6.3. In future Behat will drop support for PHP and symfony
  versions as they reach EOL. by @AlexSkrypnyk in [#1504](https://github.com/Behat/Behat/pull/1504)
* ApplicationFactory::VERSION is deprecated and will not be updated, Behat now internally uses composer's runtime API
  to report the running version. by @acoulton in [#1520](https://github.com/Behat/Behat/pull/1520)
* API changes to 2 final Behat classes that are not considered part of the public API (but were not explicitly marked
  as such). This may affect users who are creating instances direct rather than through the DI container as expected.
  See Behat\Behat\EventDispatcher\Cli\StopOnFailureController in #1501 and Behat\Behat\Tester\Cli\RerunController in #1518.

### Added

* Render JUnit test durations with millisecond precision e.g. `1.234` rather than only as integer seconds
  by @uuf6429 in [#1460](https://github.com/Behat/Behat/pull/1460)
* Support specifying `stop_on_failure` within behat.yml by @jdeniau in [#1512](https://github.com/Behat/Behat/pull/1512),
  [#1501](https://github.com/Behat/Behat/pull/1501) and [#1516](https://github.com/Behat/Behat/pull/1516)
* Allow BeforeSuite/AfterSuite hooks to be marked with attributes by @rpkamp in [#1511](https://github.com/Behat/Behat/pull/1511)

### Fixed

* `--rerun` all tests that should be considered failed (including undefined, when strict) by @carlos-granados in [#1518](https://github.com/Behat/Behat/pull/1518)
* Improve handling exceptions from unsupported PHPUnit versions by @acoulton and @uuf6429 in [#1521](https://github.com/Behat/Behat/pull/1521)
* Fix high memory consumption when using Junit formatter by @simon-auch in [#1423](https://github.com/Behat/Behat/pull/1423)
* Fix error when attempting to format long output messages by @jonpugh in [#1439](https://github.com/Behat/Behat/pull/1439)

### Internal

* Remove the unnecessary alias of the ScenarioInterface as it just causes confusion by @carlos-granados in [#1500](https://github.com/Behat/Behat/pull/1500)
* Improve output when behat's own tests pass or fail unexpectedly by @jdeniau in [#1515](https://github.com/Behat/Behat/pull/1515)
* Update guidance on submitting PRs by @acoulton in [#1505](https://github.com/Behat/Behat/pull/1505)
* Fix indentation in Github Actions config by @AlexSkrypnyk in [#1502](https://github.com/Behat/Behat/pull/1502)
* Fix Github Actions phar upload for the release by @carlos-granados in [#1509](https://github.com/Behat/Behat/pull/1509)

## [3.15.0] - 2024-10-29

***Note:** This release also bumps the minor version of behat/gherkin to 4.10.0, which was released on 2024-10-19 with
  a behaviour-changing bugfix related to the parsing of `\` characters in scenarios.
  See [the Behat/Gherkin CHANGELOG](https://github.com/Behat/Gherkin/blob/master/CHANGES.md#4100--2024-10-19).*

### Added

* PHP 8.4 support by @heiglandreas in [#1473](https://github.com/Behat/Behat/pull/1473), @jrfnl in [#1478](https://github.com/Behat/Behat/pull/1478),
  @jrfnl in [#1477](https://github.com/Behat/Behat/pull/1477)
* Support config files named `behat.dist.y[a]ml` by @uuf6429 in [#1464](https://github.com/Behat/Behat/pull/1464)
* Add a `--rerun-only` flag to immediately exit 0 without running anything if there were no failures on the previous run
  by @Treast in [#1466](https://github.com/Behat/Behat/pull/1466)
* Allow profiles to override extensions in the config file by @Zayon in [#1341](https://github.com/Behat/Behat/pull/1341)
* Support configuring a preferred profile to use instead of `default` if nothing was specified at runtime
  by @andrewnicols in [#1334](https://github.com/Behat/Behat/pull/1334)
* Add void return type when generating new snippets by @carlos-granados in [#1463](https://github.com/Behat/Behat/pull/1463)

### Fixed

* Fix enforcing that 32 character (or longer) turnip pattern names are not allowed
  by @ivastly in [#1457](https://github.com/Behat/Behat/pull/1457) and @acoulton in [#1483](https://github.com/Behat/Behat/pull/1483)
* Fix generating the PHAR for releases by upgrading the build tooling by @heiglandreas in [#1462](https://github.com/Behat/Behat/pull/1462)

### Internal

* Improve code readability; use ::class references by @uuf6429 in [#1485](https://github.com/Behat/Behat/pull/1485)
* Fix autoloading unit tests and improve some code style & assertion failure messages by @uuf6429 in [#1427](https://github.com/Behat/Behat/pull/1427),
  [#1486](https://github.com/Behat/Behat/pull/1486) and [#1487](https://github.com/Behat/Behat/pull/1487) and by
  @jrfnl in [#1479](https://github.com/Behat/Behat/pull/1479)
* Add .editorconfig file by @chapeupreto in [#1418](https://github.com/Behat/Behat/pull/1418)
* Updates to github actions workflows @jrfnl in [#1475](https://github.com/Behat/Behat/pull/1475),[#1474](https://github.com/Behat/Behat/pull/1474),
* Update contributing docs and README links by @carlos-granados in [#1489](https://github.com/Behat/Behat/pull/1489) and
  [#1492](https://github.com/Behat/Behat/pull/1492)

## [3.14.0] - 2024-01-10

### Added

* 🎉 Symfony 7 is now supported 🎉 by @dmaicher in [#1442](https://github.com/Behat/Behat/pull/1442)
* PHP 8.3 is now supported (no code changes were required) by @jrfnl in [#1440](https://github.com/Behat/Behat/pull/1440)

### Fixed

* Renamed method parameters to match signatures from interfaces by @ciaranmcnulty in [#1434](https://github.com/Behat/Behat/pull/1434)

### Internal

* CI improvements by @stof in [#1430](https://github.com/Behat/Behat/pull/1430)

## [3.13.0] - 2023-04-18

### Added
* [#1422](https://github.com/Behat/Behat/pull/1422) Add support for displaying PHPUnit 10 exceptions [@mnocon](https://github.com/mnocon)
* [#1429](https://github.com/Behat/Behat/pull/1429) Add more precise types for static analysis [@yguedidi](https://github.com/yguedidi)

## [3.12.0] - 2022-11-29

### Added
* [#1417](https://github.com/Behat/Behat/pull/1417) Allow install with PHP 8.2 [@ciaranmcnulty](https://github.com/ciaranmcnulty)

### Fixed
* [#1412](https://github.com/Behat/Behat/pull/1412) Fix dynamic property deprecation notices in PHP 8.2 [@gquemener](https://github.com/gquemener)
* [#1410](https://github.com/Behat/Behat/pull/1410) Fix deprecation errors in Junit formatter for PHP 8.1 [@albeorte96](https://github.com/albeorte96)

### Other contributions
* [#1415](https://github.com/Behat/Behat/pull/1415) Fix README typo [@vinceAmstoutz](https://github.com/vinceAmstoutz)

## [3.11.0] - 2022-07-07

### Added
* [#1387](https://github.com/Behat/Behat/pull/1387) Added file attribute to Junit output [@ppaulis](https://github.com/ppaulis)
* [#1266](https://github.com/Behat/Behat/pull/1266) Enable env placeholder resolution in config [@mpdude](https://github.com/mpdude)
* [#1380](https://github.com/Behat/Behat/pull/1380) Support psr/container 2.0 [@wouterj](https://github.com/wouterj)
* [#1340](https://github.com/Behat/Behat/pull/1340) Added Chinese language [@54853315](https://github.com/54853315)

### Fixed
* [#1374](https://github.com/Behat/Behat/pull/1374) Fixed counts in hu translations [@Sweetchuck](https://github.com/Sweetchuck)
* [#1393](https://github.com/Behat/Behat/pull/1393) Fixed counts in bg and jo translations [@delyro](https://github.com/delyro)

### Other contributions
* [#1398](https://github.com/Behat/Behat/pull/1398) Fix failing builds due to composer --allow-plugins [@Chekote](https://github.com/Chekote)

## 3.10.0 - 2021-11-02

## What's Changed
* PHP8 Hook attributes by @rpkamp in https://github.com/Behat/Behat/pull/1372

**Full Changelog**: https://github.com/Behat/Behat/compare/v3.9.1...v3.10.0

## 3.9.1 - 2021-11-02

## What's Changed
* Fix issue 1363 (Symfony 6 compatibility) by @dmaicher in https://github.com/Behat/Behat/pull/1368
* update branch alias for dev-master by @dmaicher in https://github.com/Behat/Behat/pull/1369
* Fix SYMFONY_REQUIRE for github action by @dmaicher in https://github.com/Behat/Behat/pull/1370
* Issue #1373 - Replace %1% with %count% in hu translations by @Sweetchuck in https://github.com/Behat/Behat/pull/1374

## New Contributors
* @dmaicher made their first contribution in https://github.com/Behat/Behat/pull/1368
* @Sweetchuck made their first contribution in https://github.com/Behat/Behat/pull/1374

**Full Changelog**: https://github.com/Behat/Behat/compare/v3.9.0...v3.9.1

## [3.9.0] - 2021-10-18

### What's Changed
* Fix syntax help test and bump gherkin dependency by @ciaranmcnulty in https://github.com/Behat/Behat/pull/1336
* Remove legacy Symfony compatibility layers (#1305, #1347) by @simonhammes in https://github.com/Behat/Behat/pull/1349
* Add PHP 8.1 support by @javer in https://github.com/Behat/Behat/pull/1355
* Introduce reading PHP8 Attributes for Given, When and Then steps by @rpkamp in https://github.com/Behat/Behat/pull/1342
* Allow Symfony 6 by @Kocal in https://github.com/Behat/Behat/pull/1346
* Remove minimum-stability dev from composer.json & require Gherkin ^4.9.0 by @pamil in https://github.com/Behat/Behat/pull/1365
* Allow to manually run GitHub Actions by @pamil in https://github.com/Behat/Behat/pull/1361
* Add vimeo/psalm (#1307) by @simonhammes in https://github.com/Behat/Behat/pull/1348

### New Contributors
* @simonhammes made their first contribution in https://github.com/Behat/Behat/pull/1349
* @javer made their first contribution in https://github.com/Behat/Behat/pull/1355
* @Kocal made their first contribution in https://github.com/Behat/Behat/pull/1346

## [3.8.1] - 2020-11-07

### Fixed

 * [1329](https://github.com/Behat/Behat/pull/1329): Regression when using scalar type hints ([@ciaranmcnulty](https://github.com/ciaranmcnulty))

## [3.8.0] - 2020-11-01

### Added
 * [1198](https://github.com/Behat/Behat/pull/1198): Korean language translations ([@getsolaris](https://github.com/getsolaris))
 * [1252](https://github.com/Behat/Behat/pull/1252): Hungarian language translations ([@kolesar-andras](https://github.com/kolesar-andras))
 * [1217](https://github.com/Behat/Behat/pull/1217): Bulgarian language translations ([@toni-kolev](https://github.com/toni-kolev))
 * [1322](https://github.com/Behat/Behat/pull/1322): Feature title as classname in JUnit output ([@steefmin](https://github.com/steefmin))
 * [1313](https://github.com/Behat/Behat/pull/1313): PHP 8 support ([@ciaranmcnulty](https://github.com/ciaranmcnulty))
 * [1313](https://github.com/Behat/Behat/pull/1323): Further PHP 8 support ([@dgafka](https://github.com/dgafka))

### Fixed

 * [#1303](https://github.com/Behat/Behat/pull/1303): Error when running `--debug` with recent Symfony versions ([@jawira](https://github.com/jawira))
 * [#1311](https://github.com/Behat/Behat/pull/1311): Remove symfony deprecation messages about transChoice ([@guilliamxavier](https://github.com/guilliamxavier))
 * [#1318](https://github.com/Behat/Behat/pull/1318): Allow negated filters on scenario hoooks ([@andrewnicols ](https://github.com/andrewnicols))

### Changed
 * [#1299](https://github.com/Behat/Behat/pull/1299): Removed support for PHP <7.2, Symfony <4.4 ([@upamil](https://github.com/pamil))
 * [#1310](https://github.com/Behat/Behat/pull/1310): Refactoring to use newer language features ([@rpkamp](https://github.com/rpkamp))
 * [#1315](https://github.com/Behat/Behat/pull/1315): Remove BC layer for unsuppored symfony dispatcher ([@rpkamp](https://github.com/rpkamp))
 * [#1314](https://github.com/Behat/Behat/pull/1314): Remove BC layer for unsuppored symfony translator ([@rpkamp](https://github.com/rpkamp))
 * [#1212](https://github.com/Behat/Behat/pull/1212): Updated composer description ([@tkotosz](https://github.com/tkotosz))
 * [#1317](https://github.com/Behat/Behat/pull/1317): Use PHPUnit8 for unit testing ([@phil-davis](https://github.com/phil-davis))

## [3.7.0] - 2020-06-03

### Added
  * [#1236](https://github.com/Behat/Behat/pull/1236): Add support for php 7.4 ([@snapshotpl](https://github.com/snapshotpl))

### Fixed
  * [#1270](https://github.com/Behat/Behat/pull/1270): Fix issues with PHP version handling in build ([@Sam-Burns](https://github.com/Sam-Burns))
  * [#1282](https://github.com/Behat/Behat/pull/1282): Updated the year on Changelog dates ([@choult](https://github.com/choult))
  * [#1284](https://github.com/Behat/Behat/pull/1284): Restore PHP 5.3/5.4 compat ([@dvdoug](https://github.com/dvdoug), [@Sam-Burns](https://github.com/Sam-Burns), [@pamil](https://github.com/pamil))

### Changed
  * [#1281](https://github.com/Behat/Behat/pull/1281): Make container-interop/container-interop optional dependency ([@upyx](https://github.com/upyx))

## [3.6.1] - 2020-02-06
### Fixed
  * [#1275](https://github.com/Behat/Behat/pull/1275): fix php 7.1 deprecation for ReflectionType::__toString
  * [#1278](https://github.com/Behat/Behat/pull/1278): Fix fatal when unexpected symfony/event-dispatcher version is installed

## [3.6.0] - 2020-02-04
### Added
  * [#1244](https://github.com/Behat/Behat/pull/1244): Hide internal steps from stack traces in very verbose mode
### Fixed
  * [#1238](https://github.com/Behat/Behat/pull/1238): Don't run Junit output if ext-dom is not present (and suggest in composer)
### Changed
  * [#1256](https://github.com/Behat/Behat/pull/1256): Update dependencies to support Symfony 5.x
  * [#1171](https://github.com/Behat/Behat/pull/1171): Remove symfony/class-loader dependency
  * [#1170](https://github.com/Behat/Behat/pull/1170): Switch to PSR-4 autoloading
  * [#1230](https://github.com/Behat/Behat/pull/1230): PHP 7.3 support
  * [#1230](https://github.com/Behat/Behat/pull/1230): Suggest ext-dom for JUnit support

## [3.5.0] - 2018-08-10
### Added
  * [#1144](https://github.com/Behat/Behat/pull/1144): Allow to use arrays as context parameters
  * [#1081](https://github.com/Behat/Behat/pull/1081): Allow passing null as a named context parameter
  * [#1083](https://github.com/Behat/Behat/pull/1083): Time attribute in JUnit output

### Changed
  * [#1153](https://github.com/Behat/Behat/pull/1153): Cache pattern to regex transformations
  * [#1155](https://github.com/Behat/Behat/pull/1155): Remove composer suggestions

### Fixed
  * Custom container must be public for symfony 4
  * [#1160](https://github.com/Behat/Behat/pull/1160): Register CLI services as synthetic
  * [#1163](https://github.com/Behat/Behat/pull/1163): Allow for new-style symfony serialisation
  * [#1130](https://github.com/Behat/Behat/pull/1130): Fix quoteless definition arguments matching with unicode characters

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

[3.23.0]: https://github.com/Behat/Behat/compare/v3.22.0...v3.23.0
[3.22.0]: https://github.com/Behat/Behat/compare/v3.21.1...v3.22.0
[3.21.1]: https://github.com/Behat/Behat/compare/v3.21.0...v3.21.1
[3.21.0]: https://github.com/Behat/Behat/compare/v3.20.0...v3.21.0
[3.20.0]: https://github.com/Behat/Behat/compare/v3.19.0...v3.20.0
[3.19.0]: https://github.com/Behat/Behat/compare/v3.18.1...v3.19.0
[3.18.1]: https://github.com/Behat/Behat/compare/v3.18.0...v3.18.1
[3.18.0]: https://github.com/Behat/Behat/compare/v3.17.0...v3.18.0
[3.17.0]: https://github.com/Behat/Behat/compare/v3.16.0...v3.17.0
[3.16.1]: https://github.com/Behat/Behat/compare/v3.16.0...v3.16.1
[3.16.0]: https://github.com/Behat/Behat/compare/v3.15.0...v3.16.0
[3.15.0]: https://github.com/Behat/Behat/compare/v3.14.0...v3.15.0
[3.14.0]: https://github.com/Behat/Behat/compare/v3.13.0...v3.14.0
[3.13.0]: https://github.com/Behat/Behat/compare/v3.12.0...v3.13.0
[3.12.0]: https://github.com/Behat/Behat/compare/v3.11.0...v3.12.0
[3.11.0]: https://github.com/Behat/Behat/compare/v3.10.0...v3.11.0
[3.9.0]: https://github.com/Behat/Behat/compare/v3.8.1...v3.9.0
[3.8.1]: https://github.com/Behat/Behat/compare/v3.8.0...v3.8.1
[3.8.0]: https://github.com/Behat/Behat/compare/v3.7.0...v3.8.0
[3.7.0]: https://github.com/Behat/Behat/compare/v3.6.1...v3.7.0
[3.6.1]: https://github.com/Behat/Behat/compare/v3.6.0...v3.6.1
[3.6.0]: https://github.com/Behat/Behat/compare/v3.5.0...v3.6.0
[3.5.0]: https://github.com/Behat/Behat/compare/v3.4.3...v3.5.0
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
