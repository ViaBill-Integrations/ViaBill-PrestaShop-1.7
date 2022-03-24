# Changelog
- All notable changes to this project will be documented in this file.
- See [changelog structure](https://keepachangelog.com/en/0.3.0/) for more information of how to write perfect changelog.

## Release note
- Make sure what version is required for the client. Is it production or testing
- Make sure why developing, set DISABLE_CACHE to true in order for dependency injection loaded containers would change.
  Otherwise, they are in immutable state.
- When developing, set DEV_MODE to true in order for test BASE_URL and Register/Login
- When providing the zip , make sure there are no .git or var folder
- Make sure to create git tags when adding new version. Use git tag -a vx.x.x commit-hash -m 'release of vx.x.x'
- Install vendors using composer install --no-dev --optimize-autoloader


## [1.0.0] - 2018-07-05

### Changed
- enabled production version by default
- cached dependency injection container

## [1.0.1] - 2018-07-10

### Changed
- removed authentication button from modules tab in BO
- divided BO "Refund confirmation message" setting for single and bulk actions

## [1.0.2] - 2018-07-19

### Changed
- fixed PrestaShop validation errors
- added license comments and file
- added index.php files
- added functionality that deletes authentications tab when logged in instead of css hide

## [1.0.3] - 2018-07-20

### Changed
- raised composer.json version to 5.4
- fixed get Norway Iso exceptions error - constant with array changed to static function
- fixed description spacing

## [1.0.4] - 2018-07-20

### Changed
- disabled cache and enabled live environment

## [1.0.5] - 2018-08-07

### Changed
- changed test mode MyViaBill button URL
- added 'ViaBill Test Mode' setting that allows to switch between test and live modes

## [1.0.6] - 2018-08-09

### Changed
- fixed settings controller setMedia error
- added redirect to authentication page after test mode setting switch

## [1.0.7] - 2018-08-20

### Changed
- added error when countries is failed to load in registration

## [1.0.8] - 2018-09-10

### Changed
- added terms and conditions button in registration
- added pending order state

## [1.0.9] - 2018-09-21

### Changed
- added additional field 'affiliate' in registration request payload
- added target blank to Go To My ViaBill button
- made request o ViaBill API to get auto login link to MyViaBill

## [1.1.0] - 2018-10-01

### Changed
- added DEV_MODE setting to keep test BASE_URL and Register/Login
- removed relog function from production mode

## [1.1.1] - 2018-11-07

### Changed
- added setting in BO to chose if display ViaBill logo in checkout payment selection step
- fixed Go To MyViaBill button error, not it not display field if it's empty
- now Go To MyViaBill button error will be shown ass warning to stop preventing settings changes

## [1.1.2] - 2018-11-14

### Changed
- fixed capture/refund issue on prices with comma as thousands separator
- added missing Da/No module translations

## [1.1.3] - 2019-01-07

### Changed
- added terms and conditions for US
- added price tag for US
- payment service now accepting US locale
- fixed PS1.7.5+ services file issue

### Changed
## [1.1.4] - 2019-03-06

### Changed
- added different payment logo for US currency

## [1.1.5] - 2019-07-01

### Changed
- removed different payment logo for US currency
- fixed ViaBill transaction not appearing on customer account issue
- added auto full capture functionality when the status of the order is changed to "Payment completed by ViaBill"
- disallowed merchants to change order status if order status is "Payment pending by ViaBill"

### Changed
## [1.1.6] - 2019-09-27
- fixed issue when module was installed from dashboard admin 'tabs' weren't installed.

### Changed
## [1.1.7] - 2019-11-04
- improved API request exceptions caching functionality to prevent page breaks when request fails

### Changed
## [1.1.8] - 2020-01-21
- added auto-capture and order statuses multiselect for auto-capture settings in module BO setting tab.
- changed order status hook logic to capture orders with auto-capture multiselect setting statuses in module BO instead
    of hardcoded "Payment completed by ViaBill" status.
- refactored Terms&Conditions link. Now link is made from country code taken from API locales instead of hardcoded.  
    In this way no extra work will be needed in future for T&C link.
    
## [1.1.9] - 2020-01-31
- recreated auto-capture when order status is set to "Payment completed by ViaBill".
- added data-country-code tag in priceTag.
- Added Spanish translations
    
### Changed
## [1.1.10] - 2020-02-21
- added functionality that changes order status to "Payment cancelled by ViaBill" when "cancelled" or "rejected" callback is received. Order status change by hand is still not allowed when status is pending.
    
### Changed
## [1.1.11] - 2020-02-21
- fixed order status revert issue when ViaBill callback is setting order to accepted state and auto-capture is enabled on accepted state.

### Changed
## [1.1.12] - 2020-08-05
- Changed capture amount from float to string when sending capture request via api.

### Changed
## [1.1.13] - 2020-12-14
- getContainer() function changed to gotModuleContainer() for prestashop 1.7.7 compatibility 
- added cart duplication functionality to duplicate customers cart when canceling the order.

### Changed
## [1.1.14] - 2020-12-23
- Viabill order status change validation now works only in back-office

### Changed
## [1.1.15] - 2020-12-28
- Viabill module compatibility with prestashop 1.7.7 
  - Bootstrap backoffice order template upgrade to bootstrap 4
  - As from new PS version some parameters are gone, which caused wrong redirects after any form submit in new AdminOrders page fixed
  - AdminOrdersController doesn't support legacy grids, so new functionality added GridDefinitions to display actions and bulk actions in Admin order list page
  - PS1.7.7 doesn't support overrides on Symfony migrated pages, so new controller and service added for all module functions in order view page

### Changed
## [1.1.16] - 2021-04-19
- Bug fixes & code refactoring
- Improved logging capabilities
- Built-in contact form for technical support
- Troubleshooting section

### Changed
## [1.1.17] - 2021-07-01
- Added customer info to the checkout API request
    
### Changed
## [1.1.18] - 2021-07-08
- Send an email message for order confirmation only after a successful payment

### Changed
## [1.1.19] - 2021-07-22
- Improved order confirmation functionality for older Prestashop versions

### Changed
## [1.1.22] - 2021-08-04
- Display a warning when there is a conflict with third party payment gateways

### Changed
## [1.1.23] - 2021-08-09
- Added platform info to the notifications request

### Changed
## [1.1.24] - 2021-10-04
- Add filter the js script

### Changed
## [1.1.25] - 2022-01-11
- Add customer info during checkout

### Changed
## [1.1.26] - 2022-03-24
- New checkout Viabill logo