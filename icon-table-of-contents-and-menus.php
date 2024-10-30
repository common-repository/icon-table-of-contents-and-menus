<?php
/*
Plugin Name: Icon Table of Contents and Menus
Plugin URI: http://www.davidlenton.co.uk/plugins/icon-table-of-contents-and-menus
Description: A WordPress plugin for adding an expandable table of contents (toc)
Version: 1.2
Author: David Lenton
Author URI: http://www.davidlenton.co.uk
License: GPL2
*/
/*
Copyright 2013 David Lenton  (email : info@davidlenton.co.uk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('Netf_Toc')) {

    class Netf_Toc {

        function __construct() {
			add_action( 'wp_enqueue_scripts', array($this, 'netf_toc_scripts' ));
        }

		public function toc_page_filter(){
			add_filter( 'the_content', array($this,'netf_add_toc') );
		}
		
		public function activate(){}
		public function deactivate(){}

		public function netf_toc_scripts() {
			wp_enqueue_style( 'netf_toc_styles',
				plugins_url( '/styles/toc.css', __FILE__ ) );
			wp_enqueue_script( 'netf_toc_script',
				plugins_url('/js/jquery.toc.js',__FILE__),
				array( 'jquery' )
			);
		}
		
		public function display_navs($menus=null) {
			if (is_array($menus)){
				$netf_toc_output = "";
				foreach($menus as $menu){
					$netf_toc_output .= "<li>" . ucfirst($menu).
					"<ul>";
					$args = array('orderby'=>'title');
					$items = wp_get_nav_menu_items($menu, $args);
					foreach($items As $item) {
						$netf_toc_output .= "<li><a href=\"".$item->url."\">".$item->title."</a></li>";
					}
					$netf_toc_output .= "</ul>
					</li>";
					
				}
				$style_directory = get_stylesheet_directory_uri();
				include(sprintf("%s/templates/toc_nav.php", dirname(__FILE__)));
			}
		}
 
	 	function get_content_as_string($node) {   
			$str = "";
			if ($node) {
				if ($node->nodeName=="script"||$node->nodeName=="style"||$node->nodeName=="object"||$node->nodeName=="embed"||$node->nodeName=="canvas") $str .= $node->nodeValue;	
				if ($node->childNodes) {
					foreach ($node->childNodes as $cnode) {
						if ($cnode->nodeType==XML_TEXT_NODE) {
							$str .= $cnode->nodeValue;
						}
						else if ($cnode->nodeType==XML_ELEMENT_NODE) {
							$str .= "<" . $cnode->nodeName;
							if ($attribnodes=$cnode->attributes) {
								$str .= " ";
								foreach ($attribnodes as $anode) {
									if ($anode) {
									$nodeName = $anode->nodeName;
									$nodeValue = $anode->nodeValue;
									$str .= $nodeName . "=\"" . $nodeValue . "\" ";
									}
								}
							}   
							$nodeText = $this->get_content_as_string($cnode);
							if (empty($nodeText) && !$attribnodes)
								$str .= " />";        // unary
							else
								$str .= ">" . $nodeText . "</" . $cnode->nodeName . ">";
						}
					}
					// A bit of cleanup
					$str = preg_replace("/\s>/si",">",$str);
					$str = preg_replace("/\><\/input>/is","/>",$str);
					$str = preg_replace("/<\/img>/is","",$str);
					return preg_replace("/<br><\/br>/is","<br>",$str);
				}
			}
		}

	function netf_add_toc($content,$return_content=true) {
		// We need to create a valid HTML document so that the charset is correct when
		// being parsed by DOMDocument
		$charset = get_bloginfo( 'charset' );
		$html = "<!DOCTYPE HTML><html><head><meta charset=\"$charset\" /><meta http-equiv=\"content-type\"
	          content=\"text/html; charset=$charset\"></head><body>".$content."</body></html>";
		$dom = new DOMDocument('1.0',$charset);
		$dom->validateOnParse = true;
		if (@$dom->loadHTML($html)){
			$xpath = new DOMXPath($dom);
			$last = 1;
			// Create a document fragment to hold the new TOC content
			$frag = $dom->createDocumentFragment();
			// Start constructing the TOC HTML
			$div = $dom->createElement('div');
			$div->setAttribute('class','toc');
			$wrapper_div = $dom->createElement('div');
			$wrapper_div->setAttribute('class','toc-wrapper');
			$p_tag = $dom->createElement('p');
			$p_tag->setAttribute('class','toc-heading');
			$span_tag = $dom->createElement('span','Jump to');
			$span_tag->setAttribute('style','display:none;');
			$p_tag->appendChild($span_tag);
			$wrapper_div->appendChild($p_tag);
			$div->appendChild($wrapper_div);
			$frag->appendChild($div);
			// Create initial list for the TOC elements
			$ul = $dom->createElement('ul');
			$ul->setAttribute('style','display: none;');
			$wrapper_div->appendChild($ul);
			$head = &$wrapper_div->childNodes->item(1);
			// Thank you http://stackoverflow.com/a/4912798		
			// get all H1, H2, …, H4 elements - we don't want to go too deep
			foreach ($xpath->query('//*[self::h1 or self::h2 or self::h3 or self::h4]') as $headline) {
			    // get level of current headline
			    sscanf($headline->tagName, 'h%u', $curr);
				if ($curr < $last) {
				    // move upwards
				    for ($i=$curr; $i<$last; $i++) {
				        $head = &$head->parentNode->parentNode;
				    }
				} else if ($curr > $last && $head->lastChild) {
				    // move downwards and create new lists
				    for ($i=$last; $i<$curr; $i++) {
				        $head->lastChild->appendChild($dom->createElement('ul'));
				        $head = &$head->lastChild->lastChild;
				    }
				}
				$last = $curr;		    
			    // add list item
			    $li = $dom->createElement('li');
			    $head->appendChild($li);
			    $a = $dom->createElement('a', $headline->textContent);
			    $head->lastChild->appendChild($a);
			    // build ID
			    $levels = array();
			    $tmp = &$head;
			    // walk subtree up to fragment root node of this subtree
			    while (!is_null($tmp) && $tmp != $frag) {
			        $levels[] = $tmp->childNodes->length;
			        $tmp = &$tmp->parentNode->parentNode;
			    }
			    $id = 'section'.implode('.', array_reverse($levels));
			    // set destination
			    $a->setAttribute('href', '#'.$id);
			    // add anchor to headline
			    $a = $dom->createElement('a');
			    $a->setAttribute('name', $id);
			    $a->setAttribute('id', $id);
			    $headline->insertBefore($a, $headline->firstChild);
			}
			if ($return_content){
				// Append fragment with TOC to document
				if($head->childNodes->length>0) $dom->getElementsByTagName('body')->item(0)->insertBefore($frag,$dom->getElementsByTagName('body')->item(0)->firstChild);
			}
			else {
				return $this->get_content_as_string($frag);
			}
		}
		// Get the HTML DOM content as a string
		return $this->get_content_as_string($dom->getElementsByTagName('body')->item(0));
	}

  }

}

if(class_exists('Netf_Toc')){
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('Netf_Toc', 'activate'));
    register_deactivation_hook(__FILE__, array('Netf_Toc', 'deactivate'));
    // Initiate object
	$netf_toc = new Netf_Toc();
	function icon_toc_page(){
		global $netf_toc;
		$netf_toc->toc_page_filter();
	}
	function icon_toc_navigation($menus){
		global $netf_toc;
		$netf_toc->display_navs($menus);
	}	
}

?>