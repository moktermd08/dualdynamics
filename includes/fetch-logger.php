/**
 * Dualdynamics_Fetch_Logger Class
 *
 * This class handles the logging functionality for the Dualdynamics plugin.
 * It provides methods to create a log table, store fetch logs, and retrieve fetch logs.
 *
 * @package dualdynamics
 * @subpackage dualdynamics/includes
 * @since 1.0.0
 */
class Dualdynamics_Fetch_Logger {

    /**
     * The name of the table used to store fetch logs.
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor.
     *
     * Initializes the class and sets up hooks.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'dualdynamics_fetch_logs';

        // Hook to create database table on plugin activation
        register_activation_hook(__FILE__, [$this, 'create_log_table']);
    }

    /**
     * Create a table to store fetch logs.
     *
     * This method creates a table in the WordPress database to store logs related to data fetch operations.
     */
    public function create_log_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            fetch_url varchar(255) NOT NULL,
            status varchar(255) NOT NULL,
            fetch_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Store fetch status to the database.
     *
     * This method stores logs related to fetch operations in the previously created log table.
     *
     * @param string $url The URL that was fetched.
     * @param string $status The status/result of the fetch operation.
     */
    public function log_fetch($url, $status) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            [
                'fetch_url'  => $url,
                'status'     => $status,
                'fetch_time' => current_time('mysql')
            ]
        );
    }

    /**
     * Get all fetch logs.
     *
     * This method retrieves all fetch logs stored in the log table.
     *
     * @return array An array of objects, each representing a fetch log.
     */
    public function get_fetch_logs() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY fetch_time DESC");
    }
}
