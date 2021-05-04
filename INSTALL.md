# ViaBill - seamless financing! 
## Module for PrestaShop 1.7

# Prerequisites

-  A compatible PrestaShop version. Note that this module is compatible with PrestaShop 1.7.3 or newer, but there are other ViaBill modules that are compatible with Prestashop 1.5 and PrestaShop 1.6.
-  As with _all_ PrestaShop modules, it is highly recommended to backup your site before installation and to install and test on a staging environment prior to production deployments.

# Installation

Before you start the installation of the module, make sure you meet the requirements set by your PrestaShop version. 

## Manual Installation

In order to install manually the module you need to download and copy the `viabill` folder from this repository and copy it under the PrestaShop site as:

`{Prestashop root directory}`/modules/viabill 

Then, you need to login in the backend of the PrestaShop site and navigate to menu Modules → Module. Search for the ViaBill module, under the `Payment` section and click on the the “Configure” button to start the configuration process.

## Installation via Marketplace

Please follow these instructions to install the module through the PrestaShop Add-ons Marketplace.

# Configuration

From the PrestaShop backend navigate to Modules -> Modules Manager and search for the ViaBill module under the Payment section. Click on the “Configure” button to start the configuration process.

## New or Existing User

Before configuring the module, you need to create a new ViaBill account or sign in, if you already have an existing one.

## Module Configuration

Once you have created successfully your ViaBill account, or login into your existing one, you will be able to configure the payment module. Please pay attention to the following settings:

| Parameter | Purpose |
| ------ | ------ |
| Enable on Product page | Show the ViaBill's Price Tags on the product page |
| Enable on Cart Summary | Show the ViaBill's Price Tags on the shopping cart |
| Enable on Payment selection | Show the ViaBill's Price Tags on the checkout page |
| ViaBill Test Mode | If this parameter is set to “Yes”, no actual payment is made, therefore orders should not be shipped. Once you are ready to use ViaBill with real customers it's important to set this parameter to “No”. |
| Enable Debug | This parameter is useful if something is not working as expected and it can provide valuable information to the tech support team. |

# Upgrade Module

## Manual Upgrade

This method describes how to upgrade the module manually, without any references to the PrestaShop's marketplace. 

a) Make a backup of the following folder:
`{Prestashop root directory}`/modules/viabill 
This is helpful in case something goes wrong and you want to restore the latest working version.
b) Overwrite the files of the folder above using the latest version.
c) Login in the backend of the Prestashop site and navigate to menu Modules → Module → Viabill → Upgrade. 
Note that if the button's label is still “Configure” instead of “Upgrade” it means that you haven't copied the new files to the correct folder (`{Prestashop root directory}`/modules/viabill) or the version of the module is not set properly. If you see a “success” message after the upgrade action, refresh the page so the button becomes again “Configure”. 
If not, click on the “Upgrade” button once again.

## Marketplace Upgrade

If you have installed the ViaBill module via the PrestaShop's Marketplace, every time a new version is available you will receive a notification and you will have the option to upgrade the module by simply clicking on the “Upgrade” button.

# Disable Module

If you wish disable the ViaBill module without uninstall it, you can simply go to the module configuration page by navigating to Modules →  Module Manager →  Payment section and click on the downward arrow right next to the “Configure” button for the ViaBill module. Select the Disable option.

# Uninstall Module

The proper way to uninstall the Viabill Payment module is by login in the backend of the Prestashop site and then navigate to menu Modules → Module Manager → Viabill. Right next to the “Configure” button you will find a downward arrow. Click on it to see all available options and then click on the “Uninstall” link.

# Troubleshooting and Support

## ViaBill Module Support

If you are experiencing any technical issues, please navigate to Modules -> Module Manager -> Payment section and click on the ViaBill Module's “Configure” button. Set the Enable Debug to Yes and then try to replicate your issue by repeating the action which caused it. Finally, click on the “Contact” tab that you will find at the top of the ViaBill Module configutation page. Fill out the form and submit it to our technical support team. This contact form is auto-populated with vital information that will help us to resolve your issue faster.

Alternatively, contact us via email at tech@viabill.com.





