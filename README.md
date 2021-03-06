# Multiple Roles Interface

Multiple Roles Interface provides a roles checklist to assign multiple roles to a user.

## Description

WordPress supports multiple roles for users but does not have an interface to manage them. Multiple Roles Interface replaces the Roles dropdown on the user-edit.php and user-new.php pages with a list of checkboxes so more than one role may be assigned to a user. Works on multisite. one role may be assigned to a user. Works on multisite.

**Features**

* Works on multisite as well as single installations
* Clean installation: no options added to the database or settings page to manage
* Introduces an optional constant to prevent accidental removal of all roles from a user
* Provides two filter hooks for additional customization
* Contains language support

## Installation

Download the current release of this plugin as a zip file. Make sure the file is named multiple-roles-interface.zip.

* In the WordPress admin, go to Plugins > Add New. On multisite, this is under the network admin.
* Click the Upload Plugin button at the top of the page and browse for the zip file.
* Upload the zip file.
* Once the plugin is installed, activate it. On multisite, this can be network activated or activated on individual sites.

## Failsafe Constant

WP core's role select field ensures that a role will always be assigned when a user is added or edited. When replaced with multiple checkboxes, it is possible to assign no role to a user.

If you don't want this to happen, you can prevent it by defining a  failsafe constant. Add the following line to your wp-config.php file:

define( 'SWT_MRI_ADD_FAILSAFE_ROLE', true );

When set to true, the plugin checks the user's list of roles and assigns the site's default role (probably Subscriber) if the list is empty.

## Filter Hooks

This plugin has two filter hooks:

The **swt_mri_list_multi_roles** filter in the swt_mri_generate_form_field() function may be used to sort the order of roles as they appear in the checkbox list on the user-edit.php and user-new.php pages.

The **swt_mri_modify_multi_roles_default** filter in the swt_mri_set_roles() function provides the ability to override the default role that is assigned to a user by the system if no role is assigned when the user is created or edited. This filter works only when the failsafe constant is set to true.

## Frequently Asked Questions

**Does this plugin create new roles or edit roles?**

No. There are great plugins already doing this. Some also provide multiple roles support, but when you activate them you place extremely powerful capabilities, such as the ability to add, delete or edit roles, in the hands of all site administrators. Multiple Roles Interface supports the assignment of multiple roles only, limiting the risk to your site. 

**Does this work on multisite?**

Yes, it was originally built for multisite and tested afterwards on single installations. You may network activate the plugin or activate it only on individual sites within the network.

**What happened to the Change Roles dropdown on the Users page?**

As of version 2.0 of this plugin, that dropdown is removed with a line of JavaScript. The field, and the underlying function, allow only one role per user.

**When I add or edit a user, I'm seeing both the original select field and the list of roles. Is the plugin broken? **

Because there is no filter in the WordPress users admin pages to tailor these fields, the plugin uses a few lines of JavaScript to remove the select field. If you have JavaScript disabled, another plugin has a JavaScript error or there is an issue with the version of JQuery on your site, it may break the JavaScript in this plugin. In most cases the plugin will continue to function properly even if the original select field is displayed. The role(s) assigned by the checkboxes will override whatever value is set in the select field.

**Is there a way to prevent users from assigning redundant roles such as making them an Author when they're already an Editor?**

Not at this time. A future version of this plugin may contain the ability to define mutually-exclusive roles.

## Changelog

### 1.0

* New release

### 2.0

* Refactor all code.
* Add the ability to assign multiple roles when a user is created or an existing multisite user is added to a blog.
* Remove the Change Roles select from users.php.
* Add a translations template file.
* Add index files to prevent directory browsing.
* Remove one filter.
* Updated the uninstall file.

### 2.1

* Continue to tighten up code in admin.php.
* Minify user-new.js file and remove unused code.
* Update the language template.
* Update the documentation in README.md.