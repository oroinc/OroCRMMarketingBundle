Marketing-related packaged bundles
======================

Marketing-related package comes with specific bundles intended to integrate marketing features into Oro applications.

## Use as dependency in composer

In order to use the package in your project, it needs to be added as a dependency in the composer.json file:

```yaml
    "require": {
        "oro/marketing": "1.0.*"
    }
```

Until it's a private repository and it's not published on packagist:
```yaml
    "require": {
        "oro/marketing": "dev-master",
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/orocrm/marketing.git",
            "branch": "master"
        }
    ],
```