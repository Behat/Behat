Behat is an open source, community-driven project. We would love you to contribute to
this awesome project, but remember to follow the few simple rules defined in this
document.

## Submitting an Issue

1. State clearly if it is a feature, a problem or refactoring. You can even use one
of the [GitHub labels](https://github.com/Behat/Behat/labels) we carefully created
just for that purpose. That makes our life easier as we tend to focus on resolving
issues quicker than adding new features
2. Outline clearly in one/two sentences why that feature is important to you or why
that problem causes you grief (and at what scale). This helps us properly prioritise
what we're working on
3. Behat is [automatically tested](https://github.com/Behat/Behat/actions) on every change.
If you have a problem, chances are high it is something very specific to your context
and the more we know about it the more likely we would be able help. At the very least
provide us with enough information about your feature files, context classes and local
environment
4. Make sure you stay professional and do not use offensive language in your issue.
Positive tone in the text of issues is known to reduce lead time to help
5. If [asked for clarification](https://github.com/Behat/Behat/labels/requires%20clarification),
we expect to hear back from you in 7 days. If no answer is given in 7 days, issue will
be automatically closed. You can easily open new issue again later and this rule helps
us reduce the clutter of "silent issues"

## Submitting a Pull Request

1. Make your feature addition, bug fix or refactoring
2. Add a new `Scenario` into [one of the existing features](features) or create a new `.feature` file describing
the changes. Check the [features/](features) folder for examples. This is important so we don't break the
changes you introduced in a future version unintentionally
3. Make sure your changes adhere to the [Backwards Compatibility](#backwards-compatibility) rules. This is important
so that we adhere to [Semantic Versioning v2.0.0](http://semver.org/spec/v2.0.0.html)
5. Do not mess with the [`BehatApplication::VERSION`](src/Behat/Behat/ApplicationFactory.php#L48) version
6. Make sure you [ran the tests](#running-tests) and didn't break anything. That will save some time on
[GitHub actions](https://github.com/Behat/Behat/actions)
7. Commit your code and submit a Pull Request, providing a clear description of the change, including
the motivation for the proposal.

Please note: Each Pull Request should contain only one new feature or bugfix. If the changes are large,
please structure them into multiple smaller commits. If you need to make small refactorings or internal
changes - for example to fix an already-failing test, rename private variables, or reformat an existing
method to make your own change readable - this can be included in the Pull Request but must be
committed separately with its own commit message(s). If possible, make these the first commits on
your branch to make review easier.

## Backwards compatibility

Starting from `v3.0.0`, Behat is following [Semantic Versioning v2.0.0](http://semver.org/spec/v2.0.0.html).
This means that we take backwards compatibility of public API very seriously. So unless you want your PR to start a
new major version of Behat (`v4.0.0` for example), you need to make sure that either you do not change existing
interfaces and their usage across the system or that you at least introduce backwards compatibility layer together with
your change. Not following these rules will cause a rejection of your PR. Exception could be an extremely rare case
where BC break is introduced as a measure to fix a serious issue.

You can read detailed guidance on what BC means in [Symfony2 BC guide](http://symfony.com/doc/current/contributing/code/bc.html).

### Running automated backwards compatibility checks

We use `roave/backward-compatibility-check` in CI to automatically check for BC breaks. Due to dependency conflicts,
this is not required as a composer dependency. Instead, you can:

* Use it as a docker image (see
  [the project docs](https://github.com/Roave/BackwardCompatibilityCheck?tab=readme-ov-file#install-with-docker)) - note
  that at time of writing the docker images do not appear to be up to date with the latest package releases.
* Require it as a standalone tool with composer. You could do this as a `composer global require`, or by
  requiring it into an empty composer.json in any local directory. Note that the tool requires `git` to be installed as
  well as number of PHP extensions.

The `check-bc` job in our [GitHub actions workflow](.github/workflows/build.yml) provides an example of installing and
running the tool in a standalone local directory for linux-based systems.

After installation, switch back to your Behat directory (if required) and run the docker container / global / absolute
path to `vendor/bin/roave-backward-compatibility-check`

**NOTE:** the tool will only detect / report local changes that you have already committed.

## Running tests

Make sure that you don't break anything with your changes by running the test
suite with this command:

```bash
composer all-tests
```

This will run all the Behat tests, all the PHPUnit tests and Psalm. If the tests find any issues,
you can run these tools individually by running `composer behat`, `composer phpunit` or `composer psalm`.
