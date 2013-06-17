jQuery(document).ready(function($) {
    radio_tax.slugs.forEach(function(taxonomy) {
        $('#' + taxonomy + 'checklist li :radio, #' + taxonomy + 'checklist-pop :radio').on('click', function() {
            var t = $(this), c = t.is(':checked'), id = t.val();
            $('#' + taxonomy + 'checklist li :radio, #' + taxonomy + 'checklist-pop :radio').prop('checked', false);
            $('#in-' + taxonomy + '-' + id + ', #in-popular-' + taxonomy + '-' + id).prop('checked', c);
        });

        // submit new term on Enter
        $('input#new' + taxonomy).on('keydown', function(e) {
            if (e.which !== 13) { return true; }
            submit_new_term(taxonomy);
        });

        // submit new term on button click
        $('#' + taxonomy +'-add .radio-tax-add').on('click', submit_new_term.bind(this, taxonomy));
    });

    function submit_new_term(taxonomy) {
        term  = $('#' + taxonomy + '-add #new' + taxonomy).val();
        nonce = $('#' + taxonomy + '-add #_wpnonce_radio-add-tag').val();

        $.post(ajaxurl, {
             action: 'radio_tax_add_taxterm'
            ,term: term
            ,'_wpnonce_radio-add-tag': nonce
            ,taxonomy: taxonomy
        }, function(r) {
            $('#' + taxonomy + 'checklist').append(r.html).find('li#' + taxonomy + '-' + r.term + ' :radio').attr('checked', true);
        }, 'json');
    }
});