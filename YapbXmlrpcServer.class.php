<?php

	/**
	 * Class YapbXmlrpcServer
	 **/

	/*	Copyright 2008 J.P.Jarolim (email : yapb@johannes.jarolim.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*/

	class YapbXmlrpcServer {

		/**
		 * Constructor
		 **/
		function YapbXmlrpcServer() {

			add_filter('yapb_options', array(&$this, 'filterYapbOptions'));
			add_filter('xmlrpc_methods', array(&$this, 'filterXmlrpcMethods'));

		}

		/**
		 * Method acts as action called by YAPB after its initialization
		 *
		 * @param Yapb $yapb
		 **/
		function filterYapbOptions(&$options) {

			// Add Sidebar Options to the YAPB Options page

			$additionalOptions = new YapbOptionGroup(
				'YAPB XMLRPC Server',
				'',
				array(
					new YapbOptionGroup(
						__('Security Settings', 'yapb-xmlrpc-server'), 
						'To activate the YAPB XML-RPC Server functionality, an nonempty API key has to be set.<br/>The API key ensures that noone is able to access this service without knowledge of the key.',
						array(
							new YapbInputOption('yapb_xmlrpc_apikey', __('Your current API key: #20', 'yapb-xmlrpc-server'), $this->getRandomApiKey())
						)
					)
				)
			);

			$additionalOptions->isPlugin = true;	// These are Plugin Options
			$additionalOptions->setLevel(1);		// These are suboptions that we attach to the YAPB Main Options
			$additionalOptions->initialize();		// Initialize the options
			$options->add($additionalOptions);

			return $options;

		}

		/**
		 * This filter hook adds an additional method to the list
		 * of existing YAPB XML RPC methods.
		 * Hook may be found in /blogdir/xmlrpc.php
		 *
		 * @param array $methods
		 **/
		function filterXmlrpcMethods(&$methods) {

			// The XMLRPC functionality will be registered only
			// if an non-empty apikey was set before
			
			$apikey = get_option('yapb_xmlrpc_apikey');
			if (!empty($apikey)) {

				// Add new XMLRPC call "yapb.request"
				$methods['yapb.getImages'] = array(&$this, 'onXmlRpcYapbGetImages');

			}
			
			return $methods;

		}

		/**
		 * Method acts as little controller for the new
		 * XMLRPC call "yapb.request". This little abstraction
		 * ensures max flexibility if we want to add 
		 * additional functionality later on.
		 *
		 * @param array $args
		 **/
		function onXmlRpcYapbGetImages($args) {

			// Just act on a valid apikey
			if ($this->isValidApiKey($args['apikey'])) {

				// Get the requested order (latest|random)

				$order = array_key_exists('order', $args)
					? $args['order']
					: 'latest'; 

				// Get the count of images requested

				$count = array_key_exists('count', $args)
					? $args['count']
					: 10;

				// Get the request thumbnail configuration

				$thumbnailConfiguration = array_key_exists('thumb', $args)
					? $args['thumb']
					: array('h=45');

				global $wpdb;

				// Let's build the SQL Statement

				$sqlOrder = '';
				switch ($order) {
					case 'random':
						$sqlOrder = ' ORDER BY RAND()';
						break;
					case 'latest':
					default:
						$sqlOrder = ' ORDER BY p.post_date DESC';
						break;
				}

				$sql = 'SELECT p.ID, p.post_title FROM ' . $wpdb->posts . ' p LEFT JOIN ' . YAPB_TABLE_NAME . ' yi ON p.ID = yi.post_id WHERE p.post_type = \'post\' AND yi.URI IS NOT NULL AND p.post_status = \'publish\'' . $sqlOrder . ' LIMIT 0,' . $count;

				// Get the according posts

				$posts = $wpdb->get_results($sql);

				// Now we cylce through all posts, instance the according
				// YapbImage Instance and return all the needed data

				$result = array();

				if (!empty($posts)) {
					foreach ($posts as $post) {
						
						$item = array();
						$item['post.id'] = $post->ID;
						$item['post.title'] = $post->post_title;
						$item['post.url'] = get_permalink($post->ID);

						$yapbImage = YapbImage::getInstanceFromDb($post->ID);
						$item['img.url'] = $yapbImage->getThumbnailHref($thumbnailConfiguration);
						$item['img.width'] = $yapbImage->getThumbnailWidth($thumbnailConfiguration);
						$item['img.height'] = $yapbImage->getThumbnailHeight($thumbnailConfiguration);

						$result[] = $item;

					}
				} else {

					return new IXR_Error(404, 'no posts for the selected criterium.');

				}

				// Serializing and encoding of the result
				// This ensures that really everything is well-formed ;-)

				return base64_encode(serialize($result));

			} else {

				// Uh sorry - no valid api key
				// You won't get anything from me, boy.
				return new IXR_Error(401, __('please provide a valid api key'));

			}

		}

		/**
		 * This method creates a random API key for the
		 * XML RPC Authentication
		 **/
		function getRandomApiKey() {
			$length = 10;
			$dict = 'abcdefghijklmnopqrstuvwxyz'.
					'01234567890123456789012345';
			$dict_length = strlen($dict) - 1;
			$key = '';
			for($i=0; $i<$length; ++$i) {
				$value = rand(0, $dict_length);
				$key .= substr($dict, $value, 1);
			}
			return $key;
		}

		/**
		 * This method checks if the given API key is valid
		 *
		 * @param string $key
		 * @return boolean
		 **/
		function isValidApiKey($key) {
			$apikey = get_option('yapb_xmlrpc_apikey');
			if (strip_tags(stripslashes($key)) != $apikey) return false;
			else return true;
		}

	}

?>