<?php if(!defined('BASE_URL')) define('BASE_URL', '//'.$_SERVER['HTTP_HOST'].'/'); ?>
<div class="login-container">
    <form id="login-form">
        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email" id="email" class="form-input" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" class="form-input" placeholder="Enter your password" required>
        </div>

        <div class="options-row">
            <div class="remember-option">
                <input type="checkbox" id="remember" checked>
                <label for="remember">Remember Login</label>
            </div>

            <div class="password-actions">
                <a href="<?php echo BASE_URL; ?>password-reset" class="password-link">Forgot Password?</a>
                <a href="<?php echo BASE_URL; ?>register" class="password-link">Create Account</a>
            </div>
        </div>

        <button type="submit" class="btn btn-login">Log In</button>
    </form>
</div>