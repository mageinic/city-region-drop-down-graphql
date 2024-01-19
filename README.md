# City Region Drop-down GraphQL

**City Region Drop-down GraphQL is a part of MageINIC City Region Drop-down extension that adds GraphQL features.** This extension extends City Region Drop-down definitions.

## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageinic/city-region-postcode-graphql

php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento maintenance:disable
php bin/magento cache:flush
```

**Note:**
Magento 2 City Region Drop-down GraphQL requires installing [MageINIC City Region Drop-down](https://github.com/mageinic/City-Region-Drop-Down-GraphQl) in your Magento installation.

**Or Install via composer [Recommend]**
```
composer require mageinic/city-region-postcode-graphql
```

## 2. How to use

- To view the queries that the **MageINIC City Region Drop-down GraphQL** extension supports, you can check `CityRegionPostcode GraphQl User Guide.pdf` Or run `CityRegionPostcodeGraphQl.postman_collection.json` in Postman.

## 3. Get Support

- Feel free to [contact us](https://www.mageinic.com/contact.html) if you have any further questions.
- Like this project, Give us a **Star**
