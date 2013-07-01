<?php

include 'DisqusRecentComments_v2-0.php';

$options= array("cache"=>true,"commentLimit"=>15);
$disqus = new RecentComments('[FORUM_NAME]', '[PUBLIC_KEY]', $options);

/*$testTemplate= '<div class="dqCommentWrap {ALTER}">
                <div class="dqCommentHead">
                  <div class="dqAvatarWrap">
                    <img class="dqCommentAvatar" src="{AUTHOR_AVATAR}" alt="{AUTHOR_NAME}"/>
                  </div>
                  </div>';

$disqus->setTemplate($testTemplate);*/
//var_dump($disqus->deleteCache());

echo($disqus->getComments());
?>
