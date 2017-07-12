<?php

namespace App\Helpers;

class YoutubeHelper
{
    /**
     * Get youtube by id.
     *
     * @param $youtube_id
     *
     * @return array
     */
    public static function getYoutubeById($youtube_id)
    {
        $url = 'https://www.googleapis.com/youtube/v3/videos?id='.
            $youtube_id.'&key='.env('YOUTUBE_API_KEY').
            '&fields=items(id,snippet(channelId,categoryId,title,description,thumbnails),'.
            'status(privacyStatus,embeddable,publicStatsViewable))&part=snippet,status';
        $content = file_get_contents($url);
        $json_array = json_decode($content, true);

        return $json_array;
    }

    /**
     * Search.
     *
     * @param $query
     *
     * @return array
     */
    public static function search($query)
    {
        $url = 'https://www.googleapis.com/youtube/v3/search?key='.
            env('YOUTUBE_API_KEY').'&fields=items(id,snippet)&part=snippet'.
            '&safeSearch=strict&type=video&videoEmbeddable=true'.
            '&videoCategoryId=10&maxResults=10&q='.urlencode($query);
        $content = file_get_contents($url);
        $json_array = json_decode($content, true);

        return $json_array;
    }

    /**
     * Related.
     *
     * @param $youtube_id
     *
     * @return array
     */
    public static function related($youtube_id)
    {
        $url = 'https://www.googleapis.com/youtube/v3/search?key='.
            env('YOUTUBE_API_KEY').'&fields=items(id,snippet)&part=snippet'.
            '&safeSearch=strict&type=video&videoEmbeddable=true'.
            '&videoCategoryId=10&maxResults=5&relatedToVideoId='.$youtube_id;
        $content = file_get_contents($url);
        $json_array = json_decode($content, true);

        return $json_array;
    }
}
