<?php

/*
 * @package PhpTube - A PHP class to get youtube download links
 * @author Abu Ashraf Masnun <mailbox@masnun.me>
 * @website http://masnun.com
 *
 */

class PhpTube
{
    static $mime_to_extension = array(
        'video/webm' => '.webm',
        'video/x-flv' => '.flv',
        'video/mp4' => '.mp4',
        'video/3gpp' => '.3gp'
    );

    /**
     * Parses the youtube URL and returns error message or array of download links
     *
     * @param  $watchUrl the URL of the Youtube video
     * @return string|array the error message or the array of download links
     */
    public function getDownloadInfo($watchUrl)
    {
        //utf8 encode and convert "&"
        $html = utf8_encode($this->_getHtml($watchUrl));
        $html = str_replace("\u0026amp;", "&", $html);

        preg_match('#yt.playerConfig = (\{.*\});#m', $html,$matches);

        if(!$matches) return FALSE;

        $playerConfig = json_decode($matches[1]);

        // print_r($playerConfig);

        $title = $playerConfig->args->title;
        $video_id = $playerConfig->args->video_id;
        $storyboard_specs = explode('|',$playerConfig->args->storyboard_spec);
        $image_host = parse_url(array_shift($storyboard_specs), PHP_URL_HOST);
        $thumbnail = "http://$image_host/vi/{$playerConfig->args->video_id}/default.jpg";
        $thumbnail_hq = "http://$image_host/vi/$video_id/hqdefault.jpg"; 

        $fmt_list = explode(',',$playerConfig->args->fmt_list);
        $itag_resolution = array();

        foreach($fmt_list as $index=>$def){
            $fmt_list[$index] = explode('/',$def);
            $itag_resolution[$fmt_list[$index][0]] = $fmt_list[$index][1];
        }

        $url_encoded_fmt_stream_map = explode(',',$playerConfig->args->url_encoded_fmt_stream_map);

        foreach($url_encoded_fmt_stream_map as $index=>$map){
            parse_str($map,$url_encoded_fmt_stream_map[$index]);
            $url_encoded_fmt_stream_map[$index]['file_extension'] = $file_ext = self::get_file_extension_by_mime($url_encoded_fmt_stream_map[$index]['type']);

            $url_encoded_fmt_stream_map[$index]['resolution'] = $resolution = $itag_resolution[$url_encoded_fmt_stream_map[$index]['itag']];
            
            $url_encoded_fmt_stream_map[$index]['file_name'] = self::get_download_file_name($title,$file_ext,$resolution);

            $url_encoded_fmt_stream_map[$index]['url'] = $url_encoded_fmt_stream_map[$index]['url'] . "&" . http_build_query(array(
                    "type"=>$url_encoded_fmt_stream_map[$index]['type'],
                    "fallback_host"=>$url_encoded_fmt_stream_map[$index]['fallback_host'],
                    "signature"=>$url_encoded_fmt_stream_map[$index]['sig'],
                    "keepalive"=>"yes",
                    "title"=>$title
                ));
        }

        $download_links = $url_encoded_fmt_stream_map; 

        return compact(
            'title',
            'video_id',
            'thumbnail',
            'thumbnail_hq',
            'download_links'
        );
    }

    /**
     * A wrapper around the cURL library to fetch the content of the url
     *
     * @throws Exception if the curl extension is not available (or loaded)
     * @param  $url the url of the page to fetch the content of
     * @return string the content of the url
     */
    private function _getHtml($url)
    {
        if($_SERVER['HTTP_HOST']=='localhost'){
            return file_get_contents(dirname(__FILE__) .'/sample_contents.html');
        }

        if (function_exists("curl_init"))
        {

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            return curl_exec($ch);
        }
        else
        {
            throw new Exception("No cURL module available");
        }
    }

    private static function get_file_extension_by_mime($target_mime){
        foreach (self::$mime_to_extension as $mime => $extension) {
            if(stripos($target_mime, $mime)!==FALSE) return $extension;
        }
        return '.unknown';
    }

    public static function get_download_file_name($title,$file_ext,$resolution=FALSE){
        return trim(preg_replace('#\s+#',' ',preg_replace('#\W+#', '_', $title)),'_') . (!empty($resolution)?"_{$resolution}":"") . $file_ext;    
    }
}
