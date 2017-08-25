<?php
/**
 * TweetPull4RSS Config
 * https://github.com/sahearn/TweetPull
 * http://scott.teamahearn.com/tweetpull4rss/
 *
 * @author sahearn
 */

class Config {
    
    /*
     * ************************************************************************
     *  REQUIRED PARAMETERS
     *  you must set these PATH, CONSUMER_, and ACCESS_ parameters
     * ************************************************************************
     */
    
    /* full path to tmhOAuth.php */
    const OAUTH_PATH = '/full/path/to/tmhOAuth/';
    
    /* full path to output RSS file */
    const FILE_PATH = '/full/path/to/output/destination/';
    
    /* keys and tokens for your dev.twitter app */
    const CONSUMER_KEY      = '[your consumer key]';
    const CONSUMER_SECRET   = '[your consumer secret]';
    const ACCESS_TOKEN      = '[your access token]';
    const ACCESS_SECRET     = '[your access secret]';

    
    
    
    /*
     * ************************************************************************
     *  OPTIONAL PARAMETERS
     *  set these next few parameters to your liking
     * ************************************************************************
     */

    /*
     * how to render entities (links, @mentions, #hastags, media) embedded in tweets
     * e.g. http://t.co/abcde becomes <a href="http://t.co/abcde">http://t.co/abcde</a>
     *      @twitterid becomes <a href="https://twitter.com/twitterid">@twitterid</a>
     *      #api becomes <a href="https://twitter.com/search?q=%23api&src=hash">#api</a>
     * 
     * this will only take effect in the RSS <item> description
     * 
     * also see EMBED_IMAGE below for rendering of embedded media/images
     * 
     * 0 => do not convert entities to hyperlinked HTML
     * 1 => convert entities to hyperlinked HTML
     */
    const CONVERT_ENTITIES = 1;
    
    /* 
     * how to render images embedded in tweets
     * e.g. http://t.co/abcde becomes <img src="http://full.path.com/abcde.jpg" width="[width]" height="[height]" border="0">
     * 
     * 0 => do not render images inline in RSS post content; link only
     * 1 => render images inline in RSS post content with HTML IMG tag
     */
    const EMBED_IMAGE = 1;

    /* 
     * for the purposes of RSS, the tweet itself will be used as its own title and 
     * description.  if you'd like the title truncated (safely by word-boundary, 
     * appended with ellipses) to a certain length, set TRUNCATE_TITLE to a non-zero 
     * integer.  set to 0 to leave the title unmodified
     * e.g. 50
     */
    const TRUNCATE_TITLE = 0;

    
    
    
    /*
     * ************************************************************************
     *  DEBUG PARAMETERS
     *  probably no need to mess with anything else down here unless you're
     *  having problems and need to debug
     * ************************************************************************
     */
    
    /* 
     * OFF      => no info or errors to output
     * INFO     => info/messages to DEBUG_OUTPUT
     * ERROR    => errors to DEBUG_OUTPUT
     * ALL      => INFO and ERRORS to DEBUG_OUTPUT
     */
    const DEBUG_MODE = 'OFF';
    
    /*
     * SCREEN   => output to screen
     * LOG      => output to logfile "[twitter user id].log"
     * ALL      => output to both screen and logfile
     * 
     * if DEBUG_MODE = 'OFF', DEBUG_OUTPUT will have no effect
     */
    const DEBUG_OUTPUT = 'SCREEN';
    
    /*
     * LIVE     => production; live call to Twitter API
     * SAMPLE   => sample response; no live call to Twitter API; good for testing
     * 
     * if set to 'SAMPLE', -u command line argument is still required, but
     * will have no effect on retrieved data.  script will use the contents 
     * of SAMPLE_DATA instead.
     */
    const DATA_SOURCE = 'LIVE';
    
    /* location of sample data if DATA_SOURCE is set to 'SAMPLE' */
    const SAMPLE_DATA = "tweetpull-sample-response-success_serialized.txt";
}

?>
