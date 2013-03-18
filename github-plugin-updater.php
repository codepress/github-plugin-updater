<?php

/*
Plugin Name: GitHub Plugin Updater
Description: Updates plugins that are hosted on GitHub.
Version: 1.0
Author: Codepress
Author URI: http://codepress.nl
License: GPLv2 or later
*/

if ( ! class_exists( 'GitHub_Plugin_Updater' ) ) :

/**
 * Register a new GitHub plugin
 *
 * @param array $config
 */
function github_plugin_updater_register( $config ) {
	new GitHub_Plugin_Updater( $config );
}

/**
 * Update a WordPress plugin via GitHub
 *
 * It's best avoided to call when is_admin() would return false.
 *
 * @version 1.0
 */
class GitHub_Plugin_Updater {

	/**
	 * Stores the config.
	 *
	 * @since 1.0
	 * @var type
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @param array $config
	 */
	public function __construct( $config ) {
		$defaults = array(
			'repo'			=> null,
			'owner'			=> null,
			'slug'			=> null,
			'access_token'  => null,
			'http_args'		=> array(),
		);
		$this->config = (object) array_merge( $defaults, $config );

		// default slug equals the repo name
		if ( empty( $this->config->slug ) )
			$this->config->slug = $this->config->repo . '/' . $this->config->repo . '.php';

		add_filter( 'http_request_args', array( $this, 'add_http_args' ), 10, 2 );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_available' ) );

		if ( isset( $_GET['get-zipball'] ) && $_GET['get-zipball'] == $this->config->slug )
			add_action( 'admin_init', array( $this, 'get_zipball' ) );
    }

	/**
	 * Call the GitHub API and return a json decoded body.
	 *
	 * @since 1.0
	 * @param string $url
	 * @see http://developer.github.com/v3/
	 * @return boolean|object
	 */
	protected function api( $url ) {

		add_filter( 'http_request_args', array( $this, 'add_http_args' ), 10, 2 );

		$response = wp_remote_get( $this->get_api_url( $url ) );

		remove_filter( 'http_request_args', array( $this, 'add_http_args' ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != '200' )
			return false;

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Return API url.
	 *
	 * @todo Maybe allow a filter to add or modify segments.
	 * @since 1.0
	 * @param string $endpoint
	 * @return string
	 */
	protected function get_api_url( $endpoint ) {
		$segments = array(
			'owner'			 => $this->config->owner,
			'repo'			 => $this->config->repo,
			'archive_format' => 'zipball',
		);

		foreach ( $segments as $segment => $value ) {
			$endpoint = str_replace( '/:' . $segment, '/' . $value, $endpoint );
		}

		if ( ! empty( $this->config->access_token ) )
			$endpoint = add_query_arg( 'access_token', $this->config->access_token );

		return 'https://api.github.com' . $endpoint;
	}

	/**
	 * Reads the remote plugin file.
	 *
	 * Uses a transient to limit the calls to the API.
	 *
	 * @since 1.0
	 */
	protected function get_remote_info() {
		$remote = get_site_transient( __CLASS__ . ':remote' );

		if ( ! $remote ) {
			$remote = $this->api( '/repos/:owner/:repo/contents/' . basename( $this->config->slug ) );

			if ( $remote )
				set_site_transient( __CLASS__ . ':remote', $remote, 60 * 60 );
		}

		return $remote;
	}

	/**
	 * Retrieves the local version from the file header of the plugin
	 *
	 * @since 1.0
	 * @return string|boolean
	 */
	protected function get_local_version() {
		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->config->slug );

		if ( ! empty( $data['Version'] ) )
			return $data['Version'];

		return false;
	}

	/**
	 * Retrieves the remote version from the file header of the plugin
	 *
	 * @since 1.0
	 * @return string|boolean
	 */
	protected function get_remote_version() {
		$response = $this->get_remote_info();

		if ( ! $response )
			return false;

		preg_match( '#^\s*Version\:\s*(.*)$#im', base64_decode( $response->content ), $matches );

		if ( ! empty( $matches[1] ) )
			return $matches[1];

		return false;
	}

	/**
	 * Hooks into pre_set_site_transient_update_plugins to update from GitHub.
	 *
	 * @since 1.0
	 * @todo fill url with value from remote repostory
	 * @param $transient
	 * @return $transient If all goes well, an updated one.
	 */
	public function update_available( $transient ) {

		if ( empty( $transient->checked ) )
			return $transient;

		$local_version  = $this->get_local_version();
		$remote_version = $this->get_remote_version();

		if ( $local_version && $remote_version && version_compare( $remote_version, $local_version, '>' ) ) {
			$plugin = array(
				'slug'		  => dirname( $this->config->slug ),
				'new_version' => $remote_version,
				'url'		  => null,
				'package'	  => admin_url( '?get-zipball=' . $this->config->slug ),
			);

			$transient->response[ $this->config->slug ] = (object) $plugin;
		}

		return $transient;
	}

	/**
	 * Allows to change any args used on downloading the zipball
	 *
	 * @since 1.0
	 * @param type $args
	 * @return type
	 */
	public function add_http_args( $args, $url ) {

		if ( 0 === strpos( $url, $this->get_api_url( '/repos/:owner/:repo/:archive_format' ) ) ) {
			foreach( $this->config->http_args as $name => $value ) {
				$args[ $name ] = $value;
			}
		}

		if ( admin_url( '?get-zipball=' . $this->config->slug ) == $url ) {
			$cookie = new WP_Http_Cookie( array(
				'name'		=> AUTH_COOKIE,
				'path'		=> ADMIN_COOKIE_PATH,
				'value'		=> $_COOKIE[ AUTH_COOKIE ],
				'expires'	=> 300,
				'domain'	=> defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : $_SERVER['HTTP_HOST'],
			) );

			$args['cookies'][] = $cookie;
		}

		return $args;
	}

	/**
	 * GitHub decides on the root directory in the zipball, but we might disagree.
	 *
	 * @since 1.0
	 */
	public function get_zipball() {

		add_filter( 'http_request_args', array( $this, 'add_http_args' ), 10, 2 );

		$zipball = download_url( $this->get_api_url( '/repos/:owner/:repo/:archive_format' ) );

		remove_filter( 'http_request_args', array( $this, 'add_http_args' ) );

		if ( is_wp_error( $zipball ) )
			$this->return_404();

		$z = new ZipArchive();

		if ( true === $z->open( $zipball ) ) {
			$length	= strlen( $z->getNameIndex( 0 ) );
			$status = true;

			for ( $i=0; $i<$z->numFiles; $i++ ) {
				$name = $z->getNameIndex( $i );

				if ( ! $name )
					$status = false;

				$newname = substr_replace( $name, $this->config->repo, 0, $length - 1 );

				if ( ! $z->renameName( $name, $newname ) )
					$status = false;
			}

			$z->close();

			if ( $status ) {
				header( 'Content-Disposition: attachment; filename=' . $this->config->repo . '.zip' );
				header( 'Content-Type: application/zip' );
				header( 'Content-Length: ' . filesize( $zipball ) );

				ob_clean();
				flush();
				readfile( $zipball );
				unlink( $zipball );
				exit;
			}
		}

		unlink( $zipball );

		$this->return_404();
	}

	/**
	 * Getting the zipball has failed. All hail the zipball.
	 *
	 * @since 1.0
	 */
	protected function return_404() {
		header( 'HTTP/1.1 404 Not Found', true, 404 );
		exit;
	}
}

endif;