<?php
    $errors = isset($t['errors']) ? $t['errors'] : array();
?>
<div class="container" style="margin-top: 10em;">
    <form class="well" action="<?php echo $ro->gen(NULL); ?>" method="post">
        <fieldset>
            <legend><?php echo $tm->_('Sign in using your company account','auth.ui') ?>:</legend>
<?php
    if (isset($errors['auth']))
    {
?>
            <span style="display: inline-block; margin-bottom: 2em;" class="label label-important"><?php echo htmlspecialchars($errors['auth']); ?></span>
<?php
    }
?>
            <div class="control-group input-prepend <?php echo isset($errors['username']) ? 'error' : ''; ?>">
                <label for="user_input" class="control-label add-on icon-user">&#8203;</label>
                <input type="text" id="user_input" name="username" placeholder="<?php echo $tm->_('Username','auth.ui') ?>" />
<?php
    if (isset($errors['username']))
    {
?>
                <span class="help-inline"><?php echo htmlspecialchars($errors['username']); ?></span>
<?php
    }
?>
            </div>
            <div class="control-group input-prepend <?php echo isset($errors['password']) ? 'error' : ''; ?>">
                <label for="password_input" class="add-on icon-lock">&#8203;</label>
                <input id="password_input" type="password" name="password" placeholder="<?php echo $tm->_('Password','auth.ui') ?>" />
<?php
    if (isset($errors['password']))
    {
?>
                <span class="help-inline"><?php echo htmlspecialchars($errors['password']); ?></span>
<?php
    }
?>
            </div>
            <button style="display: block;" type="submit" class="icon-signin btn btn-primary"> <?php echo $tm->_('Signin','auth.ui') ?></button>
        </fieldset>
    </form>
</div>
