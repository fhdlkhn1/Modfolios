<?php
/**
 * Modfolio Google Drive Integration
 *
 * Handles uploading downloadable product files to Google Drive (Workspace compatible)
 * and managing vendor-specific folders.
 *
 * @package Modfolio
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load Google API Client Library
if ( file_exists( get_template_directory() . '/vendor/autoload.php' ) ) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Google\Service\Drive\DriveFile as Google_Service_Drive_DriveFile;
use Google\Service\Drive\Permission as Google_Service_Drive_Permission;

/**
 * Class Modfolio_Google_Drive
 *
 * Manages Google Drive integration for downloadable products
 */
class Modfolio_Google_Drive {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Path to OAuth credentials JSON file
     */
    private $credentials_path;

    /**
     * Path to OAuth token JSON file
     */
    private $token_path;

    /**
     * Main parent folder ID in Google Drive
     */
    private $main_folder_id;

    /**
     * Google Client instance
     */
    private $client = null;

    /**
     * Google Drive Service instance
     */
    private $drive_service = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_paths();
        $this->init_hooks();
    }

    /**
     * Initialize file paths
     */
    private function init_paths() {
        // Default paths - can be filtered
        $this->credentials_path = apply_filters(
            'modfolio_gdrive_credentials_path',
            ABSPATH . 'wp-content/uploads/secure/google-credentials.json'
        );

        $this->token_path = apply_filters(
            'modfolio_gdrive_token_path',
            ABSPATH . 'wp-content/uploads/secure/google-token.json'
        );

        // Main parent folder ID in Google Drive - UPDATE THIS WITH YOUR FOLDER ID
        $this->main_folder_id = apply_filters(
            'modfolio_gdrive_main_folder_id',
            get_option( 'modfolio_gdrive_folder_id', '' )
        );
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Hook into WCFM product save
        add_action( 'after_wcfm_products_manage_meta_save', array( $this, 'process_product_downloads' ), 20, 2 );

        // Admin settings
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // AJAX handlers for OAuth
        add_action( 'wp_ajax_modfolio_gdrive_auth_url', array( $this, 'ajax_get_auth_url' ) );
        add_action( 'wp_ajax_modfolio_gdrive_save_token', array( $this, 'ajax_save_token' ) );
        add_action( 'wp_ajax_modfolio_gdrive_test_connection', array( $this, 'ajax_test_connection' ) );
        add_action( 'wp_ajax_modfolio_gdrive_delete_token', array( $this, 'ajax_delete_token' ) );
    }

    /**
     * Initialize Google Client
     *
     * @return Google_Client|WP_Error
     */
    private function init_client() {
        if ( $this->client !== null ) {
            return $this->client;
        }

        if ( ! file_exists( $this->credentials_path ) ) {
            $this->log( 'ERROR: Credentials file not found at: ' . $this->credentials_path );
            return new WP_Error( 'no_credentials', 'Google credentials file not found. Please upload your credentials.json file.' );
        }

        try {
            $this->client = new Google_Client();
            $this->client->setAuthConfig( $this->credentials_path );
            $this->client->addScope( Google_Service_Drive::DRIVE );
            $this->client->setAccessType( 'offline' );
            $this->client->setPrompt( 'consent' ); // Force to get refresh token
            $this->client->setIncludeGrantedScopes( true );

            // Load existing token
            if ( file_exists( $this->token_path ) ) {
                $token = json_decode( file_get_contents( $this->token_path ), true );

                // Ensure we have refresh_token preserved
                $refresh_token = isset( $token['refresh_token'] ) ? $token['refresh_token'] : null;

                $this->client->setAccessToken( $token );

                // Refresh token if expired
                if ( $this->client->isAccessTokenExpired() ) {
                    if ( $this->client->getRefreshToken() || $refresh_token ) {
                        try {
                            $new_token = $this->client->fetchAccessTokenWithRefreshToken(
                                $this->client->getRefreshToken() ?: $refresh_token
                            );

                            // Check for errors in the new token
                            if ( isset( $new_token['error'] ) ) {
                                $this->log( 'ERROR: Token refresh failed - ' . $new_token['error'] );
                                // Delete invalid token file
                                @unlink( $this->token_path );
                                return new WP_Error( 'token_invalid', 'OAuth token invalid. Please re-authenticate via Settings > Google Drive.' );
                            }

                            // Preserve refresh_token if not in new token
                            if ( ! isset( $new_token['refresh_token'] ) && $refresh_token ) {
                                $new_token['refresh_token'] = $refresh_token;
                            }

                            $this->save_token( $new_token );
                            $this->client->setAccessToken( $new_token );
                            $this->log( 'Token refreshed successfully.' );
                        } catch ( Exception $e ) {
                            $this->log( 'ERROR: Token refresh exception - ' . $e->getMessage() );
                            // Delete invalid token file
                            @unlink( $this->token_path );
                            return new WP_Error( 'token_refresh_failed', 'Token refresh failed. Please re-authenticate via Settings > Google Drive.' );
                        }
                    } else {
                        $this->log( 'ERROR: Token expired and no refresh token available.' );
                        return new WP_Error( 'token_expired', 'OAuth token expired. Please re-authenticate.' );
                    }
                }
            } else {
                $this->log( 'WARNING: Token file not found. Authentication required.' );
                return new WP_Error( 'no_token', 'OAuth token not found. Please authenticate first.' );
            }

            return $this->client;

        } catch ( Exception $e ) {
            $this->log( 'EXCEPTION during client init: ' . $e->getMessage() );
            return new WP_Error( 'client_error', $e->getMessage() );
        }
    }

    /**
     * Get Drive Service
     *
     * @return Google_Service_Drive|WP_Error
     */
    private function get_drive_service() {
        if ( $this->drive_service !== null ) {
            return $this->drive_service;
        }

        $client = $this->init_client();
        if ( is_wp_error( $client ) ) {
            return $client;
        }

        $this->drive_service = new Google_Service_Drive( $client );
        return $this->drive_service;
    }

    /**
     * Upload file to Google Drive
     *
     * @param string $file_path Local file path
     * @param string|null $folder_id Target folder ID (optional)
     * @return array|WP_Error
     */
    public function upload_file( $file_path, $folder_id = null ) {
        if ( ! file_exists( $file_path ) ) {
            $this->log( 'ERROR: Local file missing: ' . $file_path );
            return new WP_Error( 'file_not_found', 'Local file does not exist: ' . $file_path );
        }

        $service = $this->get_drive_service();
        if ( is_wp_error( $service ) ) {
            return $service;
        }

        try {
            $file_name = basename( $file_path );
            $mime_type = mime_content_type( $file_path ) ?: 'application/octet-stream';

            // Prepare file metadata
            $file_metadata = new Google_Service_Drive_DriveFile([
                'name' => $file_name,
                'parents' => $folder_id ? [ $folder_id ] : ( $this->main_folder_id ? [ $this->main_folder_id ] : [] )
            ]);

            // Upload file - with Shared Drive support
            $content = file_get_contents( $file_path );
            $file = $service->files->create( $file_metadata, [
                'data' => $content,
                'mimeType' => $mime_type,
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink,webContentLink',
                'supportsAllDrives' => true  // Support Shared Drives
            ]);

            // Set file permissions (anyone with link can view/download)
            $this->set_file_public( $file->id );

            // Generate direct download link
            $download_link = 'https://drive.google.com/uc?export=download&id=' . $file->id;

            $this->log( 'SUCCESS: Uploaded file ' . $file->name . ' (ID: ' . $file->id . ')' );

            return [
                'id' => $file->id,
                'name' => $file->name,
                'download_link' => $download_link,
                'webViewLink' => $file->webViewLink ?? null,
                'webContentLink' => $file->webContentLink ?? null,
            ];

        } catch ( Exception $e ) {
            $this->log( 'EXCEPTION during upload: ' . $e->getMessage() );
            return new WP_Error( 'upload_error', $e->getMessage() );
        }
    }

    /**
     * Set file to public (anyone with link)
     *
     * @param string $file_id Google Drive file ID
     * @return bool|WP_Error
     */
    private function set_file_public( $file_id ) {
        $service = $this->get_drive_service();
        if ( is_wp_error( $service ) ) {
            return $service;
        }

        try {
            $permission = new Google_Service_Drive_Permission();
            $permission->setType( 'anyone' );
            $permission->setRole( 'reader' );

            // Support Shared Drives
            $service->permissions->create( $file_id, $permission, [
                'supportsAllDrives' => true
            ]);
            return true;

        } catch ( Exception $e ) {
            $this->log( 'EXCEPTION setting permissions: ' . $e->getMessage() );
            return new WP_Error( 'permission_error', $e->getMessage() );
        }
    }

    /**
     * Get or create vendor folder
     *
     * @param string $vendor_name Vendor display name
     * @return string|WP_Error Folder ID
     */
    public function get_or_create_vendor_folder( $vendor_name ) {
        $service = $this->get_drive_service();
        if ( is_wp_error( $service ) ) {
            return $service;
        }

        $folder_name = sanitize_title( $vendor_name );
        $parent_folder_id = $this->main_folder_id;

        if ( empty( $parent_folder_id ) ) {
            $this->log( 'WARNING: No main folder ID configured. Files will be uploaded to root.' );
            return '';
        }

        try {
            // Search for existing folder - with Shared Drive support
            $query = sprintf(
                "mimeType = 'application/vnd.google-apps.folder' and name = '%s' and '%s' in parents and trashed = false",
                addslashes( $folder_name ),
                $parent_folder_id
            );

            $results = $service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id,name)',
                'supportsAllDrives' => true,           // Support Shared Drives
                'includeItemsFromAllDrives' => true,   // Include items from Shared Drives
            ]);

            if ( count( $results->files ) > 0 ) {
                $this->log( 'Found existing vendor folder: ' . $folder_name . ' (ID: ' . $results->files[0]->id . ')' );
                return $results->files[0]->id;
            }

            // Create new folder - with Shared Drive support
            $folder_metadata = new Google_Service_Drive_DriveFile([
                'name' => $folder_name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [ $parent_folder_id ]
            ]);

            $folder = $service->files->create( $folder_metadata, [
                'fields' => 'id',
                'supportsAllDrives' => true  // Support Shared Drives
            ]);

            $this->log( 'Created new vendor folder: ' . $folder_name . ' (ID: ' . $folder->id . ')' );
            return $folder->id;

        } catch ( Exception $e ) {
            $this->log( 'EXCEPTION during folder creation: ' . $e->getMessage() );
            // Fallback to main folder
            return $parent_folder_id;
        }
    }

    /**
     * Process product downloads after WCFM save
     *
     * @param int $post_id Product ID
     * @param array $form_data Form data from WCFM
     */
    public function process_product_downloads( $post_id, $form_data ) {
        $product = wc_get_product( $post_id );

        if ( ! $product || ! $product->is_downloadable() ) {
            $this->log( 'Skipped - product not downloadable. Product ID: ' . $post_id );
            return;
        }

        $this->log( 'Processing downloads for product ' . $post_id );

        $downloads = $product->get_downloads();
        if ( empty( $downloads ) ) {
            $this->log( 'No downloads found for product ' . $post_id );
            return;
        }

        // Get vendor info
        $vendor_id = function_exists( 'wcfm_get_vendor_id_by_post' )
            ? wcfm_get_vendor_id_by_post( $post_id )
            : get_post_field( 'post_author', $post_id );

        $vendor_user = get_userdata( $vendor_id );
        $vendor_name = $vendor_user ? $vendor_user->display_name : 'unknown-vendor';

        // Get or create vendor folder
        $vendor_folder_id = $this->get_or_create_vendor_folder( $vendor_name );
        if ( is_wp_error( $vendor_folder_id ) ) {
            $this->log( 'ERROR getting vendor folder: ' . $vendor_folder_id->get_error_message() );
            $vendor_folder_id = $this->main_folder_id;
        }

        $updated_downloads = [];
        $has_changes = false;

        foreach ( $downloads as $key => $download_obj ) {
            $file_url = $download_obj->get_file();
            $name = $download_obj->get_name();

            $this->log( 'Processing file: ' . $file_url );

            // Skip if already a Drive link
            if ( strpos( $file_url, 'drive.google.com' ) !== false ) {
                $this->log( 'Already a Drive link, skipping.' );
                $updated_downloads[ $key ] = $download_obj;
                continue;
            }

            // Try to get local file path
            $attachment_id = attachment_url_to_postid( $file_url );
            if ( ! $attachment_id ) {
                $this->log( 'Could not resolve attachment ID for: ' . $file_url );
                $updated_downloads[ $key ] = $download_obj;
                continue;
            }

            $local_path = get_attached_file( $attachment_id );
            if ( ! $local_path || ! file_exists( $local_path ) ) {
                $this->log( 'Local file not found for attachment ' . $attachment_id );
                $updated_downloads[ $key ] = $download_obj;
                continue;
            }

            // Upload to Google Drive
            $result = $this->upload_file( $local_path, $vendor_folder_id );

            if ( is_wp_error( $result ) ) {
                $this->log( 'Upload error: ' . $result->get_error_message() );
                $updated_downloads[ $key ] = $download_obj;
                continue;
            }

            // Create new download with Drive link
            $new_key = 'file-' . wp_generate_password( 10, false, false );
            $new_download = new WC_Product_Download();
            $new_download->set_id( $new_key );
            $new_download->set_name( $name );
            $new_download->set_file( esc_url_raw( $result['download_link'] ) );

            $updated_downloads[ $new_key ] = $new_download;
            $has_changes = true;

            $this->log( 'Successfully uploaded to Drive: ' . $result['download_link'] );
        }

        // Save updated downloads if there were changes
        if ( $has_changes ) {
            $product->set_downloads( $updated_downloads );
            $product->save();
            $this->log( 'Finished updating product ' . $post_id );
        }
    }

    /**
     * Save OAuth token
     *
     * @param array $token Token data
     * @return bool
     */
    public function save_token( $token ) {
        // Ensure directory exists
        $dir = dirname( $this->token_path );
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        // Secure the directory
        $htaccess = $dir . '/.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, "Order deny,allow\nDeny from all" );
        }

        return file_put_contents( $this->token_path, json_encode( $token ) ) !== false;
    }

    /**
     * Get OAuth authentication URL
     *
     * @return string|WP_Error
     */
    public function get_auth_url() {
        if ( ! file_exists( $this->credentials_path ) ) {
            return new WP_Error( 'no_credentials', 'Credentials file not found.' );
        }

        try {
            $client = new Google_Client();
            $client->setAuthConfig( $this->credentials_path );
            $client->addScope( Google_Service_Drive::DRIVE );
            $client->setAccessType( 'offline' );
            $client->setPrompt( 'consent' );
            $client->setRedirectUri( admin_url( 'admin.php?page=modfolio-gdrive-settings' ) );

            return $client->createAuthUrl();

        } catch ( Exception $e ) {
            return new WP_Error( 'auth_error', $e->getMessage() );
        }
    }

    /**
     * Exchange auth code for token
     *
     * @param string $code Authorization code
     * @return array|WP_Error
     */
    public function exchange_code_for_token( $code ) {
        if ( ! file_exists( $this->credentials_path ) ) {
            return new WP_Error( 'no_credentials', 'Credentials file not found.' );
        }

        try {
            $client = new Google_Client();
            $client->setAuthConfig( $this->credentials_path );
            $client->setRedirectUri( admin_url( 'admin.php?page=modfolio-gdrive-settings' ) );

            $token = $client->fetchAccessTokenWithAuthCode( $code );

            if ( isset( $token['error'] ) ) {
                return new WP_Error( 'token_error', $token['error_description'] ?? $token['error'] );
            }

            $this->save_token( $token );
            $this->log( 'Token saved successfully.' );

            return $token;

        } catch ( Exception $e ) {
            return new WP_Error( 'exchange_error', $e->getMessage() );
        }
    }

    /**
     * Test connection to Google Drive
     *
     * @return array|WP_Error
     */
    public function test_connection() {
        $service = $this->get_drive_service();
        if ( is_wp_error( $service ) ) {
            return $service;
        }

        try {
            $about = $service->about->get(['fields' => 'user']);
            return [
                'success' => true,
                'email' => $about->user->emailAddress,
                'name' => $about->user->displayName
            ];
        } catch ( Exception $e ) {
            return new WP_Error( 'connection_error', $e->getMessage() );
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Google Drive Settings',
            'Google Drive',
            'manage_options',
            'modfolio-gdrive-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'modfolio_gdrive_settings', 'modfolio_gdrive_folder_id' );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle token deletion
        if ( isset( $_GET['delete_token'] ) && $_GET['delete_token'] === '1' ) {
            if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'modfolio_delete_token' ) ) {
                $this->delete_token();
                echo '<div class="notice notice-success"><p>Token deleted. Please re-authenticate.</p></div>';
            }
        }

        // Handle OAuth callback
        if ( isset( $_GET['code'] ) ) {
            $result = $this->exchange_code_for_token( sanitize_text_field( $_GET['code'] ) );
            if ( is_wp_error( $result ) ) {
                echo '<div class="notice notice-error"><p>Authentication failed: ' . esc_html( $result->get_error_message() ) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>Successfully authenticated with Google Drive!</p></div>';
            }
        }

        // Test connection
        $connection_status = $this->test_connection();
        $is_connected = ! is_wp_error( $connection_status );
        ?>
        <div class="wrap">
            <h1>Google Drive Integration Settings</h1>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Connection Status</h2>
                <?php if ( $is_connected ) : ?>
                    <p style="color: green;">
                        <strong>&#10004; Connected</strong><br>
                        Account: <?php echo esc_html( $connection_status['email'] ); ?><br>
                        Name: <?php echo esc_html( $connection_status['name'] ); ?>
                    </p>
                <?php else : ?>
                    <p style="color: red;">
                        <strong>&#10008; Not Connected</strong><br>
                        <?php echo esc_html( $connection_status->get_error_message() ); ?>
                    </p>
                <?php endif; ?>

                <?php
                $auth_url = $this->get_auth_url();
                if ( ! is_wp_error( $auth_url ) ) :
                ?>
                    <p>
                        <a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary">
                            <?php echo $is_connected ? 'Re-authenticate' : 'Connect to Google Drive'; ?>
                        </a>
                        <?php if ( file_exists( $this->token_path ) ) : ?>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=modfolio-gdrive-settings&delete_token=1' ), 'modfolio_delete_token' ) ); ?>"
                               class="button button-secondary"
                               onclick="return confirm('Are you sure you want to delete the token? You will need to re-authenticate.');">
                                Delete Token &amp; Re-authenticate
                            </a>
                        <?php endif; ?>
                    </p>
                <?php else : ?>
                    <p style="color: orange;">Cannot generate auth URL: <?php echo esc_html( $auth_url->get_error_message() ); ?></p>
                <?php endif; ?>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'modfolio_gdrive_settings' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="modfolio_gdrive_folder_id">Main Folder ID</label>
                            </th>
                            <td>
                                <input type="text" name="modfolio_gdrive_folder_id" id="modfolio_gdrive_folder_id"
                                       value="<?php echo esc_attr( get_option( 'modfolio_gdrive_folder_id', '' ) ); ?>"
                                       class="regular-text" />
                                <p class="description">
                                    The Google Drive folder ID where vendor folders will be created.<br>
                                    Find this in the folder URL: drive.google.com/drive/folders/<strong>[FOLDER_ID]</strong>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>File Paths</h2>
                <table class="form-table">
                    <tr>
                        <th>Credentials File</th>
                        <td>
                            <code><?php echo esc_html( $this->credentials_path ); ?></code>
                            <?php if ( file_exists( $this->credentials_path ) ) : ?>
                                <span style="color: green;">&#10004; Found</span>
                            <?php else : ?>
                                <span style="color: red;">&#10008; Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Token File</th>
                        <td>
                            <code><?php echo esc_html( $this->token_path ); ?></code>
                            <?php if ( file_exists( $this->token_path ) ) : ?>
                                <span style="color: green;">&#10004; Found</span>
                            <?php else : ?>
                                <span style="color: orange;">&#10008; Not yet created (authenticate first)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2>Setup Instructions</h2>
                <ol>
                    <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                    <li>Create a new project (or select existing)</li>
                    <li>Enable the <strong>Google Drive API</strong></li>
                    <li>Go to <strong>Credentials</strong> → <strong>Create Credentials</strong> → <strong>OAuth client ID</strong></li>
                    <li>Select <strong>Web application</strong></li>
                    <li>Add this redirect URI: <code><?php echo esc_html( admin_url( 'admin.php?page=modfolio-gdrive-settings' ) ); ?></code></li>
                    <li>Download the JSON credentials file</li>
                    <li>Rename it to <code>google-credentials.json</code> and upload to: <code>wp-content/uploads/secure/</code></li>
                    <li>Click "Connect to Google Drive" button above</li>
                    <li>Enter your main Drive folder ID in the settings above</li>
                </ol>

                <h3>For Google Workspace:</h3>
                <ul>
                    <li>Make sure the OAuth consent screen is configured for your organization</li>
                    <li>If using domain-wide delegation, configure it in Google Admin Console</li>
                    <li>Ensure the user authenticating has access to the target Drive folder</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Get auth URL
     */
    public function ajax_get_auth_url() {
        check_ajax_referer( 'modfolio_gdrive_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $url = $this->get_auth_url();
        if ( is_wp_error( $url ) ) {
            wp_send_json_error( $url->get_error_message() );
        }

        wp_send_json_success( [ 'url' => $url ] );
    }

    /**
     * AJAX: Test connection
     */
    public function ajax_test_connection() {
        check_ajax_referer( 'modfolio_gdrive_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $result = $this->test_connection();
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        wp_send_json_success( $result );
    }

    /**
     * AJAX: Delete token (for re-authentication)
     */
    public function ajax_delete_token() {
        check_ajax_referer( 'modfolio_gdrive_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        if ( file_exists( $this->token_path ) ) {
            @unlink( $this->token_path );
            $this->client = null;
            $this->drive_service = null;
            wp_send_json_success( [ 'message' => 'Token deleted. Please re-authenticate.' ] );
        } else {
            wp_send_json_error( 'No token file found.' );
        }
    }

    /**
     * Delete token file (public method)
     *
     * @return bool
     */
    public function delete_token() {
        if ( file_exists( $this->token_path ) ) {
            $this->client = null;
            $this->drive_service = null;
            return @unlink( $this->token_path );
        }
        return false;
    }

    /**
     * Log messages
     *
     * @param string $message Message to log
     */
    private function log( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[Modfolio Drive] ' . $message );
        }
    }
}

// Initialize
function modfolio_google_drive() {
    return Modfolio_Google_Drive::get_instance();
}

// Start on plugins loaded
add_action( 'init', 'modfolio_google_drive' );
