<!-- markdownlint-disable MD013 -->
# PHPLint

:material-web: Visit [Official Project Site](https://github.com/overtrue/phplint)

## Goals

See how to use the `colors` option with either `always` or `never` value to use a Symfony/Console `--ansi` compatible flag.

## Installation

=== ":octicons-command-palette-16: Install Command"

    ```shell
    composer bin phplint update
    ```

=== ":material-text-long: Standard Output"

    > [!NOTE]
    >
    > Generated with Composer 2.9 (and composer-bin-plugin 1.9) on PHP 8.2 runtime

    ```text
    [bamarni-bin] Checking namespace vendor-bin/phplint
    Loading composer repositories with package information
    Updating dependencies
    Lock file operations: 22 installs, 0 updates, 0 removals
      - Locking overtrue/phplint (9.7.1)
      - Locking psr/cache (3.0.0)
      - Locking psr/container (2.0.2)
      - Locking psr/event-dispatcher (1.0.0)
      - Locking psr/log (3.0.2)
      - Locking symfony/cache (v7.4.6)
      - Locking symfony/cache-contracts (v3.6.0)
      - Locking symfony/console (v7.4.6)
      - Locking symfony/deprecation-contracts (v3.6.0)
      - Locking symfony/event-dispatcher (v7.4.4)
      - Locking symfony/event-dispatcher-contracts (v3.6.0)
      - Locking symfony/finder (v7.4.6)
      - Locking symfony/options-resolver (v7.4.0)
      - Locking symfony/polyfill-ctype (v1.33.0)
      - Locking symfony/polyfill-intl-grapheme (v1.33.0)
      - Locking symfony/polyfill-intl-normalizer (v1.33.0)
      - Locking symfony/polyfill-mbstring (v1.33.0)
      - Locking symfony/process (v7.4.5)
      - Locking symfony/service-contracts (v3.6.1)
      - Locking symfony/string (v7.4.6)
      - Locking symfony/var-exporter (v7.4.0)
      - Locking symfony/yaml (v7.4.6)
    Writing lock file
    Installing dependencies from lock file (including require-dev)
    Package operations: 22 installs, 0 updates, 0 removals
      - Downloading symfony/polyfill-ctype (v1.33.0)
      - Downloading symfony/deprecation-contracts (v3.6.0)
      - Downloading symfony/yaml (v7.4.6)
      - Downloading symfony/process (v7.4.5)
      - Downloading symfony/options-resolver (v7.4.0)
      - Downloading symfony/finder (v7.4.6)
      - Downloading psr/event-dispatcher (1.0.0)
      - Downloading symfony/event-dispatcher-contracts (v3.6.0)
      - Downloading symfony/event-dispatcher (v7.4.4)
      - Downloading symfony/polyfill-mbstring (v1.33.0)
      - Downloading symfony/polyfill-intl-normalizer (v1.33.0)
      - Downloading symfony/polyfill-intl-grapheme (v1.33.0)
      - Downloading symfony/string (v7.4.6)
      - Downloading psr/container (2.0.2)
      - Downloading symfony/service-contracts (v3.6.1)
      - Downloading symfony/console (v7.4.6)
      - Downloading symfony/var-exporter (v7.4.0)
      - Downloading psr/cache (3.0.0)
      - Downloading symfony/cache-contracts (v3.6.0)
      - Downloading psr/log (3.0.2)
      - Downloading symfony/cache (v7.4.6)
      - Downloading overtrue/phplint (9.7.1)
      - Installing symfony/polyfill-ctype (v1.33.0): Extracting archive
      - Installing symfony/deprecation-contracts (v3.6.0): Extracting archive
      - Installing symfony/yaml (v7.4.6): Extracting archive
      - Installing symfony/process (v7.4.5): Extracting archive
      - Installing symfony/options-resolver (v7.4.0): Extracting archive
      - Installing symfony/finder (v7.4.6): Extracting archive
      - Installing psr/event-dispatcher (1.0.0): Extracting archive
      - Installing symfony/event-dispatcher-contracts (v3.6.0): Extracting archive
      - Installing symfony/event-dispatcher (v7.4.4): Extracting archive
      - Installing symfony/polyfill-mbstring (v1.33.0): Extracting archive
      - Installing symfony/polyfill-intl-normalizer (v1.33.0): Extracting archive
      - Installing symfony/polyfill-intl-grapheme (v1.33.0): Extracting archive
      - Installing symfony/string (v7.4.6): Extracting archive
      - Installing psr/container (2.0.2): Extracting archive
      - Installing symfony/service-contracts (v3.6.1): Extracting archive
      - Installing symfony/console (v7.4.6): Extracting archive
      - Installing symfony/var-exporter (v7.4.0): Extracting archive
      - Installing psr/cache (3.0.0): Extracting archive
      - Installing symfony/cache-contracts (v3.6.0): Extracting archive
      - Installing psr/log (3.0.2): Extracting archive
      - Installing symfony/cache (v7.4.6): Extracting archive
      - Installing overtrue/phplint (9.7.1): Extracting archive
    Generating autoload files
    18 packages you are using are looking for funding.
    Use the `composer fund` command to find out more!
    No security vulnerability advisories found.
    ```

## Run sample

=== ":octicons-command-palette-16: Test Hook"

    ```shell
    vendor/bin/captainhook hook:pre-commit -c examples/captainhook-phplint-sample.json --verbose
    ```

=== ":octicons-file-code-16: Configuration File"

    ```json hl_lines="13-14 26 32"
    {
        "config": {
            "allow-failure": false,
            "bootstrap": "vendor-bin-autoloader.php",
            "ansi-colors": true,
            "git-directory": "../.git",
            "fail-on-first-error": false,
            "verbosity": "normal",
            "plugins": [
                {
                    "plugin": "\\Bartlett\\CaptainHookBinPlugin\\BinPlugin",
                    "options": {
                        "always-colors-flag": "--ansi",
                        "never-colors-flag": "--no-ansi"
                    }
                }
            ]
        },
        "pre-commit": {
            "enabled": true,
            "actions": [
                {
                    "action": [
                        "vendor/bin/phplint",
                        "src/",
                         "{$ENV|value-of:\\Bartlett\\CaptainHookBinPlugin\\BinPlugin.colors-flag|cache:false}"
                    ],
                    "config": {
                        "label": "Lint Files (with PHPLint)"
                    },
                    "options": {
                        "colors": "always",
                        "package-require": "overtrue/phplint"
                    }
                }
            ]
        }
    }
    ```

    > [!NOTE]
    > Explains about the `captainhook-phplint-sample.json` config file
    >
    > The `{$ENV|value-of:\\Bartlett\\CaptainHookBinPlugin\\BinPlugin.colors-flag|cache:false}` syntax
    > allow to access the plugin config for `*-colors-flag` pre-set choices, matching the `colors` action/option value.
    >
    > - the `always-colors-flag` pre-set is defined with `--ansi`
    > - the `never-colors-flag` pre-set is defined with `--no-ansi`
    >
    > These pre-settings match common options on all PHP CLI tool that implement the `symfony/console` component.

    > [!TIP]
    > If you don't specify the `colors` option, as default value is equal to `auto`, and without `auto-colors-flag` definition (pre-setting),
    > no additional flag is added to your action command.

    > [!IMPORTANT]
    >
    > 1. As CaptainHook does not (yet) delegate the color support (even if `ansi-colors` is set to TRUE), we must tell it on each binary dependency action.
    > 2. Refer to each dependency binary documentation to know what flag is accepted.

=== ":material-text-long: Results"

    ![ansi-colors-flag with PHPLint](../assets/images/ansi-colors-flag.png)
