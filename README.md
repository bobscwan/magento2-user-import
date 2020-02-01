# Installation

## composer

```
composer config repositories.repo-name vcs https://github.com/bobscwan/magento2-user-import
composer require expertime/import
```

## magento
```
bin/magento module:enable Expertime_Import
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static:deploy en_US -t Magento/luma
bin/magento setup:static:deploy en_US -t Magento/backend
```