# NextCloud4WHMCS
Provisionning Module for NextCloud

Description:
This module is intended to create/suspend/unsuspend/terminate accounts on NextCloud using the REST API.
It can also, setup the NextCloud client password, add the client to a Group and get some stats.

Disclaimer:
You are free to use and modify this module at your will (even for enterprise use). Just keep the credits to me and add yours.
It's not perfect, feel free to provide some feedback.

Requirements:
- A full working WHMCS setup.
- A full working NextCloud instance with a account with enough priviledges to create/manage users and quotas.

Installation:
- Download the zip file and unzip it into your WHMCS/modules (the path should be WHMCS/modules/nextcloud).
- Go to your WHMCS Admin Panel -> System Settings -> Servers -> Add New Server.
- Fill the data:
  - On the Name: Put whatever you want the server to be called.
  - On the Hostname: Put the NextCloud DNS hostname.
  - On the Module: Select "NextCloud Provisioning Module".
  - On the Username: Put the username of the NextCloud account (see requirements).
  - On the Password: Put the password of the NextCloud account (see requirements).
  - Tick "Secure" if your server is on HTTPS, don't tick it if it's only on HTTP.
  - Click "Save".
- Go to System Settings -> Configurable Options -> Create New Group.
- Give a Group name and a Description of your choice.
- Add a New Configurable Option with the following data:
  - Option Name: quota (you can use the pipe to add a description ex: quota|Quota in Gb).
  - Option Type: Quantity.
  - Minimum Quantity Required: The minimum quantity of Gb you wanna sell.
  - Maximum Allowed: The maximum quantity of Gb you wanna sell.
  - Options: 1.
  - Prices: set your own prices.
  - Click "Save Changes".
- Go to System Settings -> Products and Services -> Create a New Product
- Provide a Name and description for your product (ex: NextCloud)
- Untick "Require Domain".
- Set your pricing on the Pricing page.
- On the module page, select "NextCloud Provisioning Module".
- Create 2 Custom fields:
  - 1st Field:
    - Name: nextcloud_group (you can also use pipe to beautify it. ex: nextcloud_group|Team Group).
    - Type: Textbox.
    - Ticks: Admin Only (Unless you want your clients to add themselves to administrator group of NextCloud).
  - 2nd Field:
    - Name: language (you can also use pipe to beautify it: ex: language|Nextcloud Interface Language).
    - Type: Dropdown.
    - Select Options: en|English,fr|Français,pt_PT|Português (add any languages you want that are also available on NextCloud. Check NextCloud documentation)
    - Ticks: Show on order form.
 - On Configurable Options select the group we created previously.
 - Save and start selling NextCloud.
