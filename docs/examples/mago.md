<!-- markdownlint-disable MD013 -->
# Mago

:material-web: Visit [Official Project Site](https://github.com/carthage-software/mago)

## Goals

See how to use the `colors` option with either `auto`, `always` or `never` value.

## Installation

=== ":octicons-command-palette-16: Install Command"

    ```shell
    composer bin mago update
    ```

=== ":material-text-long: Standard Output"

    > [!NOTE]
    >
    > Generated with Composer 2.9 (and composer-bin-plugin 1.9) on PHP 8.1 runtime

    ```text
    [bamarni-bin] Checking namespace vendor-bin/mago
    Loading composer repositories with package information
    Updating dependencies
    Lock file operations: 1 install, 0 updates, 0 removals
      - Locking carthage-software/mago (1.19.0)
    Writing lock file
    Installing dependencies from lock file (including require-dev)
    Package operations: 1 install, 0 updates, 0 removals
      - Downloading carthage-software/mago (1.19.0)
      - Installing carthage-software/mago (1.19.0): Extracting archive
        Skipped installation of bin composer/bin/mago for package carthage-software/mago: name conflicts with an existing file
    Generating autoload files
    1 package you are using is looking for funding.
    Use the `composer fund` command to find out more!
    No security vulnerability advisories found.
    ```

## Run sample

=== ":octicons-command-palette-16: Test Hook"

    ```shell
    vendor/bin/captainhook hook:pre-commit -c examples/captainhook-mago-sample.json --verbose
    ```

=== ":octicons-file-code-16: Configuration File"

    ```json hl_lines="13-15 26 33"
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
                        "auto-colors-flag": "--colors=auto",
                        "always-colors-flag": "--colors=always",
                        "never-colors-flag": "--colors=never"
                    }
                }
            ]
        },
        "pre-commit": {
            "enabled": true,
            "actions": [
                {
                    "action": [
                        "vendor/bin/mago",
                        "{$ENV|value-of:\\Bartlett\\CaptainHookBinPlugin\\BinPlugin.colors-flag|cache:false}",
                        "lint"
                    ],
                    "config": {
                        "label": "Lint Files (with Mago)"
                    },
                    "options": {
                        "colors": "always",
                        "package-require": "carthage-software/mago"
                    }
                }
            ]
        }
    }
    ```

    > [!NOTE]
    > Explains about the `captainhook-mago-sample.json` config file
    >
    > The `{$ENV|value-of:\\Bartlett\\CaptainHookBinPlugin\\BinPlugin.colors-flag|cache:false}` syntax
    > allow to access the plugin config for `colors` pre-sets choice:
    >
    > - the `colors` option definition should be `auto` to match `auto-colors-flag` pre-setting
    > - the `colors` option definition should be `always` to match `always-colors-flag` pre-setting
    > - the `colors` option definition should be `never` to match `never-colors-flag` pre-setting

    > [!IMPORTANT]
    >
    > 1. As CaptainHook does not (yet) delegate the color support (even if `ansi-colors` is set to TRUE),
    >    we must tell it on each binary dependency action.
    > 2. Refer to each dependency binary documentation to know what flag syntax is accepted.

=== ":material-text-long: Results"

    The color support is not propagated to each binary dependency action (when `--colors=auto`)

    ![auto-colors-flag with Mago](../assets/images/auto-colors-flag.png)

    So we should specify it explicitly (with `--colors=always`)

    ![always-colors-flag with Mago](../assets/images/always-colors-flag.png)
