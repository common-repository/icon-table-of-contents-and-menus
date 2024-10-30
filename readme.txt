=== Icon Table of Contents and Menus ===
Contributors: dmlenton
Tags: table of contents, menus, tocs, toc, menu
Requires at least: 3.0.1
Tested up to: 3.7.1
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides an icon at the top of the page which, when expanded, provides a table of contents with bookmarks to all of the main h tags in the content. 

== Description ==

Long documents with multiple headings are sometimes difficult to scan, especially on mobile devices. This plugin helps by providing an icon at the top of the page under the main heading which, when expanded, provides a table of contents (ToC)  with bookmarks to all of the main headings in the content below (h1 to h4). It can also be used to display WordPress menus using the same icon interface.

#### How does it work?

To generate the table of contents for the current page content the plugin parses the current page content into a DOMDocument and then uses DOMXPath to find all of the main heading elements (h1 to h4). It adds bookmarks for each of these headings and then builds a nested unordered list linking to them as a DOM fragment. This gets inserted back into the main DOMDocument object. Finally, the generated DOMDocument is returned as a string for output into the main page content.

The menu version of the plugin doesn't have to parse any content. Instead it just loads in the menu details using the WordPress wp_get_nav_menu_items() function and iterates through them building an unordered list as a string. This is then inserted into a template and displayed on the page.

== Installation ==

Once you have installed and activated the plugin there are two functions available to use in your WordPress templates:

* icon_toc_page()
* icon_toc_navigation(array())

The first of these is used to generate a table of contents (ToC) for your current page. To call it, you need to edit the appropriate template (for example, page.php) and insert the following line just before the WordPress function the_content() is called:

`<?php if (class_exists('Netf_Toc')) { icon_toc_page(); }	?>`

It doesn't take any parameters. This will generate the ToC icon under the main heading on the page. 

If need be, you can amend the look of the ToC by extending the existing toc.css styles. More information can be derived from the main stylesheet, but to change the main colours you just need to add new values for:

.toc {
	background-color: #fafafa;
	border-color:  #ddd;
}

Some colours will work better than others. 


icon_toc_navigation() is used to display one or more of the menus you have set up for your WordPress site. Use the following code, replacing 'menu_1' and 'menu_2' with the names of the WordPress menus you want to display. You can add as many menus as you like to the array. Even if you're only calling one menu it must be passed as an array:

`<?php	
if (class_exists('Netf_Toc')) {
	icon_toc_navigation(array('menu_1','menu_2'));
} 
?>`

This will work anywhere, but the expectation is that it will be called within the header of the page. 

If you're going to put the ToC in the header, you might want to specify a specific position for it and set it to display over other content using an appropriate z-index value. For example:

	header .toc {
		position: absolute;
		top: 0em;
		right: 0.5em; 
		display: block;
		z-index: 1;
	}	

In addition, you'll probably need to amend the CSS of the element within which the TOC is set to display (the containing element) so that it allows content to be displayed outside of its bounding box by setting overflow to visisble. So, for example, if the container element is the header, then:

	header {
		overflow: visible;
	}

You may also want the navigation menu to only appear in certain circumstances, for example when the width of the browser is small (I use it in this way to replace other forms of navigation on a site). To do that, you'll need something like this in the main CSS:
	
	header .toc {
		display: none;
	}
	
And a suitable @media entry:
	
	@media (max-width: 767px) {
		header .toc {
			display: block;
		}	
	}


== Changelog ==

= 1.2 =
* Now uses charset specified in settings for site instead of assuming UTF-8

= 1.1 =
* Improved readme.txt

= 1.0 = 
* First stable release

