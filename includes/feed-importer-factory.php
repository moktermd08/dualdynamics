<?php 

/**
 * RSSFeedImporterFactory Class
 * Factory for creating RSS feed importer instances.
 */
class RSSFeedImporterFactory {

    /**
     * Create an RSS feed importer.
     *
     * @param string $type The type of RSS feed (e.g., 'cnn').
     * @return AbstractRSSFeedImporter An instance of an RSS feed importer.
     */
    public static function create($type) {
        switch ($type) {
            case 'cnn':
                return new CNN_RSS_Feed_Importer();
            // You can extend with more cases for other RSS feed types.
            default:
                throw new Exception("Invalid RSS feed type.");
        }
    }
}

/**
 * CNN_RSS_Feed_Importer Class
 * Concrete class for importing CNN RSS feed.
 */
class CNN_RSS_Feed_Importer extends AbstractRSSFeedImporter {
    
    /**
     * Get the RSS Feed URL for CNN.
     *
     * @return string RSS Feed URL.
     */
    protected function get_rss_feed_url() {
        return 'https://edition.cnn.com/services/rss/';
    }
}


?>