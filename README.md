# FAU-Elemental

[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/fau-elemental/main?label=Version)](https://github.com/RRZE-Webteam/FAU-Elemental) [![Release Version](https://img.shields.io/github/v/release/rrze-webteam/FAU-Elemental?label=Release+Version)](https://github.com/rrze-webteam/fau-elemental/releases/) [![GitHub License](https://img.shields.io/github/license/rrze-webteam/fau-elemental?label=Lizenz)](https://github.com/RRZE-Webteam/FAU-Elemental/blob/master/LICENSE) [![GitHub issues](https://img.shields.io/github/issues/rrze-webteam/fau-elemental)](https://github.com/RRZE-Webteam/FAU-Elemental/issues)

Allgemeines WordPress-Theme der Friedrich-Alexander-Universität Erlangen-Nürnberg (FAU) ab 2025, https://www.fau.de

## Version

Version: 0.1

## Download 

GitHub-Repo: https://github.com/RRZE-Webteam/FAU-Elemental

## Internationalization (I18n)

This project supports WordPress internationalization.  
To work with translations, make sure **wp-cli is installed and available in your PATH**.

### Workflow

- **Prepare translations**  
  ```bash
  npm run i18n:prepare
  ```  
  Collects all translatable strings, updates the `.pot` file, and refreshes existing `.po` files.

- **Add a new language**  
  Copy `fau-elemental.pot` to `/languages/<locale>.po` (e.g. `languages/de_DE.po`, `languages/de_DE_formal.po`).

- **Translate**  
  Open the `.po` file and fill in the `msgstr ""` lines.  
  Do **not** edit the `.pot` file, comments, or `msgid` entries.

- **Build translations**  
  For local testing, first run:
  ```bash
  npm run build
  ``` 
  Then build translations:
  ```bash
  npm run i18n:build
  ```
  WordPress will automatically load them.  
  On GitHub, translations are built automatically during a **beta merge**.

## Autor 

RRZE-Webteam , http://www.rrze.fau.de

## Copyright

GNU General Public License (GPL) Version 3


## Feedback

Bitte verwenden Sie GitHub um Issues oder Feedback zu geben:
 https://github.com/RRZE-Webteam/FAU-Elemental/issues

Alternativ können Sie auch eine E-Mail senden an: 
 webmaster@rrze.fau.de
