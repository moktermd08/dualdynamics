<?php 
/**
 * AbstractRSSFeedImporter Class
 * An abstract base for importing posts from an RSS feed.
 */
abstract class AbstractRSSFeedImporter {

    /**
     * Get the RSS Feed URL. 
     * Child classes should define this method to set their feed URLs.
     *
     * @return string RSS Feed URL.
     */
    abstract protected function get_rss_feed_url();

    /**
     * Get the mappings for the RSS fields.
     * Child classes can override this to provide specific mappings.
     *
     * @return array Field mappings.
     */
    protected function get_rss_field_mappings() {
        return array(
            'title' => 'title',
            'excerpt' => 'description',
            'description' => 'description',
            'thumbnail' => 'thumbnail',
        );
    }

    /**
     * Permission check for using the API.
     *
     * @return bool True if user has the capability, false otherwise.
     */
    protected function can_import_rss() {
        return current_user_can('manage_options');
    }

    /**
     * Fetch the RSS feed and convert entries into WordPress posts.
     *
     * @param string|null $rss_feed_url RSS feed URL. If null, uses the abstract function's value.
     * @param int $num_entries Number of RSS feed entries to fetch.
     *
     * @return array Response indicating success or failure.
     */
    public function import_rss_to_posts($rss_feed_url = null, $num_entries = 5) {
        $rss_feed_url = $rss_feed_url ?? $this->get_rss_feed_url();
        
        $rss = fetch_feed($rss_feed_url);

        if (is_wp_error($rss)) {
            return new WP_Error('rss_error', 'Error fetching the RSS feed.');
        }

        $items = $rss->get_items(0, $num_entries);
        $mappings = $this->get_rss_field_mappings();

        foreach ($items as $item) {
            wp_insert_post(array(
                'post_title'   => sanitize_text_field($this->get_mapped_field($item, $mappings['title'])),
                'post_excerpt' => sanitize_text_field($this->get_mapped_field($item, $mappings['excerpt'])),
                'post_content' => wp_kses_post($this->get_mapped_field($item, $mappings['description'])),
                'post_status'  => 'publish',
            ));
        }

        return array('success' => true, 'message' => 'Posts imported successfully.');
    }

    private function get_mapped_field($item, $field_name) {
        switch ($field_name) {
            case 'title':
                return $item->get_title();
            case 'description':
                return $item->get_description();
            // More cases can be added as per the structure of your RSS feed.
            default:
                return '';
        }
    }
}



?>