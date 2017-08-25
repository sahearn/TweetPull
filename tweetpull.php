<?php
/**
 * TweetPull4RSS
 * Create an RSS feed of a selected Twitter user's recent tweets using the v1.1 API.
 * https://github.com/sahearn/TweetPull
 * http://scott.teamahearn.com/tweetpull4rss/
 *
 * @author sahearn
 * @version 0.4
 *
 * 10 April 2014
 *
 *  
 *    Copyright 2014 Scott A'Hearn
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

$options = getopt("u:r::t::"); 
if (empty($options) ) { 
    print "User id is required. [-u userID]" . "\n\n";
    print "usage: tweetpull.php -u [userID] [-r] [-t]" . "\n\n";
    print "  optional:" . "\n";
    print "\t" . "-r" . "\t" . "exclude replies" . "\n";
    print "\t" . "-t" . "\t" . "exclude retweets" . "\n";
    exit(1); 
}
// clean up input
$passedID = substr(strip_tags(stripslashes($options['u'])), 0, 15);
$passedReplies = false;
if (isset($options['r']) && is_bool($options['r']))
    $passedReplies = true;
$passedRetweets = true;
if (isset($options['t']) && is_bool($options['t']))
    $passedRetweets = false;

// import and initialize config file
include 'tweetpull-config.php';
initConfig();

// import auth library to credential with twitter
require Config::OAUTH_PATH . 'tmhOAuth.php';

class tmhOAuthExample extends tmhOAuth {
    const VERSION = '0.4';

    public $twitter_userid;
    public $twitter_name;
    public $twitter_desc;
    public $twitter_icon;
    public $file;
    
    public function __construct($config = array()) {
        $this->config = array_merge(
            array(
                // change the values below to ones for your application
                'consumer_key'    => Config::CONSUMER_KEY,
                'consumer_secret' => Config::CONSUMER_SECRET,
                'token'           => Config::ACCESS_TOKEN,
                'secret'          => Config::ACCESS_SECRET,
                //'bearer'          => 'YOUR_OAUTH2_TOKEN',
                'user_agent'      => 'TweetPull4RSS ' . self::VERSION . ' - //github.com/sahearn/TweetPull with tmhOAuth ' . parent::VERSION . ' - //github.com/themattharris/tmhOAuth',
            ),
            $config
        );

        parent::__construct($this->config);
    }
  
    // open output file for writing; config with your path here
    public function openOutputFile() {
        $this->file = Config::FILE_PATH . $this->twitter_userid . '.xml';
        writeDebugMessage("INFO", "Output RSS file created: " . $this->file);
    }
  
    public function writeHeader() {
        $output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $output .= '<rss version="2.0"' . "\n";
        $output .= ' xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
        $output .= ' xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
        $output .= ' >' . "\n";

        $output .= "\t" . '<channel>' . "\n";
        $output .= "\t\t" . '<title>' . $this->twitter_name . ' (' . $this->twitter_userid . ') on Twitter</title>' . "\n";
        $output .= "\t\t" . '<link>https://twitter.com/' . $this->twitter_userid . '</link>' . "\n";
        $output .= "\t\t" . '<description>' . $this->twitter_desc . '</description>' . "\n";
        $output .= "\t\t" . '<generator>TweetPull for RSS v' . self::VERSION . ' - //bit.ly/15dLYuO</generator>' . "\n";
        $output .= "\t\t" . '<language>en-US</language>' . "\n";

        $output .= "\t\t" . '<image>' . "\n";
        $output .= "\t\t" . ' <title>' . $this->twitter_name . ' (' . $this->twitter_userid . ') on Twitter</title>' . "\n";
        $output .= "\t\t" . ' <url>' . $this->twitter_icon . '</url>' . "\n";
        $output .= "\t\t" . ' <link>https://twitter.com/' . $this->twitter_userid . '</link>' . "\n";
        $output .= "\t\t" . '</image>' . "\n";

        file_put_contents($this->file, $output);
        
        writeDebugMessage("INFO", "Output RSS header written.");
    }
  
    public function writeFooter() {
        $output = "\t" . '</channel>' . "\n";
        $output .= "</rss>";

        $current = file_get_contents($this->file);
        $current .= $output;
        file_put_contents($this->file, $current);
        
        writeDebugMessage("INFO", "Output RSS footer written.");
    }
  
    public function truncate($string, $length, $stopanywhere=false) {
        //truncates a string to a certain char length, stopping on a word if not specified otherwise.
        if (strlen($string) > $length) {
            //limit hit!
            $string = substr($string,0,($length -3));
            if ($stopanywhere) {
                //stop anywhere
                $string .= '...';
            } else{
                //stop on a word.
                $string = substr($string,0,strrpos($string,' ')).'...';
            }
        }
        return $string;
    }

    public function links($r) {
        $final = '';
        $respText = $r["text"];

        $urls = $r["entities"]["urls"];
        foreach ($urls as $url) {
            $uUrl = $url["url"];
            list($uStart, $uEnd) = $url["indices"];
            $entities[] = array( $uStart, $uEnd, 'url', $uUrl );
        }
        
        $mentions = $r["entities"]["user_mentions"];
        foreach ($mentions as $mention) {
            $mName = $mention["screen_name"];
            list($mStart, $mEnd) = $mention["indices"];
            $entities[] = array( $mStart, $mEnd, 'mention', '@' . $mName );
        }
        
        $hashtags = $r["entities"]["hashtags"];
        foreach ($hashtags as $hashtag) {
            $hName = $hashtag["text"];
            list($hStart, $hEnd) = $hashtag["indices"];
            $entities[] = array( $hStart, $hEnd, 'hashtag', '#' . $hName );
        }
        
        if (isset($r["entities"]["media"])) {
            $media = $r["entities"]["media"][0];
            $iType = $media["type"];
            if (strcasecmp($iType,"photo") == 0) {
                $iUrl = $media["url"];
                $iMUrl = $media["media_url"];
                list($iStart, $iEnd) = $media["indices"];
                $iWidth = $media["sizes"]["medium"]["w"];
                $iHeight = $media["sizes"]["medium"]["h"];
                $entities[] = array( $iStart, $iEnd, 'media', $iUrl, $iMUrl, $iWidth, $iHeight );
            }
        }

        if (isset($entities)) {
            rsort($entities);
            
            $convertedEntities = 0;
            foreach ($entities as $entity) {
                if (strcasecmp(substr($respText, $entity[0], $entity[1]-$entity[0]),$entity[3]) == 0) {
                    if (strcmp($entity[2],"url") == 0) {
                        $final = substr_replace($respText, '<a href="' . $entity[3] . '">' . $entity[3] . '</a>', $entity[0], $entity[1]-$entity[0]);
                        $convertedEntities++;
                    } elseif (strcmp($entity[2],"mention") == 0) {
                        $final = substr_replace($respText, '<a href="https://twitter.com/' . trim($entity[3],'@') . '">' . $entity[3] . '</a>', $entity[0], $entity[1]-$entity[0]);
                        $convertedEntities++;
                    } elseif (strcmp($entity[2],"hashtag") == 0) {
                        $final = substr_replace($respText, '<a href="https://twitter.com/search?q=%23' . trim($entity[3],'#') . '&amp;src=hash">' . $entity[3] . '</a>', $entity[0], $entity[1]-$entity[0]);
                        $convertedEntities++;
                    } elseif (strcmp($entity[2],"media") == 0) {
                        if (Config::EMBED_IMAGE) {
                            $final = substr_replace($respText, '<img src="' . $entity[4] . '" width="' . $entity[5] . '" height="' . $entity[6] . '" border="0">', $entity[0], $entity[1]-$entity[0]);
                        } else {
                            $final = substr_replace($respText, '<a href="' . $entity[3] . '">' . $entity[3] . '</a>', $entity[0], $entity[1]-$entity[0]);
                        }
                        $convertedEntities++;
                    }
                    $respText = $final;
                }
            }
            writeDebugMessage("INFO", "\tConverted " . $convertedEntities . " entities.");
        }

        return $respText;
    }
}

// initialize config
function initConfig() {
    if (strcmp(Config::DEBUG_MODE,"OFF") <> 0 && (strcmp(Config::DEBUG_OUTPUT,"LOG") == 0 || strcmp(Config::DEBUG_OUTPUT,"ALL") == 0)) {
        global $passedID, $passedReplies, $passedRetweets, $logHandle;
        $logfile = Config::FILE_PATH . $passedID . '.log';
        $logHandle = fopen($logfile, 'a') or die('Unable to open file: '.$logfile);
        writeDebugMessage("INFO", "Log file initialized: " . $logfile);
    }
    writeDebugMessage("INFO", "Initializing configuration...");
    $refl = new ReflectionClass('Config');
    writeDebugMessage("INFO", $refl->getConstants());
    writeDebugMessage("INFO", "Initialization complete.");
}
// clean up logging
function cleanUp() {
    if (strcmp(Config::DEBUG_MODE,"OFF") <> 0 && (strcmp(Config::DEBUG_OUTPUT,"LOG") == 0 || strcmp(Config::DEBUG_OUTPUT,"ALL") == 0)) {
        global $logHandle;
        fclose($logHandle);
        writeDebugMessage("INFO", "Log file closed.");
    }
    writeDebugMessage("INFO", "Finished.");
}

// write debug message
function writeDebugMessage($messageMode, $message) {
    if (strcmp(Config::DEBUG_MODE, $messageMode) == 0 || strcmp(Config::DEBUG_MODE, "ALL") == 0) {
        writeDebugOutput(Config::DEBUG_OUTPUT, $messageMode, $message);
    }
}
// write debug output
function writeDebugOutput($output, $messageMode, $message) {
    $ts = @date('Y-m-d H:i:s');
    global $logHandle;

    // used for config output
    if (is_array($message)) {
        $logEntry = '';
        foreach ($message as $i => $value) {
            $logEntry .= "\t[" . $i . "] => " . $value . "\n";
        }
    } else {
        $logEntry = $ts . " [" . $messageMode . "] " . $message . "\n";
    }

    switch ($output) {
        case "SCREEN":
            print $logEntry;
            break;
        case "LOG":
            fwrite($logHandle, $logEntry);
            break;
        case "ALL":
            print $logEntry;
            fwrite($logHandle, $logEntry);
            break;
    }
}

// instantiate ctor
$tmhOAuth = new tmhOAuthExample();

if (strcmp(Config::DATA_SOURCE,"LIVE") == 0) {
    writeDebugMessage("INFO", "Preparing API request.");

    if ($passedReplies == TRUE) {
        writeDebugMessage("INFO", "exclude_replies set to TRUE");
    }
    if ($passedRetweets == FALSE) {
        writeDebugMessage("INFO", "include_rts set to FALSE");
    }
    
    // call API
    $code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), 
            array('screen_name' => $passedID, 'exclude_replies' => $passedReplies, 'include_rts' => $passedRetweets));
    $decoded = json_decode($tmhOAuth->response['response'], true);
    $respHeaders = $tmhOAuth->response['headers'];
    $respCode = $tmhOAuth->response['code'];
    $respInfo = $tmhOAuth->response['info'];

    /* development debug code
    $response = $tmhOAuth->response;
    print_r($response);
    echo serialize($response);
     */
} else {
    writeDebugMessage("INFO", "Preparing sample data file.");

    // no live call to API; use sample data instead; good for testing
    $testFile = Config::SAMPLE_DATA;
    $fh = fopen($testFile, "r");
    $data = unserialize(fread($fh, filesize($testFile)));
    fclose($fh);
    $decoded = json_decode($data['response'], true);
    $respHeaders = $data['headers'];
    $respCode = $data['code'];
    $respInfo = $data['info'];
}

// response code of 200 means success
// existence of "created_at" means presence of tweets
if (strcmp($respCode,'200') == 0 && isset($decoded[0]["created_at"])) {
    
    // extract twitter user data from first tweet
    $tmhOAuth->twitter_userid = $decoded[0]["user"]["screen_name"];
    $tmhOAuth->twitter_name = $decoded[0]["user"]["name"];
    $tmhOAuth->twitter_desc = $decoded[0]["user"]["description"];
    $tmhOAuth->twitter_icon = $decoded[0]["user"]["profile_image_url"];

    writeDebugMessage("INFO", 'Successfully retrieved timeline for user "' . $tmhOAuth->twitter_userid . '"');

    // start output, write header
    $tmhOAuth->openOutputFile();
    $tmhOAuth->writeHeader();
    
    writeDebugMessage("INFO", "Parsing " . count($decoded) . " tweet(s).");

    // write each tweet
    foreach ($decoded as $i) {
        writeDebugMessage("INFO", "Writing Tweet ID " . $i["id_str"] . " to RSS file...");

        // api returns the date in a format considered invalid for RSS; need to shuffle it around
        $p = explode(" ", $i["created_at"]);
        $dateFinal = $p[0] . ', ' . $p[2] . ' ' . $p[1] . ' ' . $p[5] . ' ' . $p[3] . ' ' . $p[4];

        $content = "\t\t" . '<item>' . "\n";
        if (Config::TRUNCATE_TITLE > 0) {
            $content .= "\t\t" . ' <title>' . $tmhOAuth->truncate($i["text"], Config::TRUNCATE_TITLE) . '</title>' . "\n";
        } else {
            $content .= "\t\t" . ' <title>' . $i["text"] . '</title>' . "\n";
        }
        $content .= "\t\t" . ' <link>https://twitter.com/' . $tmhOAuth->twitter_userid . '/status/' . $i["id_str"] . '</link>' . "\n";
        $content .= "\t\t" . ' <guid isPermaLink="false">https://twitter.com/' . $tmhOAuth->twitter_userid . '/status/' . $i["id_str"] . '</guid>' . "\n";
        if (Config::CONVERT_ENTITIES) {
            $content .= "\t\t" . ' <description><![CDATA[' . $tmhOAuth->links($i) . ']]></description>' . "\n";
        } else {
            $content .= "\t\t" . ' <description><![CDATA[' . $i["text"] . ']]></description>' . "\n";
        }
        $content .= "\t\t" . ' <dc:creator>' . $tmhOAuth->twitter_userid . '</dc:creator>' . "\n";
        $content .= "\t\t" . ' <pubDate>' . $dateFinal . '</pubDate>' . "\n";
        $content .= "\t\t" . '</item>' . "\n";

        $current = file_get_contents($tmhOAuth->file);
        $current .= $content;
        file_put_contents($tmhOAuth->file, $current);
    }

    $tmhOAuth->writeFooter();

} elseif (strcmp($respCode,'200') == 0 && count($decoded) == 0) {
    // a rare case, but if a valid user hasn't tweeted yet, API will still return a 200
    writeDebugMessage("INFO", 'User "' . $passedID . '" has not tweeted anything yet!');
} else {
    // errors
    writeDebugMessage("INFO", 'User timeline retrieval unsuccessful for user "' . $passedID . '". Set DEBUG_MODE: ERROR or ALL for more info.');
    if (isset($decoded["errors"][0]["message"])) {
        $allKeys = array_keys($respHeaders);
        writeDebugMessage("ERROR", $allKeys[0] . ' "' . $decoded["errors"][0]["message"] . '"');
    }
}

// adios
cleanUp();

?>
