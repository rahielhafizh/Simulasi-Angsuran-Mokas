<div class="alert" id="error-alert" style="display: <?php echo $provider->errorValidation ? 'block' : 'none'; ?>;">
    <span id="error-message"><?php echo htmlspecialchars($provider->errorValidation ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
</div>