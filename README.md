# Multiple Roles Interface

Contributors: susanwrotethis  
Tags: multiple roles, many roles, more than one role, edit roles, user roles, roles, edit user, multisite  
Requires at least: 3.1  
Tested up to: 4.9.1  
Stable tag: 1.0  

Multiple Roles Interface provides a roles checklist to assign multiple roles to a user.

## Description
WordPress supports multiple roles for users but does not have an interface to manage them. Multiple Roles Interface replaces the Roles dropdown on the user-edit.php page with a list of checkboxes so more than one role may be assigned to a user. Works on multisite.

**Features**

* Works on multisite as well as single installations
* Clean installation: no options added to the database or settings page to manage
* Introduces an optional constant to prevent accidental removal of all roles from a user
* Provides filter hooks for additional customization
* Contains language support

## Installation

1. Upload the plugin folder to your /wp-content/plugins/ directory.
2. Make plugin folder is named multiple-roles-interface.
3. Activate the "Multiple Roles Interface" plugin in your WordPress administration.

## Frequently Asked Questions

**Does this plugin create new roles or edit roles?**

No. There are great plugins already doing this. Some also provide multiple roles support, but when you activate them you place extremely powerful capabilities, such as the ability to add, delete or edit roles, in the hands of all site administrators. Multiple Roles Interface supports the assignment of multiple roles only, limiting the risk to your site. 

**Does this work on multisite?**

Yes, it was originally built for multisite and tested afterwards on single installations. You may network activate the plugin or activate it only on individual sites within the network.

**How do I prevent administrators from accidentally removing all of a user's roles?**

Add the following line to your wp-config.php file:

define('MULTI_ROLES_EDIT_DEFAULT', true);

When set to true, the plugin checks the user's list of roles and assigns the site's default role (probably Subscriber) if the list is empty.

**What if I want the the plugin to assign a different role than the site's default?**

The plugin contains a filter hook, swt_modify_multi_roles_default, so you can override this behavior.

**Are there other filters?**

There are three filters in the plugin. To find out more, visit https://susanwrotethis.com/plugins/multiple-roles-interface/.

## Changelog

### 1.0
* New release
