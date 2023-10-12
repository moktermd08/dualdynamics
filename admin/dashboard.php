<?php

/**
 * File for the admin-facing aspects of the plugin.
 *
 * Provides an admin area view for the plugin. This file is responsible for marking up the
 * admin-side of the plugin.
 *
 * @link              https://github.com/moktermd08
 * @since             1.0.0
 * @package           Dualdynamics
 * @subpackage        Dualdynamics/admin/
 * @author            Mokter Hossian
 * @author_email      mokter897@gmail.com
 * @author_website    https://github.com/moktermd08
 */

require_once plugin_dir_path(__FILE__) . '../includes/fetch-logger.php';

class dualdynamics_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'styles/admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'scripts/admin.js', array('jquery'), $this->version, false);
    }

   public function add_admin_menu() {
    add_menu_page(
        'Import RSS',
        'Import RSS',
        'manage_options',
        'dualdynamics-rss-import',
        array($this, 'display_admin_page_content'),
        'dashicons-rss',
        100
    );

    add_submenu_page(
        'dualdynamics-rss-import', // Parent slug
        'Fetch Logs',
        'Fetch Logs',
        'manage_options',
        'dualdynamics-fetch-logs',
        array($this, 'display_fetch_logs')
    );
}


    public function display_fetch_logs() {
    ?>
    <div class="wrap">
        <h1>Fetch Logs</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>RSS URL</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php

                $logger = new Dualdynamics_Fetch_Logger();
$logs = $logger->get_fetch_logs();  // Note the updated method name
                foreach ($logs as $log) {
                    echo '<tr>';
                    echo '<td>' . esc_html($log->fetch_time) . '</td>';
                    echo '<td>' . esc_url($log->fetch_url) . '</td>';
                    echo '<td>' . esc_html($log->status) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}


   public function display_admin_page_content() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="" method="post">
            <?php wp_nonce_field('dualdynamics_import_rss', 'dualdynamics_nonce'); ?>
            <label for="rss-url">RSS URL:</label>
            <input type="url" id="rss-url" name="rss_url" required value="https://www.dailymail.co.uk/articles.rss"><br/>

            <label for="num-entries">Number of Entries:</label>
            <select id="num-entries" name="num_entries">
                <?php for ($i = 5; $i <= 100; $i += 5): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select><br/>

            <label for="title-length">Title Length:</label>
            <input type="number" id="title-length" name="title_length" min="1" value="100"><br/>

            <label for="excerpt-length">Excerpt Length:</label>
            <input type="number" id="excerpt-length" name="excerpt_length" min="1" value="200"><br/>

            <label for="description-length">Description Length:</label>
            <input type="number" id="description-length" name="description_length" min="1" value="500"><br/>

            <label for="image-style">Image Style:</label>
            <select id="image-style" name="image_style">
                <?php 
                $image_sizes = get_intermediate_image_sizes();
                foreach ($image_sizes as $size_name):
                ?>
                    <option value="<?php echo $size_name; ?>"><?php echo ucfirst($size_name); ?></option>
                <?php endforeach; ?>
            </select><br/><br/>

            <h2>RSS Field Mapper</h2>
            <!-- Mapping fields input here -->
            <!-- ... -->
            <input type="submit" id="submit-button" name="import_rss" class="button button-primary" value="Import RSS">
        </form>
    </div>
    <?php
    if (isset($_POST['import_rss']) && check_admin_referer('dualdynamics_import_rss', 'dualdynamics_nonce')) {
        $rss_url = sanitize_url($_POST['rss_url']);
        $num_entries = intval($_POST['num_entries']);
        // Store and use these new variables in your importing process:
        $title_length = intval($_POST['title_length']);
        $excerpt_length = intval($_POST['excerpt_length']);
        $description_length = intval($_POST['description_length']);
        $image_style = sanitize_text_field($_POST['image_style']);
        $this->import_rss_feed($rss_url, $num_entries); // Make sure you modify the import function to use the new variables.
    }
}


private function get_mapped_field($item, $field_name) {
    switch ($field_name) {
        case 'title':
            return $item->get_title();
        case 'description':
            return $item->get_description();
        case 'thumbnail':
            return $item->get_enclosure()->get_link();
        // Add cases for other fields as needed
        default:
            return '';
    }
}


/**
 * Imports entries from the given RSS feed URL and displays or saves them as posts.
 *
 * @param string $rss_url The RSS feed URL to import.
 * @param int $num_entries The number of entries to import.
 * @return void
 */

private function import_rss_feed(string $rss_url, int $num_entries): void
{
    // Check if the current user has the right capability.
    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to run this operation.');
    }

    // Fetch the RSS feed.
    $rss = fetch_feed($rss_url);
    
    // If there was an error fetching the feed, display an error message and exit.
    if (is_wp_error($rss)) {
        echo 'Failed to fetch RSS feed from the provided URL.';
        return;
    }

    // Define the default field mappings, these can be overwritten by POST data.
    $mapper = array(
        'title'       => 'title',
        'excerpt'     => 'description',
        'description' => 'description',
        'thumbnail'   => ''
    );

    // Update mappings from POST if they're set.
    foreach ($mapper as $key => $default) {
        if (isset($_POST['map_' . $key])) {
            $mapper[$key] = sanitize_text_field($_POST['map_' . $key]);
        }
    }

    // Get the title, excerpt, and description length limits from the POST data.
    $title_length = isset($_POST['title_length']) ? intval($_POST['title_length']) : 255;
    $excerpt_length = isset($_POST['excerpt_length']) ? intval($_POST['excerpt_length']) : 255;
    $description_length = isset($_POST['description_length']) ? intval($_POST['description_length']) : 500;

    // Get the required number of items from the RSS feed.
    $maxitems = $rss->get_item_quantity($num_entries);
    $rss_items = $rss->get_items(0, $maxitems);

    // Display fetched items.
    $this->display_fetched_items($rss_items, $mapper);

    // Save items as posts with the length limits.
    $this->save_items_as_posts($rss_items, $mapper, $title_length, $excerpt_length, $description_length);

    // Instantiate the logger
$logger = new Dualdynamics_Fetch_Logger();
$logs = $logger->get_fetch_logs();  // Note the updated method name


// Fetch the RSS feed.
$rss = fetch_feed($rss_url);

// If there was an error fetching the feed, log the failure status and display an error message.
if (is_wp_error($rss)) {
    $logger->log_fetch($rss_url, 'Failed');  // Logging the fetch failure
    echo 'Failed to fetch RSS feed from the provided URL.';
    return;
} else {
    $logger->log_fetch($rss_url, 'Success');  // Logging the fetch success
}

}


/**
 * Displays the fetched RSS items.
 *
 * @param array $rss_items Fetched RSS items.
 * @param array $mapper Field mapping.
 * @return void
 */
private function display_fetched_items(array $rss_items, array $mapper): void
{
    echo '<ul>';
    foreach ($rss_items as $item) {
        echo '<li>';
        echo '<strong>Title:</strong> ' . esc_html($this->get_mapped_field($item, $mapper['title'])) . '<br/>';
        echo '<strong>Excerpt:</strong> ' . esc_html($this->get_mapped_field($item, $mapper['excerpt'])) . '<br/>';
        echo '<strong>Description:</strong> ' . esc_html($this->get_mapped_field($item, $mapper['description'])) . '<br/>';
        $thumbnail = $this->get_mapped_field($item, $mapper['thumbnail']);
        if ($thumbnail) {
            echo '<strong>Thumbnail:</strong> <img src="' . esc_url($thumbnail) . '" width="100" /><br/>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Saves fetched RSS items as WordPress posts.
 *
 * @param array $rss_items Fetched RSS items.
 * @param array $mapper Field mapping.
 * @param int $title_length Maximum title length.
 * @param int $excerpt_length Maximum excerpt length.
 * @param int $description_length Maximum description length.
 * @return void
 */
private function save_items_as_posts(array $rss_items, array $mapper, int $title_length, int $excerpt_length, int $description_length): void
{
    foreach ($rss_items as $item) {
        // Check if post already exists by title.
        $title = esc_html($this->get_mapped_field($item, $mapper['title']));
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'title' => $title,
            'fields' => 'ids',
            'posts_per_page' => 1,
        );
        $query = new WP_Query($args);

        $existing = !empty($query->posts) ? get_post($query->posts[0]) : null;

        // If post doesn't exist, create it.
        if ($existing === null) {
            $my_post = array(
                'post_title'   => wp_trim_words(esc_html($this->get_mapped_field($item, $mapper['title'])), $title_length, ''),
                'post_content' => wp_trim_words(esc_html($this->get_mapped_field($item, $mapper['description'])), $description_length, ''),
                'post_excerpt' => wp_trim_words(esc_html($this->get_mapped_field($item, $mapper['excerpt'])), $excerpt_length, ''),
                'post_status'  => 'publish', // Change to 'draft' if you don't want to publish immediately.
                'post_author'  => 1,        // Change to desired author's ID.
                'post_type'    => 'post',
            );
            $post_id = wp_insert_post($my_post);

            // If a thumbnail is set, attach it to the post.
            $thumbnail = $this->get_mapped_field($item, $mapper['thumbnail']);
            if ($thumbnail) {
                $attach_id = $this->insert_attachment_from_url($thumbnail, $post_id);
                if ($attach_id) {
                    set_post_thumbnail($post_id, $attach_id);
                }
            }
        }
    }
}


private function insert_attachment_from_url($url, $post_id = null) {
    if (!$url || empty($url)) return new WP_Error('No URL provided');

    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $filename = basename($url);
    $upload_file = wp_upload_bits($filename, null, file_get_contents($url));
    if (!$upload_file['error']) {
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attachment_id = wp_insert_attachment($attachment, $upload_file['file'], $post_id);
        if (!is_wp_error($attachment_id)) {
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            return $attachment_id;
        }
    }

    return false;
}


}
