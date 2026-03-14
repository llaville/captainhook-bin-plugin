<!-- markdownlint-disable MD013 -->
# About this Plugin

Is it not annoying to see the execution of an action failed, simply because you forgot to install the corresponding package ?

With this plugin, you don't have to think about it :

- if the package is not installed, action is simply skipped
- if the package is installed (and match version constraint), action is executed

And more than just and if/else execution strategy, this plugin allow to avoid hard-coding arguments in your `action` definition.

Learn more with the [tutorials section](learn/README.md)

> [!CAUTION]
>
> Requires [CaptainHook 5.28.4](https://github.com/captainhook-git/captainhook/releases/tag/5.28.4) or greater.
