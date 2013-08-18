Contributing
------------

Behat is an open source, community-driven project. If you'd like to contribute, feel free to do this, but remember to follow this few simple rules:

- Make your feature addition or bug fix,
- __Always__ as base for your changes use `2.5` branch (all 2.5 development happens here, `master` branch is for releases & hotfixes only),
- Add `*.features` for those changes (please look into `features/` folder for some examples). This is important so we don't break it in a future version unintentionally,
- Commit your code, but do not mess with `BehatApplication` version, or `CHANGES.md` one,
- __Remember__: when you create Pull Request, always select `2.5` branch as target, otherwise it will be closed.

Contributing to Formatter Translations
--------------------------------------

Almost any output message (except exceptions and custom output) printed by Behat
formatters could be translated into your language with `--lang` option. In order
to fix/add translation, edit the appropriate section of the `i18n.php` file.

Running tests
-------------

Make sure that you don't break anything with your changes by running:

```bash
$> bin/behat
```
