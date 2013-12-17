<?php $this->context->loadHelpers(array('form', 'session', 'js')); ?>
<div id="postViewWrapper">
    <div id="postList" p1mvc="postList"></div>
    <div id="postView">
        <div id="parentPost" p1mvc="parentPost">
        </div>
        <div id="replyPosts" p1mvc="replyPosts">
        </div>
        <?php if($this->context->helpers['session']->isLoggedIn()): ?>
        <?php
            $this->context->helpers['form']
                ->begin('post', 'posts', 'reply')
                ->input('title', 'text', array('name'=>'data[title]'))
                ->input('text', 'text', array('name'=>'data[content][text]'))
                ->input('parentId', 'hidden', array('name'=>'data[parentId]', 'p1mvc'=>'parentId'))
                ->input('userId', 'hidden', array('name'=>'data[userId]'))
                ->input('token', 'hidden')
                ->submit('reply')
                ->useAjax('theGridREST.postsReplyCallback')
                ->end();
        ?>
        <?php endif; ?>
    </div>
</div>