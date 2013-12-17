<?php $this->context->loadHelpers(array('form', 'session', 'js')); ?>
<label><input type="checkbox" style="width: auto; margin-top: 10px;" id="toggleGrid" checked="true" onclick="p1.toggleGrid();" />Toggle Grid</label>
<span class="header-divider"></span>
<?php if($this->context->helpers['session']->isLoggedIn()): ?>
    <div id="postFormWrapper">
    <?php
    $this->context->helpers['form']
        ->begin('post', 'posts')
        ->input('title', 'text', array('name'=>'data[title]'))
        ->input('text', 'text', array('name'=>'data[content][text]'))
        ->input('tags', 'text', array('value'=>'default'))
        ->input('latitude', 'hidden', array('name'=>'data[latitude]'))
        ->input('longitude', 'hidden', array('name'=>'data[longitude]'))
        ->input('location', 'hidden', array('name'=>'data[location]'))
        ->input('userId', 'hidden', array('name'=>'data[userId]'))
        ->input('token', 'hidden')
        ->submit('Submit')
        ->useAjax('theGridREST.postsNewPostCallback')
        ->end();
    ?>
    </div>
<?php endif; ?>
<span style="float: right;">
    <?php if($this->context->helpers['session']->isLoggedIn()): ?>
        Welcome, <?php echo $this->context->helpers['session']->getUsername(); ?>
        <?php
        $this->context->helpers['form']
            ->begin('', 'account', 'logout')
            ->submit('logout')
            ->useAjax('theGridREST.accountLogoutCallback')
            ->end();
        ?>
    <?php else: ?>
        <?php
        $this->context->helpers['form']
            ->begin('', 'account', 'login')
            ->input('username')
            ->input('password', 'password')
            ->submit('login')
            ->useAjax('theGridREST.accountLoginCallback')
            ->end();
        ?>
        <a href="#" onclick="$('#userFormWrapper').toggle(); return false;">Register</a>
        <div id="userFormWrapper" style="position: absolute; right: 0; z-index:1; background: #444; display: none;">
            <?php
            $this->context->helpers['form']
                ->begin('', 'account', 'register')
                ->input('username', 'text', array('name'=>'data[username]'))
                ->input('email', 'text', array('name'=>'data[email]'))
                ->input('password', 'password', array('name'=>'data[pass]'))
                ->submit('register')
                ->useAjax('theGridREST.accountRegisterCallback')
                ->end();
            ?>
        </div>
    <?php endif; ?>
</span>
