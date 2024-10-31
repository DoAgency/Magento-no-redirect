Magento 2 Plugin: Add to Wishlist Without Redirect
Overview
This Magento 2 plugin enhances user experience by changing the default wishlist behavior. When users add a product to their wishlist, they can continue browsing without being redirected to a new page. This reduces interaction time and provides a smoother, more intuitive shopping experience.

Features
Adds products to the wishlist without redirecting the user.
Improves overall navigation and reduces disruptions.
Ideal for e-commerce sites focused on a seamless user experience.
Installation
Upload the plugin files: Copy the plugin files to the app/code directory of your Magento installation.

Enable the plugin:
bin/magento module:enable YourCompany_WishlistNoRedirect
bin/magento module:enable DoAgency_WishlistNoRedirect

Run setup and compile:
bin/magento setup:upgrade
bin/magento setup:di:compile

Clear cache:
bin/magento cache:clean

Compatibility
Magento 2.3 and higher.
Usage
Once installed, the plugin will automatically prevent redirection when adding items to the wishlist. No additional configuration is needed.

Support
For questions or issues, please contact info@doagency.it.
