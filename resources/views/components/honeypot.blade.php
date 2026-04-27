{{-- Honeypot anti-bot --}}
<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true" tabindex="-1">
    <label for="website_url_{{ $id ?? 'default' }}">Ne pas remplir</label>
    <input type="text" name="website_url" id="website_url_{{ $id ?? 'default' }}" value="" autocomplete="off" tabindex="-1">
</div>
<input type="hidden" name="_timestamp" value="{{ time() }}">
