# Rollbar for WordPress #

**Rollbar for WordPress** is an easy-to-use WordPress plugin to send warnings, notices and errors to [Rollbar](http://rollbar.com/).
With this plug-in, both your PHP and JavaScript errors will be transmitted to Rollbar (disabling JavaScript logging is optional).

## Basic Usage ##

Getting started using Rollbar for WordPress is a quick three-step process:

1. At the **Plugins** configuration page, activate the **Rollbar for WordPress** plug-in.

2. Next, within the Settings menu, click on the new menu option called **Rollbar** to view the plug-in's settings page. 

3. Optionally enter a string in the **Environment** field that pertains to the environment your warnings and errors should be logged 
against.

Click **Save Changes**. You're done!


## Upcoming Features ##

* Nothing planned; if you've got an idea, contribute it as an GitHub Issue!


## Tips & Tricks ##

### Hardcoding Your Environment Name Into A Settings File ###

If you've got a sizable development team working on a WordPress project and a number of them are using Rollbar to debug issues, or 
you've got a development, staging and production environment that you regularly sync data across, you'll eventually run into a 
problem where you find out days or weeks later that a number of your environments have been reporting to Rollbar under the same 
name. 

The easiest way to avoid this is to place a definition for the constant **ROLLBAR_THIS_ENVIRONMENT** in your wp-config settings 
file:

	define('ROLLBAR_THIS_ENVIRONMENT', 'local-ssyed');

Once set, Rollbar for WordPress will always use this value regardless of the value set in the database.