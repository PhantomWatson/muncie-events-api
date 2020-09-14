<!-- bootstrap css local fallback -->
<div id="bootstrapCssTest" class="hidden-xs-up"></div>
<script>
    $(function() {
        if ($('#bootstrapCssTest').is(':visible')) {
            $('head').prepend('<link rel="stylesheet" href="/css/bootstrap.min.css" />');
        }
    });
</script>
