<?php


class RecentComments
{
  private $forumName;
  private $publicKey;
  private $settings;
  private $baseUrl = "http://disqus.com/api/3.0/posts/list.json";
  private $threadBaseUrl ="http://disqus.com/api/3.0/threads/details.json";
  private $cachedThreads;
  private $commentTemplate;
  private $commentResponse;
  private $commentHTML;


  public function __construct($forum, $key, $options=array())
  {
    $this->forumName = $forum;
    $this->publicKey = $key;
    $defaults = array(
      "commentLimit" => 15,
      "filterUsers" => "",
      "filterLimit" => 0,
      "useRelativeTime" => false,
      "dateFormat" => 'F j, Y \a\t g:iA',
      "commentLength" => 95,
      "cache" => true,
      "cacheFile"=> __DIR__ . "/recent_comments.cache",
      "cacheTime" => 3
    );
    $this->settings = array_merge($defaults, $options);
    $this->settings["cacheTime"] *= 60;
    $this->commentTemplate = '<div class="dqCommentWrap {ALTER}">
                <div class="dqCommentHead">
                  <div class="dqAvatarWrap">
                    <img class="dqCommentAvatar" src="{AUTHOR_AVATAR}" alt="{AUTHOR_NAME}"/>
                  </div>
                  <div class="dqCommentMeta">
                    <span class="dqCommentAuthor">
                      <a class="dqProfileLink" href="{AUTHOR_PROFILE}">{AUTHOR_NAME}</a>
                    </span>
                    <span class="dqCommentTime">{POST_TIME}</span>
                  </div>
                  <div class="dqClear"></div>
                </div>
                <div class="dqCommentBody">
                  <a class="dqCommentThread" href="{THREAD_LINK}">{THREAD_TITLE}</a>
                  <div class="dqCommentText"><a class="dqCommentLink" href="{THREAD_LINK}#comment-{COMMENT_ID}">{MESSAGE}</a></div>
                </div>
              </div>';
   
  
  }
  public function getComments()
  {
    $c = $this->settings["cache"];
    $f = $this->settings["cacheFile"];
    $t = $this->settings["cacheTime"];
    
    if($c and file_exists($f) and (time() - $t < filemtime($f)) )
    {/* If cache on, file exists and time is right use cache */
      return file_get_contents($f);
    }
    else
    {/* we need to recreate comments */
      $comments = $this->createComments();
      if(!$c and file_exists($f))
      {/*if caching is off but a cache file exist delete the cache*/
        @unlink($f);
      }
      if($c)
      {/*if caching is on recreate cache file*/
        file_put_contents ($f, $comments);
      }
       return $comments;
    }

  }
  private function createComments()
  {


    if(!$this->getResponse())
    {
      return '<div id="dqRecentComments"><span id="dqNoComments">No Recent Comments Found</span></div>';
    }

    if($this->commentResponse == 'You have exceeded your hourly limit of requests')
    {
      return '<div id="dqRecentComments"><span id="dqNoComments">No Recent Comments Found</span></div><!--hourly comment limit-->';
    }
    //basic counter
    $commentCounter = 0;
    $recentComments = '<div id="dqRecentComments">';
    $comment = $this->commentResponse;
    $filteredUsers = explode(",",$this->settings["filterUsers"]);
   
    foreach($comment as $commentObj)
    {
      $commentInfo =array();
      // first skip to next if user is filtered
       $commentInfo["author_name"] = $commentObj["author"]["name"];
       
      if($filteredUsers != null)
      {
        if(in_array($commentInfo["author_name"],$filteredUsers))
        { 
          //we don't like this user. skip to next
          continue;
        }
      }
       $commentInfo["author_profile"] = $commentObj["author"]["profileUrl"];
       $commentInfo["author_avatar"] = $commentObj["author"]["avatar"]["large"]["cache"];
       $commentInfo["message"] = $commentObj["raw_message"];
       $commentInfo["comment_id"] = $commentObj["id"];

      if($this->settings["useRelativeTime"] == true)
      {
        $commentInfo["post_time"] = $this->relative_time(strtotime($commentObj["createdAt"].'+0000'));
      }
      else
      {   
        $commentInfo["post_time"] = date($this->settings["dateFormat"],strtotime($commentObj["createdAt"].'+0000'));
      }
      $commentCounter++;
      //alternate class
      if($commentCounter % 2 == 0)
      {
        $commentInfo["alter"] ="";
      }
      else
      {
        $commentInfo["alter"] ="alter";
      }

      $threadInfo = $this->getThread($commentObj["thread"]);
      $commentInfo["thread_title"] = $threadInfo["title"];
      $commentInfo["thread_link"] = $threadInfo["link"];
      $commentInfo["message"] = $this->shortenComment($commentObj["raw_message"], $this->settings["commentLength"]);

      $commentHtml = $this->useTemplate($this->commentTemplate,$commentInfo);
      $recentComments .= $commentHtml;
      if($commentCounter == $styleParameter["commentLimit"])
      {
        break;
      }
    }
    $recentComments .= '</div>';
    return $recentComments;
  }
  public function setTemplate($strTemplate)
  {
    $this->commentTemplate = $strTemplate;
  }
  public function deleteCache()
  {
    $f = $this->settings["cacheFile"];
    if(file_exists($f))
      {
        return @unlink($f);
      }
      else
      {
        return false;
      }
  }
  public function getResponse(){

    $dqParameter = array( 
      "api_key" => $this->publicKey,
      "forum" =>  $this->forumName,
      "include" => "approved",
      "limit" =>   $this->settings["commentLimit"]
    );
    //add parameters to request string
    $dqRequest = $this->addQueryStr($this->baseUrl, $dqParameter);
    // get response with finished request url
    $dqResponse = $this->file_get_contents_curl($dqRequest);
    //check repsonse
    if($dqResponse != false )
    {
      // convert response to php object 
      $dqResponse = @json_decode($dqResponse, true);
      $this->commentResponse = $dqResponse["response"];
      //check comment count
      if(count($this->commentResponse) > 0)
      {
        return $this->commentResponse;
      }
      else
      {
        return false; 
      }
    }
    else
    {
      return false;  
    }

  }

  private function useTemplate($text,$variables)
  {
    // white list of variables
    $allowed_variables = array_keys($variables);
    $match = array();
    preg_match_all("/\{([A-Z|_]*?)\}/", $text, $match);

    foreach($match[0] as $variable) 
    {
        $key = strtolower(trim($variable,"{}"));
        // only allow white listed variables
        if(!in_array($key, $allowed_variables)) continue; 
        $text = str_replace($variable, $variables[$key], $text);
    }

    return $text;
  }

  private function getThread($threadId)
  {
    if($this->cachedThreads)
    {
      if(key_exists($threadId, $this->cachedThreads))
      {
        return $this->cachedThreads[$threadId];
      }
    }

    $dqParameter = array( 
      "api_key" => $this->publicKey,
      "thread" => $threadId
    );

    $dqRequest = $this->addQueryStr($this->threadBaseUrl, $dqParameter);
    // convert response to php object 
    $dqResponse= $this->file_get_contents_curl($dqRequest);

      if($dqResponse !== false)
      {
        $dqResponse = @json_decode($dqResponse, true);
        $dqThread = $dqResponse["response"];
        $this->cachedThreads[$threadId] = $dqThread;
        return $dqThread;
      }
      else
      {
        $dqThread = array(
          title=> "Article not found",
          link => "#"
        );
        $this->cachedThreads[$threadId] = $dqThread;
        return $dqThread;
      } 
  }

  private function addQueryStr($baseUrl,$parameters)
  {
    $i=0;
    $newUrl = $baseUrl;
    if (count($parameters) > 0)
    {
      foreach($parameters as $key => $value)
      { 
        if($i == 0)
        {
          $newUrl .="?".$key."=".$value;
        }
        else
        {
          $newUrl .="&".$key."=".$value;
        }
        $i +=1;
      }
    }
    return $newUrl;
  }
  private function file_get_contents_curl($url) 
  {
    //Source: http://www.codeproject.com/Questions/171271/file_get_contents-url-failed-to-open-stream
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);  // don't use cached ver. of url 
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); // seriously...don't use cached ver. of url
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }
  private function relative_time($date)
  {
    //Source: http://stackoverflow.com/questions/2690504/php-producing-relative-date-time-from-timestamps
      $now = time();
      $diff = $now - $date;
      if ($diff < 60){
          return sprintf($diff > 1 ? '%s seconds ago' : 'a second ago', $diff);
      }
      $diff = floor($diff/60);
      if ($diff < 60){
          return sprintf($diff > 1 ? '%s minutes ago' : 'one minute ago', $diff);
      }
      $diff = floor($diff/60);
      if ($diff < 24){
          return sprintf($diff > 1 ? '%s hours ago' : 'an hour ago', $diff);
      }
      $diff = floor($diff/24);
      if ($diff < 7){
          return sprintf($diff > 1 ? '%s days ago' : 'yesterday', $diff);
      }
      if ($diff < 30)
      {
          $diff = floor($diff / 7);
          return sprintf($diff > 1 ? '%s weeks ago' : 'one week ago', $diff);
      }
      $diff = floor($diff/30);
      if ($diff < 12){
          return sprintf($diff > 1 ? '%s months ago' : 'last month', $diff);
      }
      $diff = date('Y', $now) - date('Y', $date);
      return sprintf($diff > 1 ? '%s years ago' : 'last year', $diff);
  }
  private function shortenComment($comment, $commentLength)
  {
    if($commentLength != 0)
    {
      if(strlen($comment) > $commentLength)
       {
        
        $comment = preg_replace(
                      '/\s+?(\S+)?$/', '', 
                      substr($comment, 0, $commentLength+1)
                    )."...";
        
       }
    }
    return $comment;
  }

}




?>