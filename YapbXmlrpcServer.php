<?php

	/*
	Plugin Name: YAPB XMLRPC Server
	Plugin URI: http://johannes.jarolim.com/yapb/xmlrpc-server
	Description: Adds YAPB remote accessibility via XML RPC
	Author: J.P.Jarolim
	Author URI: http://johannes.jarolim.com
	License: GPL
	Version: 1.1
	*/

	/*
	 * Access your YAPB images remotly via XML RPC.
	 * Sample Client Code using IXR - The Inutio XML-RPC Library:
	 *
	 * $client = new IXR_Client('http://www.yourdomain.tld/blog-dir/xmlrpc.php');
	 * $client->query(
	 *   'yapb.request',
	 *   array(
	 *     'apikey' => 'your API key here', // Your API key
	 *     'order' => 'latest',             // We want the latest images ('random' for a selection of random images)
	 *     'count' => 5,                    // We want 5 images
	 *     'thumb' => array(                // phpThumb configuration
	 *       'h=45',                        // Max height of 45 Pixels
	 *       'q=100'                        // jpeg quality of 100
	 *     )
	 *   )
	 * );
	 * $response = &$client->getResponse();
	 * print_r($response);
	 *
	 */

	/*	
	 * Special Thanks:
	 *
	 * Markus Mayer: Thanks to Markus for providing the code of his original
	 *               XMLRPC server plugin: Without that sample code (which still lives 
	 *               on in some methods) this wouldn't have been possible.
	 */

	/** Short and Sweet **/

	require_once 'YapbXmlrpcServer.class.php';
	$yapbXmlrpcServer = new YapbXmlrpcServer();

?>